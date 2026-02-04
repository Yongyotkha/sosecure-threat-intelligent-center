<?php

namespace App\Http\Controllers\Api;

use App\ApiToken;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use MongoDB\BSON\UTCDateTime;
use Illuminate\Support\Str;

class ServiceApiController extends Controller
{
    protected $mongoClient;
    protected $dbName = 'sosecure_threatintelligent_dev';

    public function __construct()
    {
        $DB_MONGO_KEY = config('app.DB_MONGO_DEV') ?: env('DB_MONGO_STOREDATA', '');
        if ($DB_MONGO_KEY) {
            $this->mongoClient = new \MongoDB\Client($DB_MONGO_KEY);
        }
    }

    /**
     * Receive Event in MISP-like format
     * 
     * Expected JSON format:
     * {
     *   "dry_run": false,
     *   "Event": {
     *     "id": "custom-id-001",
     *     "info": "Event Title",
     *     "date": "2026-01-20",
     *     "published": true,
     *     "publish_timestamp": 1705756800,
     *     "timestamp": 1705756800,
     *     "threat_level_id": 2,
     *     "analysis": 2,
     *     "orgc_id": "ORG_NAME",
     *     "org_id": "ORG_NAME",
     *     "Tag": [
     *       {"name": "tlp:white"},
     *       {"name": "apt"}
     *     ],
     *     "Attribute": [
     *       {
     *         "id": "attr-001",
     *         "type": "domain",
     *         "value": "evil.com",
     *         "category": "Network activity",
     *         "timestamp": 1705756800,
     *         "to_ids": true,
     *         "comment": "C2 server",
     *         "score": 85,
     *         "severity": "high",
     *         "Tag": [{"name": "malware"}]
     *       }
     *     ]
     *   }
     * }
     */
    public function receiveEvent(Request $request)
    {
        $dryRun = filter_var($request->input('dry_run', false), FILTER_VALIDATE_BOOLEAN);

        // Support both "Event" (MISP style) and "event" (simple style)
        $eventInput = $request->input('Event') ?? $request->input('event');
        
        if (!$eventInput) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => 'Event data is required (use "Event" or "event" key)'
            ], 422);
        }

        // Validate required fields
        $eventName = $eventInput['info'] ?? $eventInput['name'] ?? null;
        if (!$eventName) {
            return response()->json([
                'error' => 'Validation failed', 
                'message' => 'Event info/name is required'
            ], 422);
        }

        try {
            $siteId = $request->attributes->get('site_id');
            $dateNowStr = date("Y-m-d H:i:s");
            $dateNow = date("Y-m-d");

            // Parse Event data
            $eventId = $eventInput['id'] ?? null;
            $pulseId = $eventId ? ('svc.' . $eventId) : ('svc_' . Str::uuid()->toString());

            // Handle timestamps
            $createdDate = $this->parseTimestamp($eventInput['date'] ?? $eventInput['created'] ?? null, $dateNowStr);
            $modifiedDate = $this->parseTimestamp($eventInput['timestamp'] ?? $eventInput['modified'] ?? null, $dateNowStr);
            $publishTimestamp = $this->parseTimestamp($eventInput['publish_timestamp'] ?? null, null);

            // Parse Tags
            $tags = $this->parseTags($eventInput['Tag'] ?? $eventInput['tags'] ?? []);
            $tlpColor = $this->extractTLP($eventInput['Tag'] ?? $eventInput['tags'] ?? [], $eventInput['TLP'] ?? 'white');

            // Get attributes
            $attributes = $eventInput['Attribute'] ?? $eventInput['attributes'] ?? [];

            // Build event document
            $eventDocument = [
                'pulse_id' => $pulseId,
                'name' => $eventName,
                'description' => $eventInput['info'] ?? $eventInput['description'] ?? '',
                'TLP' => $tlpColor,
                'tags' => $tags,
                'references' => $this->formatArrayField($eventInput['references'] ?? null),
                'industries' => $this->formatArrayField($eventInput['industries'] ?? null),
                'groups' => $eventInput['groups'] ?? '',
                'malware_families' => $this->formatArrayField($eventInput['malware_families'] ?? null),
                'author_username' => $eventInput['author_username'] ?? null,
                'public' => isset($eventInput['published']) ? ($eventInput['published'] ? 1 : 0) : 1,
                'is_modified' => isset($eventInput['timestamp'], $eventInput['publish_timestamp']) 
                    ? ($eventInput['timestamp'] != $eventInput['publish_timestamp']) 
                    : false,
                'created' => $createdDate,
                'modified' => $modifiedDate,
                'publish_timestamp' => $publishTimestamp,
                'threat_level_id' => $eventInput['threat_level_id'] ?? null,
                'analysis' => $eventInput['analysis'] ?? null,
                'indicator_count' => count($attributes),
                'indicator_type_counts' => $this->countIndicatorTypes($attributes),
                'count_view' => 0,
                'count_related_pulse' => null,
                'creator_org' => $eventInput['orgc_id'] ?? $eventInput['creator_org'] ?? 'SERVICE_API',
                'org_id' => $eventInput['org_id'] ?? null,
                'source' => 'service_api',
                'status' => 1,
                'site_id' => $siteId,
                'transaction_date' => $dateNow,
                'transaction_id' => '',
                'created_at' => $dateNowStr,
                'created_by' => 'system',
                'updated_at' => $dateNowStr,
                'updated_by' => 'system',
                'deleted_at' => null,
            ];

            // Build indicator documents
            $indicatorDetailsDocuments = [];
            $indicatorRefDocuments = [];

            foreach ($attributes as $attr) {
                $attrId = $attr['id'] ?? null;
                $indicatorId = $attrId ? ('svc.' . $attrId) : ('svc_ind_' . Str::uuid()->toString());
                
                $attrValue = $attr['value'] ?? $attr['indicator'] ?? '';
                $attrType = $attr['type'] ?? 'unknown';
                $attrCategory = $attr['category'] ?? $attr['role'] ?? 'Network activity';
                $attrTimestamp = $this->parseTimestamp($attr['timestamp'] ?? $attr['created'] ?? null, $dateNowStr);
                $attrExpiration = $this->parseTimestamp($attr['expiration'] ?? null, null);
                $attrCreatorOrg = $attr['creator_org'] ?? $eventDocument['creator_org'];

                // Parse attribute tags
                $attrTags = $this->parseTags($attr['Tag'] ?? $attr['tags'] ?? []);

                $indicatorDetailsDocuments[] = [
                    'indicator_id' => $indicatorId,
                    'indicator_name' => $attrValue,
                    'type' => $attrType,
                    'category' => $attrCategory,
                    'to_ids' => $attr['to_ids'] ?? true,
                    'comment' => $attr['comment'] ?? null,
                    'score' => $attr['score'] ?? null,
                    'severity' => $attr['severity'] ?? null,
                    'confidence' => $attr['confidence'] ?? null,
                    'tags' => $attrTags,
                    'allrow' => [
                        'detail' => $attrValue,
                        'comment' => $attr['comment'] ?? null,
                    ],
                    'creator_org' => $attrCreatorOrg,
                    'source' => 'service_api',
                    'status' => 1,
                    'transaction_date' => $dateNow,
                    'transaction_id' => '',
                    'created_at' => $attrTimestamp,
                    'created_by' => 'system',
                    'updated_at' => $dateNowStr,
                    'updated_by' => 'system',
                    'deleted_at' => null,
                ];

                $indicatorRefDocuments[] = [
                    'indicator_id' => $indicatorId,
                    'pulse_id' => $pulseId,
                    'indicator' => $attrValue,
                    'type' => $attrType,
                    'role' => $attrCategory,
                    'to_ids' => $attr['to_ids'] ?? true,
                    'comment' => $attr['comment'] ?? null,
                    'score' => $attr['score'] ?? null,
                    'severity' => $attr['severity'] ?? null,
                    'confidence' => $attr['confidence'] ?? null,
                    'tags' => $attrTags,
                    'is_active' => $attr['is_active'] ?? 1,
                    'is_count_attr' => 1,
                    'created' => $attrTimestamp,
                    'expiration' => $attrExpiration,
                    'pulse_modified' => $modifiedDate,
                    'creator_org' => $attrCreatorOrg,
                    'source' => 'service_api',
                    'status' => 1,
                    'transaction_date' => $dateNow,
                    'created_at' => $attrTimestamp,
                    'created_by' => 'system',
                    'updated_at' => $dateNowStr,
                    'updated_by' => 'system',
                    'deleted_at' => null,
                ];
            }

            // Dry run - return preview
            if ($dryRun) {
                return response()->json([
                    'success' => true,
                    'dry_run' => true,
                    'message' => 'Dry run - data preview (not saved)',
                    'data' => [
                        'fx_otx_events' => $eventDocument,
                        'fx_otx_indicator_detail' => $indicatorDetailsDocuments,
                        'fx_otx_events_indicator_ref' => $indicatorRefDocuments,
                    ]
                ], 200);
            }

            // Check MongoDB connection
            if (!$this->mongoClient) {
                return response()->json(['error' => 'Database connection failed'], 500);
            }

            // Convert dates to MongoDB UTCDateTime
            $eventDocument = $this->convertDatesToMongo($eventDocument, 
                ['created', 'modified', 'publish_timestamp', 'created_at', 'updated_at']);

            $col_events = $this->mongoClient->{$this->dbName}->fx_otx_events;
            $col_indicator_ref = $this->mongoClient->{$this->dbName}->fx_otx_events_indicator_ref;
            $col_indicator_detail = $this->mongoClient->{$this->dbName}->fx_otx_indicator_detail;

            // Upsert event
            $col_events->updateOne(
                ['pulse_id' => $pulseId],
                ['$set' => $eventDocument],
                ['upsert' => true]
            );

            // Upsert indicators
            foreach ($indicatorDetailsDocuments as $doc) {
                $doc = $this->convertDatesToMongo($doc, ['created_at', 'updated_at']);
                $col_indicator_detail->updateOne(
                    ['indicator_id' => $doc['indicator_id']],
                    ['$set' => $doc],
                    ['upsert' => true]
                );
            }

            foreach ($indicatorRefDocuments as $doc) {
                $doc = $this->convertDatesToMongo($doc, ['created', 'pulse_modified', 'created_at', 'updated_at', 'expiration']);
                $col_indicator_ref->updateOne(
                    ['indicator_id' => $doc['indicator_id'], 'pulse_id' => $doc['pulse_id']],
                    ['$set' => $doc],
                    ['upsert' => true]
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Event received successfully',
                'data' => [
                    'pulse_id' => $pulseId,
                    'event_name' => $eventName,
                    'indicators_count' => count($attributes),
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to save event',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function parseTimestamp($value, $default)
    {
        if ($value === null) return $default;
        
        // If it's a Unix timestamp (numeric)
        if (is_numeric($value)) {
            return date("Y-m-d H:i:s", $value);
        }
        
        // If it's already a date string
        return $value;
    }

    private function parseTags($tags): string
    {
        if (empty($tags)) return '';
        
        $tagNames = [];
        
        // Handle array of objects [{name: "tag1"}, {name: "tag2"}]
        if (is_array($tags)) {
            foreach ($tags as $tag) {
                if (is_array($tag) && isset($tag['name'])) {
                    // Skip TLP tags
                    if (strpos(strtolower($tag['name']), 'tlp:') === false) {
                        $tagNames[] = $tag['name'];
                    }
                } elseif (is_string($tag)) {
                    if (strpos(strtolower($tag), 'tlp:') === false) {
                        $tagNames[] = $tag;
                    }
                }
            }
        }
        
        return implode(', ', $tagNames);
    }

    private function extractTLP($tags, $default = 'white'): string
    {
        if (empty($tags)) return $default;
        
        if (is_array($tags)) {
            foreach ($tags as $tag) {
                $tagName = is_array($tag) ? ($tag['name'] ?? '') : $tag;
                if (stripos($tagName, 'tlp:') !== false) {
                    return strtolower(str_replace('tlp:', '', $tagName));
                }
            }
        }
        
        return $default;
    }

    private function formatArrayField($value): string
    {
        if (is_array($value)) {
            return implode(', ', $value);
        }
        return $value ?? '';
    }

    private function countIndicatorTypes(array $attributes): array
    {
        $counts = [];
        foreach ($attributes as $attr) {
            $type = $attr['type'] ?? 'unknown';
            $counts[$type] = ($counts[$type] ?? 0) + 1;
        }
        return $counts;
    }

    private function convertDatesToMongo(array $doc, array $dateFields): array
    {
        foreach ($dateFields as $field) {
            if (isset($doc[$field]) && $doc[$field] !== null && $doc[$field] !== '') {
                $timestamp = is_numeric($doc[$field]) ? $doc[$field] : strtotime($doc[$field]);
                if ($timestamp !== false) {
                    $doc[$field] = new UTCDateTime($timestamp * 1000);
                }
            }
        }
        return $doc;
    }
}
