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
     * Receive Event(s) in MISP-like format
     * 
     * Supports both single Event and multiple Events:
     * 
     * Single Event format:
     * {
     *   "dry_run": false,
     *   "Event": {
     *     "id": "custom-id-001",
     *     "info": "Event Title",
     *     "Attribute": [...]
     *   }
     * }
     * 
     * Multiple Events format:
     * {
     *   "dry_run": false,
     *   "Events": [
     *     { "id": "001", "info": "Event 1", "Attribute": [...] },
     *     { "id": "002", "info": "Event 2", "Attribute": [...] }
     *   ]
     * }
     */
    public function receiveEvent(Request $request)
    {
        $dryRun = filter_var($request->input('dry_run', false), FILTER_VALIDATE_BOOLEAN);
        $siteId = $request->attributes->get('site_id');

        // Check for explicit multiple events format first (Events key)
        $eventsInput = $request->input('Events') ?? $request->input('events');
        
        if ($eventsInput && is_array($eventsInput)) {
            return $this->processMultipleEvents($eventsInput, $siteId, $dryRun);
        }

        // Support both "Event" (MISP style) and "event" (simple style)
        $eventInput = $request->input('Event') ?? $request->input('event');
        
        if (!$eventInput) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => 'Event data is required (use "Event" or "Events" key)'
            ], 422);
        }

        // Check if Event is an array of events (user passed multiple events via "Event" key)
        if (is_array($eventInput) && isset($eventInput[0])) {
            // It's an indexed array = multiple events
            return $this->processMultipleEvents($eventInput, $siteId, $dryRun);
        }

        // Process single event
        return $this->processSingleEvent($eventInput, $siteId, $dryRun);
    }

    /**
     * Bulk receive Events endpoint (explicit bulk endpoint)
     * 
     * Expected JSON format:
     * {
     *   "dry_run": false,
     *   "Events": [
     *     { "id": "001", "info": "Event 1", "Attribute": [...] },
     *     { "id": "002", "info": "Event 2", "Attribute": [...] }
     *   ]
     * }
     */
    public function receiveEventsBulk(Request $request)
    {
        $dryRun = filter_var($request->input('dry_run', false), FILTER_VALIDATE_BOOLEAN);
        $siteId = $request->attributes->get('site_id');

        $eventsInput = $request->input('Events') ?? $request->input('events');
        
        if (!$eventsInput || !is_array($eventsInput)) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => 'Events array is required (use "Events" or "events" key)'
            ], 422);
        }

        if (empty($eventsInput)) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => 'Events array cannot be empty'
            ], 422);
        }

        return $this->processMultipleEvents($eventsInput, $siteId, $dryRun);
    }

    /**
     * Process multiple events
     */
    private function processMultipleEvents(array $eventsInput, $siteId, bool $dryRun)
    {
        $results = [];
        $errors = [];
        $successCount = 0;
        $errorCount = 0;

        foreach ($eventsInput as $index => $eventInput) {
            try {
                $result = $this->processSingleEventInternal($eventInput, $siteId, $dryRun);
                $results[] = $result;
                $successCount++;
            } catch (\Exception $e) {
                $errorCount++;
                $errors[] = [
                    'index' => $index,
                    'event_id' => $eventInput['id'] ?? null,
                    'event_name' => $eventInput['info'] ?? $eventInput['name'] ?? 'unknown',
                    'error' => $e->getMessage()
                ];
            }
        }

        $statusCode = $errorCount === 0 ? 201 : ($successCount > 0 ? 207 : 422);
        
        return response()->json([
            'success' => $errorCount === 0,
            'dry_run' => $dryRun,
            'message' => $dryRun 
                ? "Dry run - preview for {$successCount} event(s)" 
                : "Processed {$successCount} event(s) successfully" . ($errorCount > 0 ? ", {$errorCount} failed" : ""),
            'summary' => [
                'total' => count($eventsInput),
                'success' => $successCount,
                'failed' => $errorCount,
            ],
            'data' => $results,
            'errors' => $errors,
        ], $statusCode);
    }

    /**
     * Process single event (public wrapper for backward compatibility)
     */
    private function processSingleEvent(array $eventInput, $siteId, bool $dryRun)
    {
        // Validate required fields
        $eventName = $eventInput['info'] ?? $eventInput['name'] ?? null;
        if (!$eventName) {
            return response()->json([
                'error' => 'Validation failed', 
                'message' => 'Event info/name is required'
            ], 422);
        }

        try {
            $result = $this->processSingleEventInternal($eventInput, $siteId, $dryRun);
            
            if ($dryRun) {
                return response()->json([
                    'success' => true,
                    'dry_run' => true,
                    'message' => 'Dry run - data preview (not saved)',
                    'data' => $result
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'Event received successfully',
                'data' => $result
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to save event',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Internal method to process a single event
     * Returns processed data or throws exception on error
     */
    private function processSingleEventInternal(array $eventInput, $siteId, bool $dryRun): array
    {
        $eventName = $eventInput['info'] ?? $eventInput['name'] ?? null;
        if (!$eventName) {
            throw new \Exception('Event info/name is required');
        }

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
            $attrCreatedAt = $this->parseTimestamp($attr['created_at'] ?? $attr['created'] ?? $attr['timestamp'] ?? null, $dateNowStr);
            $attrUpdatedAt = $this->parseTimestamp($attr['modified'] ?? null, $dateNowStr);
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
                'attribute_score' => $attr['score'] ?? null,
                'attribute_serverity' => $attr['severity'] ?? null,
                'attribute_confidence' => $attr['confidence'] ?? null,
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
                'created_at' => $attrCreatedAt,
                'created_by' => 'system',
                'updated_at' => $attrUpdatedAt,
                'updated_by' => 'system',
                'deleted_at' => null,
                'imported_at' => $dateNowStr,
            ];

            $indicatorRefDocuments[] = [
                'indicator_id' => $indicatorId,
                'pulse_id' => $pulseId,
                'indicator' => $attrValue,
                'type' => $attrType,
                'role' => $attrCategory,
                'to_ids' => $attr['to_ids'] ?? true,
                'comment' => $attr['comment'] ?? null,
                'attribute_score' => $attr['score'] ?? null,
                'attribute_serverity' => $attr['severity'] ?? null,
                'attribute_confidence' => $attr['confidence'] ?? null,
                'tags' => $attrTags,
                'is_active' => $attr['is_active'] ?? 1,
                'is_count_attr' => 1,
                'created' => $attrCreatedAt,
                'expiration' => $attrExpiration,
                'pulse_modified' => $modifiedDate,
                'creator_org' => $attrCreatorOrg,
                'source' => 'service_api',
                'status' => 1,
                'transaction_date' => $dateNow,
                'created_at' => $attrCreatedAt,
                'created_by' => 'system',
                'updated_at' => $attrUpdatedAt,
                'updated_by' => 'system',
                'deleted_at' => null,
                'imported_at' => $dateNowStr,
            ];
        }

        // Dry run - return preview data
        if ($dryRun) {
            return [
                'pulse_id' => $pulseId,
                'event_name' => $eventName,
                'indicators_count' => count($attributes),
                'preview' => [
                    'fx_otx_events' => $eventDocument,
                    'fx_otx_indicator_detail' => $indicatorDetailsDocuments,
                    'fx_otx_events_indicator_ref' => $indicatorRefDocuments,
                ]
            ];
        }

        // Check MongoDB connection
        if (!$this->mongoClient) {
            throw new \Exception('Database connection failed');
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

        return [
            'pulse_id' => $pulseId,
            'event_name' => $eventName,
            'indicators_count' => count($attributes),
        ];
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
