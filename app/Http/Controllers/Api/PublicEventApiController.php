<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use MongoDB\Client as MongoClient;

class PublicEventApiController extends Controller
{
    protected $mongo;
    protected $db;

    public function __construct()
    {
        $this->mongo = new MongoClient(config('app.DB_MONGO_DEV'));
        $this->db = $this->mongo->sosecure_threatintelligent_dev;
    }

    /**
     * GET /api/v1/public/events
     * ดึงรายการ Events พร้อม pagination
     */
    public function listEvents(Request $request)
    {
        try {
            $page = max(1, (int) $request->get('page', 1));
            $perPage = min(200, max(1, (int) $request->get('per_page', 50)));
            $source = $request->get('source'); // misp, otx
            $skip = ($page - 1) * $perPage;

            // Build query - only get published events (public = 1)
            $query = ['status' => 1, 'public' => 1];
            if ($source) {
                $query['source'] = $source;
            }

            // Get events from MongoDB
            $col = $this->db->fx_otx_events;

            $totalItems = $col->countDocuments($query);
            $totalPages = ceil($totalItems / $perPage);

            $cursor = $col->find($query, [
                'skip' => $skip,
                'limit' => $perPage,
                'sort' => ['created' => -1],
                'projection' => [
                    'pulse_id' => 1,
                    'name' => 1,
                    'description' => 1,
                    'TLP' => 1,
                    'tags' => 1,
                    'source' => 1,
                    'indicator_count' => 1,
                    'created' => 1,
                    'modified' => 1,
                ]
            ]);

            $events = [];
            foreach ($cursor as $doc) {
                $events[] = [
                    'event' => $doc['pulse_id'] ?? null,
                    'name' => $doc['name'] ?? null,
                    'description' => $doc['description'] ?? null,
                    'tlp' => $doc['TLP'] ?? null,
                    'tags' => $doc['tags'] ?? null,
                    'source' => $doc['source'] ?? null,
                    'created' => isset($doc['created']) ? $this->formatDate($doc['created']) : null,
                    'modified' => isset($doc['modified']) ? $this->formatDate($doc['modified']) : null,
                ];
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'events' => $events,
                    'pagination' => [
                        'current_page' => $page,
                        'total_pages' => $totalPages,
                        'total_items' => $totalItems,
                        'per_page' => $perPage,
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/v1/public/events/feed
     * ดึง Events ที่มี Indicators ถูก update ในช่วงเวลาที่กำหนด
     * 
     * Logic: Filter indicators by timestamp FIRST, then get parent events
     * Default: ดึงเฉพาะ indicators ที่ updated_at วันนี้
     * 
     * @param timestamp string - Dynamic time filter (e.g., 6h, 24h, 1d, 3d, 1w, 3w, 1m, 3m)
     *                          Format: {number}{unit} where unit is h(hours), d(days), w(weeks), m(months)
     * @param notTag string - Comma-separated tags to exclude (e.g., notTag=spam,test)
     */
    public function listEventsWithIndicators(Request $request)
    {
        try {
            // เพิ่ม execution time สำหรับ request นี้
            set_time_limit(120);

            $page = max(1, (int) $request->get('page', 1));
            $perPage = min(20, max(1, (int) $request->get('per_page', 10))); // จำกัดไว้ที่ 20 เพื่อป้องกัน timeout
            $indicatorLimit = min(50, max(10, (int) $request->get('indicator_limit', 5))); // จำนวน indicators ต่อ event
            $source = $request->get('source');
            $timestamp = $request->get('timestamp'); // e.g., 6h, 24h, 1d, 3d, 1w, 3w, 1m, 3m
            $notTag = $request->get('notTag'); // e.g., spam,test,exclude
            $skip = ($page - 1) * $perPage;

            // Step 1: Build indicator query based on timestamp
            $indicatorQuery = [];
            if ($timestamp) {
                $cutoffDate = $this->parseTimestamp($timestamp);
                if ($cutoffDate) {
                    $indicatorQuery['updated_at'] = ['$gte' => new \MongoDB\BSON\UTCDateTime($cutoffDate->getTimestamp() * 1000)];
                }
            } else {
                // Default: ดึงเฉพาะ indicators ที่ updated_at วันนี้
                $todayStart = new \DateTime('today');
                $indicatorQuery['updated_at'] = ['$gte' => new \MongoDB\BSON\UTCDateTime($todayStart->getTimestamp() * 1000)];
            }

            // Step 2: Get unique pulse_ids from indicators that match the timestamp filter
            $indicatorRefCol = $this->db->fx_otx_events_indicator_ref;
            $pipeline = [
                ['$match' => $indicatorQuery],
                ['$group' => ['_id' => '$pulse_id']],
                ['$sort' => ['_id' => 1]]
            ];
            
            $pulseIdsCursor = $indicatorRefCol->aggregate($pipeline);
            $pulseIds = [];
            foreach ($pulseIdsCursor as $doc) {
                if (!empty($doc['_id'])) {
                    $pulseIds[] = $doc['_id'];
                }
            }

            // Step 3: Build events query
            $eventsQuery = [
                'status' => 1,
                'public' => 1,
                'pulse_id' => ['$in' => $pulseIds]
            ];
            
            if ($source) {
                $eventsQuery['source'] = $source;
            }

            // Filter out events with specified tags
            if ($notTag) {
                $excludeTags = array_map('trim', explode(',', $notTag));
                $eventsQuery['tags'] = ['$nin' => $excludeTags];
            }

            // Step 4: Get events
            $eventsCol = $this->db->fx_otx_events;
            
            $totalItems = $eventsCol->countDocuments($eventsQuery);
            $totalPages = ceil($totalItems / $perPage);

            $cursor = $eventsCol->find($eventsQuery, [
                'skip' => $skip,
                'limit' => $perPage,
                'sort' => ['modified' => -1],
            ]);

            // Step 5: Build response with indicators
            $events = [];
            foreach ($cursor as $doc) {
                $pulseId = $doc['pulse_id'] ?? null;
                
                // Get indicators for this event (with timestamp filter)
                $indicators = $pulseId ? $this->getIndicatorsByPulseIdWithTimestamp($pulseId, $indicatorQuery, $indicatorLimit) : [];

                $events[] = [
                    'event' => $pulseId,
                    'name' => $doc['name'] ?? null,
                    'description' => $doc['description'] ?? null,
                    'tlp' => $doc['TLP'] ?? null,
                    'tags' => $doc['tags'] ?? null,
                    'source' => $doc['source'] ?? null,
                    'created' => isset($doc['created']) ? $this->formatDate($doc['created']) : null,
                    'modified' => isset($doc['modified']) ? $this->formatDate($doc['modified']) : null,
                    'modified_date' => isset($doc['modified']) ? $this->formatDate($doc['modified']) : null,
                    'indicator_count' => count($indicators),
                    'indicators' => $indicators,
                ];
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'events' => $events,
                    'pagination' => [
                        'current_page' => $page,
                        'total_pages' => $totalPages,
                        'total_items' => $totalItems,
                        'per_page' => $perPage,
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper: Get indicators by pulse_id with timestamp filter
     */
    private function getIndicatorsByPulseIdWithTimestamp($pulse_id, $timestampQuery, $limit = 50)
    {
        $query = array_merge(['pulse_id' => $pulse_id], $timestampQuery);

        $col = $this->db->fx_otx_events_indicator_ref;

        $cursor = $col->find($query, [
            'limit' => $limit,
            'sort' => ['updated_at' => -1],
        ]);

        $indicators = [];
        $indicatorCol = $this->db->fx_otx_indicator_detail;

        foreach ($cursor as $ref) {
            $indicatorId = $ref['indicator_id'] ?? null;
            if (!$indicatorId) continue;

            // Get indicator detail
            $detail = $indicatorCol->findOne(['indicator_id' => $indicatorId]);

            // Parse tags string to array
            $tagsRaw = $ref['tags'] ?? '';
            $tagsArray = $tagsRaw ? array_map('trim', explode(',', $tagsRaw)) : [];

            $indicators[] = [
                'indicator_id' => $indicatorId,
                'indicator' => $ref['indicator'] ?? ($ref['indicator_name'] ?? ($detail['indicator_name'] ?? null)),
                'type' => $ref['type'] ?? ($detail['type'] ?? null),
                'score' => $ref['attribute_score'] ?? null,
                'tags' => $tagsArray,
                'severity' => $ref['attribute_serverity'] ?? null,
                'created' => isset($ref['created']) ? $this->formatDate($ref['created']) : null,
                'updated_at' => isset($ref['updated_at']) ? $this->formatDate($ref['updated_at']) : null,
            ];
        }

        return $indicators;
    }

    /**
     * GET /api/v1/public/events/{pulse_id}
     * ดึง Event Detail พร้อม Indicators
     */
    public function getEvent(Request $request, $pulse_id)
    {
        try {
            $col = $this->db->fx_otx_events;

            $event = $col->findOne(['pulse_id' => $pulse_id, 'status' => 1, 'public' => 1]);

            if (!$event) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Event not found'
                ], 404);
            }

            // Get indicators for this event
            $indicators = $this->getIndicatorsByPulseId($pulse_id);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'event' => [
                        'event' => $event['pulse_id'] ?? null,
                        'name' => $event['name'] ?? null,
                        'description' => $event['description'] ?? null,
                        'tlp' => $event['TLP'] ?? null,
                        'tags' => $event['tags'] ?? null,
                        'source' => $event['source'] ?? null,
                        'created' => isset($event['created']) ? $this->formatDate($event['created']) : null,
                        'modified' => isset($event['modified']) ? $this->formatDate($event['modified']) : null,
                    ],
                    'indicators' => $indicators
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/v1/public/events/{pulse_id}/indicators
     * ดึง Indicators ของ Event (with pagination)
     */
    public function getEventIndicators(Request $request, $pulse_id)
    {
        try {
            $page = max(1, (int) $request->get('page', 1));
            $perPage = min(200, max(1, (int) $request->get('per_page', 50)));
            $type = $request->get('type');

            $indicators = $this->getIndicatorsByPulseId($pulse_id, $page, $perPage, $type);

            // Get total count
            $query = ['pulse_id' => $pulse_id];
            if ($type) {
                $query['type'] = $type;
            }
            $totalItems = $this->db->fx_otx_events_indicator_ref->countDocuments($query);
            $totalPages = ceil($totalItems / $perPage);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'pulse_id' => $pulse_id,
                    'indicators' => $indicators,
                    'pagination' => [
                        'current_page' => $page,
                        'total_pages' => $totalPages,
                        'total_items' => $totalItems,
                        'per_page' => $perPage,
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/v1/public/indicators
     * ดึงรายการ Indicators ทั้งหมด
     * 
     * @param timestamp string - Dynamic time filter (e.g., 6h, 24h, 1d, 3d, 1w, 3w, 1m, 3m)
     *                          Format: {number}{unit} where unit is h(hours), d(days), w(weeks), m(months)
     */
    public function listIndicators(Request $request)
    {
        try {
            $page = max(1, (int) $request->get('page', 1));
            $perPage = min(200, max(1, (int) $request->get('per_page', 50)));
            $type = $request->get('type');
            $source = $request->get('source');
            $timestamp = $request->get('timestamp'); // e.g., 6h, 24h, 1d, 3d, 1w, 3w, 1m, 3m
            $skip = ($page - 1) * $perPage;

            $query = [];
            if ($type) {
                $query['type'] = $type;
            }
            if ($source) {
                $query['source'] = $source;
            }

            // Dynamic timestamp filtering
            if ($timestamp) {
                $cutoffDate = $this->parseTimestamp($timestamp);
                if ($cutoffDate) {
                    $query['updated_at'] = ['$gte' => new \MongoDB\BSON\UTCDateTime($cutoffDate->getTimestamp() * 1000)];
                }
            }

            $col = $this->db->fx_otx_indicator_detail;

            $totalItems = $col->countDocuments($query);
            $totalPages = ceil($totalItems / $perPage);

            $cursor = $col->find($query, [
                'skip' => $skip,
                'limit' => $perPage,
                'sort' => ['updated_at' => -1],
                'projection' => [
                    'indicator_id' => 1,
                    'indicator_name' => 1,
                    'type' => 1,
                    'source' => 1,
                    'created_at' => 1,
                    'updated_at' => 1,
                ]
            ]);

            $indicators = [];
            foreach ($cursor as $doc) {
                $indicators[] = [
                    'indicator_id' => $doc['indicator_id'] ?? null,
                    'indicator' => $doc['indicator_name'] ?? null,
                    'type' => $doc['type'] ?? null,
                    'source' => $doc['source'] ?? null,
                    'created_at' => isset($doc['created_at']) ? $this->formatDate($doc['created_at']) : null,
                    'updated_at' => isset($doc['updated_at']) ? $this->formatDate($doc['updated_at']) : null,
                    'modified_date' => isset($doc['updated_at']) ? $this->formatDate($doc['updated_at']) : null,
                ];
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'indicators' => $indicators,
                    'pagination' => [
                        'current_page' => $page,
                        'total_pages' => $totalPages,
                        'total_items' => $totalItems,
                        'per_page' => $perPage,
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper: Parse dynamic timestamp string to DateTime
     * Supports formats like: 6h, 24h, 1d, 3d, 1w, 3w, 1m, 3m, etc.
     * 
     * @param string $timestamp - Format: {number}{unit}
     * @return \DateTime|null
     */
    private function parseTimestamp($timestamp)
    {
        // Match pattern: number followed by unit (h=hours, d=days, w=weeks, m=months)
        if (!preg_match('/^(\d+)([hdwm])$/i', $timestamp, $matches)) {
            return null;
        }

        $value = (int) $matches[1];
        $unit = strtolower($matches[2]);

        $now = new \DateTime();

        switch ($unit) {
            case 'h': // hours
                $now->modify("-{$value} hours");
                break;
            case 'd': // days
                $now->modify("-{$value} days");
                break;
            case 'w': // weeks
                $now->modify("-{$value} weeks");
                break;
            case 'm': // months
                $now->modify("-{$value} months");
                break;
            default:
                return null;
        }

        return $now;
    }

    /**
     * Helper: Get indicators by pulse_id
     */
    private function getIndicatorsByPulseId($pulse_id, $page = 1, $perPage = 100, $type = null)
    {
        $skip = ($page - 1) * $perPage;

        $query = ['pulse_id' => $pulse_id];
        if ($type) {
            $query['type'] = $type;
        }

        $col = $this->db->fx_otx_events_indicator_ref;

        $cursor = $col->find($query, [
            'skip' => $skip,
            'limit' => $perPage,
            'sort' => ['created' => -1],
        ]);

        $indicators = [];
        $indicatorCol = $this->db->fx_otx_indicator_detail;

        foreach ($cursor as $ref) {
            $indicatorId = $ref['indicator_id'] ?? null;
            if (!$indicatorId) continue;

            // Get indicator detail
            $detail = $indicatorCol->findOne(['indicator_id' => $indicatorId]);

            // Parse tags string to array (e.g., "type:Four, type:Five,type:Six" -> ["type:Four", "type:Five", "type:Six"])
            $tagsRaw = $ref['tags'] ?? '';
            $tagsArray = $tagsRaw ? array_map('trim', explode(',', $tagsRaw)) : [];

            $indicators[] = [
                'indicator_id' => $indicatorId,
                'indicator' => $ref['indicator'] ?? ($ref['indicator_name'] ?? ($detail['indicator_name'] ?? null)),
                'type' => $ref['type'] ?? ($detail['type'] ?? null),
                'score' => $ref['attribute_score'] ?? null,
                'tags' => $tagsArray,
                'severity' => $ref['attribute_serverity'] ?? null,
                'created' => isset($ref['created']) ? $this->formatDate($ref['created']) : null,
            ];
        }

        return $indicators;
    }

    /**
     * Helper: Format MongoDB date to ISO string
     */
    private function formatDate($date)
    {
        if ($date instanceof \MongoDB\BSON\UTCDateTime) {
            return $date->toDateTime()->format('Y-m-d\TH:i:s\Z');
        }
        return $date;
    }
}
