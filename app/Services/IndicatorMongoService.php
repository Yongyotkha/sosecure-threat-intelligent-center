<?php

namespace App\Services;

use Carbon\Carbon;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Database;

class IndicatorMongoService
{
    protected $client;

    protected $database;

    public function getUri(): string
    {
        foreach ($this->uriCandidates() as $uri) {
            if ($uri !== '') {
                return $uri;
            }
        }

        return '';
    }

    public function getDatabaseName(): string
    {
        foreach ($this->databaseCandidates() as $name) {
            if ($name !== '') {
                return $name;
            }
        }

        return 'sosecure_threatintelligent_dev';
    }

    /**
     * @return string[]
     */
    protected function uriCandidates(): array
    {
        return array_map('trim', array_map('strval', [
            config('mongodb.indicator.uri'),
            config('app.DB_MONGO_DEV'),
            config('app.DB_MONGO_STOREDATA'),
            config('app.DB_MONGO'),
            env('DB_MONGO_DEV'),
            env('DB_MONGO_STOREDATA'),
            env('DB_MONGO_STOREDATAB'),
            env('DB_MONGO'),
        ]));
    }

    /**
     * @return string[]
     */
    protected function databaseCandidates(): array
    {
        return array_map('trim', array_map('strval', [
            config('mongodb.indicator.database'),
            env('MONGO_DATABASE'),
        ]));
    }

    public function getClient(): Client
    {
        if ($this->client === null) {
            $uri = $this->getUri();
            if ($uri === '') {
                throw new \RuntimeException(
                    'MongoDB URI is not set. Add DB_MONGO_DEV (or DB_MONGO_STOREDATA / DB_MONGO_STOREDATAB) to .env and run php artisan config:clear.'
                );
            }
            $this->client = new Client($uri);
        }

        return $this->client;
    }

    public function getDatabase(): Database
    {
        if ($this->database === null) {
            $this->database = $this->getClient()->selectDatabase($this->getDatabaseName());
        }

        return $this->database;
    }

    public function collection(string $name): Collection
    {
        return $this->getDatabase()->selectCollection($name);
    }

    public function getProfile(): array
    {
        return [
            'uri' => $this->getUri(),
            'database' => $this->getDatabaseName(),
        ];
    }

    /**
     * Match only active (non-deleted) events — same filter as the events DataTable.
     */
    public static function activeEventsFilter(): array
    {
        return [
            '$or' => [
                ['deleted_at' => null],
                ['deleted_at' => ['$exists' => false]],
            ],
        ];
    }

    public static function mergeActiveEventsFilter(array $query = []): array
    {
        if ($query === []) {
            return self::activeEventsFilter();
        }

        return ['$and' => [self::activeEventsFilter(), $query]];
    }

    public static function hasEventFilters(array $input): bool
    {
        if (!empty($input['keywords']) || !empty($input['keyword_search']) || !empty($input['industries'])
            || !empty($input['groups']) || !empty($input['check_published'])) {
            return true;
        }

        if (filter_var($input['isDateSearch'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            return !empty($input['startDate']) || !empty($input['endDate']);
        }

        return false;
    }

    /**
     * Same event filter logic as IndicatorsController@datatableEvent.
     */
    public static function buildEventsQueryFromInput(array $input): array
    {
        $query = self::activeEventsFilter();

        if (!self::hasEventFilters($input)) {
            return $query;
        }

        if (!empty($input['keywords'])) {
            $query['name'] = ['$regex' => $input['keywords'], '$options' => 'i'];
        }
        if (!empty($input['industries'])) {
            $query['industries'] = ['$regex' => $input['industries'], '$options' => 'i'];
        }
        if (!empty($input['groups'])) {
            $query['groups'] = ['$regex' => $input['groups'], '$options' => 'i'];
        }
        if (!empty($input['keyword_search'])) {
            $query['$or'] = [
                ['name' => ['$regex' => $input['keyword_search'], '$options' => 'i']],
                ['groups' => ['$regex' => $input['keyword_search'], '$options' => 'i']],
                ['source' => ['$regex' => $input['keyword_search'], '$options' => 'i']],
                ['creator_org' => ['$regex' => $input['keyword_search'], '$options' => 'i']],
                ['tags' => ['$regex' => $input['keyword_search'], '$options' => 'i']],
                ['pulse_id' => ['$regex' => $input['keyword_search'], '$options' => 'i']],
            ];
        }

        $isDateSearch = filter_var($input['isDateSearch'] ?? false, FILTER_VALIDATE_BOOLEAN);
        if ($isDateSearch) {
            if (!empty($input['startDate']) && !empty($input['endDate'])) {
                $query['modified'] = [
                    '$gt' => new UTCDateTime(strtotime($input['startDate']) * 1000),
                    '$lte' => new UTCDateTime(strtotime($input['endDate']) * 1000),
                ];
            } elseif (!empty($input['startDate'])) {
                $query['modified'] = ['$gt' => new UTCDateTime(strtotime($input['startDate']) * 1000)];
            } elseif (!empty($input['endDate'])) {
                $query['modified'] = ['$lte' => new UTCDateTime(strtotime($input['endDate']) * 1000)];
            }
        }

        if (!empty($input['check_published'])) {
            if ((string) $input['check_published'] === '1') {
                $query['public'] = ['$in' => [1, '1']];
            } elseif ((string) $input['check_published'] === '2') {
                $query['public'] = ['$in' => [0, '0']];
            }
        }

        return $query;
    }

    /**
     * Live dashboard stats from MongoDB (same logic as app:MDCountIndicator).
     */
    public function getDashboardStats(array $filters = []): array
    {
        $colEvents = $this->collection('fx_otx_events');
        $colAttr = $this->collection('fx_otx_indicator_detail');
        $aggregateOptions = ['allowDiskUse' => true];
        $eventQuery = self::buildEventsQueryFromInput($filters);
        $hasFilters = self::hasEventFilters($filters);

        if (!$hasFilters) {
            $activeEventsFilter = self::activeEventsFilter();
            $since = new UTCDateTime(Carbon::now('UTC')->subDays(1));
            $currentAttrQuery = ['created_at' => ['$gt' => $since]];
            $currentEventQuery = self::mergeActiveEventsFilter(['created_at' => ['$gt' => $since]]);

            $attrCurrent = (int) $colAttr->countDocuments($currentAttrQuery);
            $eventCurrent = (int) $colEvents->countDocuments($currentEventQuery);
            $attrAll = (int) $colAttr->countDocuments([]);
            $eventAll = (int) $colEvents->countDocuments($activeEventsFilter);
            $attrType = $this->aggregateAttributeTypes($colAttr, [], $aggregateOptions);

            return [
                'attr_all' => (object) [
                    'event_count' => $eventAll,
                    'attribute_count' => $attrAll,
                ],
                'attr_current' => (object) [
                    'event_count' => $eventCurrent,
                    'attribute_count' => $attrCurrent,
                ],
                'attr_type' => $attrType,
                'filtered' => false,
            ];
        }

        $since = new UTCDateTime(Carbon::now('UTC')->subDays(1));
        $eventAll = (int) $colEvents->countDocuments($eventQuery);
        $attrAll = $this->sumIndicatorCount($colEvents, $eventQuery, $aggregateOptions);

        $currentEventQuery = $eventQuery;
        $currentEventQuery['created_at'] = ['$gt' => $since];
        $eventCurrent = (int) $colEvents->countDocuments($currentEventQuery);
        $attrCurrent = $this->sumIndicatorCount($colEvents, $currentEventQuery, $aggregateOptions);
        $attrType = $this->aggregateAttributeTypesFromEvents($colEvents, $eventQuery, $aggregateOptions);

        return [
            'attr_all' => (object) [
                'event_count' => $eventAll,
                'attribute_count' => $attrAll,
            ],
            'attr_current' => (object) [
                'event_count' => $eventCurrent,
                'attribute_count' => $attrCurrent,
            ],
            'attr_type' => $attrType,
            'filtered' => true,
        ];
    }

    protected function sumIndicatorCount(Collection $colEvents, array $eventQuery, array $options): int
    {
        $pipeline = [
            ['$match' => $eventQuery],
            [
                '$group' => [
                    '_id' => null,
                    'total' => ['$sum' => ['$ifNull' => ['$indicator_count', 0]]],
                ],
            ],
        ];

        $result = $colEvents->aggregate($pipeline, $options)->toArray();

        return (int) ($result[0]['total'] ?? 0);
    }

    protected function aggregateAttributeTypes(Collection $colAttr, array $match, array $options): array
    {
        $pipeline = [
            ['$group' => ['_id' => '$type', 'count' => ['$sum' => 1]]],
            ['$match' => ['_id' => ['$nin' => [null, '']]]],
            ['$sort' => ['count' => -1]],
            ['$limit' => 10],
        ];

        if ($match !== []) {
            array_unshift($pipeline, ['$match' => $match]);
        }

        $attrType = [];
        foreach ($colAttr->aggregate($pipeline, $options) as $doc) {
            $attrType[] = [
                'name' => ucwords((string) $doc['_id']),
                'data' => [(int) $doc['count']],
            ];
        }

        return $attrType;
    }

    protected function aggregateAttributeTypesFromEvents(Collection $colEvents, array $eventQuery, array $options): array
    {
        $pipeline = [
            ['$match' => $eventQuery],
            [
                '$lookup' => [
                    'from' => 'fx_otx_events_indicator_ref',
                    'localField' => 'pulse_id',
                    'foreignField' => 'pulse_id',
                    'as' => 'attrs',
                ],
            ],
            ['$unwind' => '$attrs'],
            ['$group' => ['_id' => '$attrs.type', 'count' => ['$sum' => 1]]],
            ['$match' => ['_id' => ['$nin' => [null, '']]]],
            ['$sort' => ['count' => -1]],
            ['$limit' => 10],
        ];

        $attrType = [];
        foreach ($colEvents->aggregate($pipeline, $options) as $doc) {
            $attrType[] = [
                'name' => ucwords((string) $doc['_id']),
                'data' => [(int) $doc['count']],
            ];
        }

        return $attrType;
    }

    /**
     * Load event header + type-count stats for the events detail page.
     * Falls back to honeypot publish DB and indicator refs when fx_otx_events is missing.
     */
    public function resolveEventDetailPayload(string $pulseId, ?string $startDate = null, ?string $endDate = null): array
    {
        $typeMap = [
            'root' => 'array',
            'document' => 'array',
            'array' => 'array',
        ];

        $eventsCol = $this->collection('fx_otx_events');
        $indicatorRefCol = $this->collection('fx_otx_events_indicator_ref');

        $event = $eventsCol->findOne(['pulse_id' => $pulseId], ['typeMap' => $typeMap]);

        if (empty($event) && strpos($pulseId, 'honeypot.') === 0) {
            try {
                $honeypot = app(HoneypotMongoService::class);
                $event = $honeypot->indicatorCollection('events')->findOne(
                    ['pulse_id' => $pulseId],
                    ['typeMap' => $typeMap]
                );
            } catch (\Throwable $e) {
                // Honeypot Mongo profile may be unavailable in some environments.
            }
        }

        $refCount = $indicatorRefCol->countDocuments(['pulse_id' => $pulseId]);

        if (empty($event) && $refCount > 0 && strpos($pulseId, 'honeypot.') === 0) {
            $event = $this->buildHoneypotEventFallback($pulseId, $refCount);
        } elseif (!empty($event) && strpos($pulseId, 'honeypot.') === 0) {
            $event = $this->applyHoneypotEventDefaults($event, $refCount);
        }

        $countKey = [];
        $countVal = [];
        $hasValidKeys = false;
        $typeCounts = $event['indicator_type_counts'] ?? null;

        if (!empty($typeCounts)) {
            $typeCountsArray = (array) $typeCounts;
            $firstKey = array_key_first($typeCountsArray);
            $hasValidKeys = $firstKey !== null && !is_numeric($firstKey);

            if ($hasValidKeys) {
                foreach ($typeCountsArray as $key => $value) {
                    $countKey[] = ucwords((string) $key);
                    $countVal[] = $value;
                }
            }
        }

        $hasDateFilter = $startDate && $endDate;

        if (!$hasValidKeys || $hasDateFilter || empty($countVal)) {
            $matchQuery = ['pulse_id' => $pulseId];

            if ($hasDateFilter) {
                $matchQuery['updated_at'] = [
                    '$gte' => new UTCDateTime(strtotime($startDate) * 1000),
                    '$lte' => new UTCDateTime(strtotime($endDate) * 1000),
                ];
            }

            $pipeline = [
                ['$match' => $matchQuery],
                ['$group' => ['_id' => '$type', 'count' => ['$sum' => 1]]],
                ['$sort' => ['count' => -1]],
            ];

            if ($hasDateFilter) {
                $countKey = [];
                $countVal = [];
            }

            foreach ($indicatorRefCol->aggregate($pipeline) as $item) {
                if ($item instanceof \MongoDB\Model\BSONDocument) {
                    $item = $item->getArrayCopy();
                }

                if (!empty($item['_id'])) {
                    $countKey[] = ucwords((string) $item['_id']);
                    $countVal[] = $item['count'];
                }
            }
        }

        $indicatorTypeCounts = !empty($countKey)
            ? count($countKey)
            : (isset($event['indicator_type_counts']) ? count((array) $event['indicator_type_counts']) : 0);

        $payload = [
            'otx_events' => $event ? [$event] : [],
            'indicator_type_counts' => $indicatorTypeCounts,
            'count_related_pulse' => $event['count_related_pulse'] ?? 0,
            'countKey' => $countKey,
            'countVal' => $countVal,
        ];

        if (!empty($countVal)) {
            $payload['actual_indicator_count'] = array_sum($countVal);
            $payload['indicator_type_counts'] = count($countKey);
        } elseif (!empty($event['indicator_count'])) {
            $payload['actual_indicator_count'] = (int) $event['indicator_count'];
        } elseif ($refCount > 0) {
            $payload['actual_indicator_count'] = $refCount;
        }

        return $payload;
    }

    protected function buildHoneypotEventFallback(string $pulseId, int $refCount): array
    {
        $date = substr($pulseId, strlen('honeypot.'));
        $parsed = \DateTimeImmutable::createFromFormat('Y-m-d', $date, new \DateTimeZone('Asia/Bangkok'));
        $displayDate = $parsed ? $parsed->format('d M Y') : $date;

        return [
            'pulse_id' => $pulseId,
            'name' => 'Honeypot Thailand TXEC - ' . $displayDate,
            'tags' => 'honeypot,TXEC',
            'groups' => 'Honeypot',
            'industries' => '',
            'indicator_count' => $refCount,
            'is_modified' => true,
            'public' => (int) config('honeypot.indicator_publish.public', 0),
            'creator_org' => (string) config('honeypot.indicator_publish.creator_org', 'TXEC'),
            'indicator_type_counts' => [],
        ];
    }

    protected function applyHoneypotEventDefaults(array $event, int $refCount): array
    {
        if (empty($event['tags'])) {
            $event['tags'] = 'honeypot,TXEC';
        }

        if (empty($event['groups'])) {
            $event['groups'] = 'Honeypot';
        }

        if (empty($event['creator_org'])) {
            $event['creator_org'] = (string) config('honeypot.indicator_publish.creator_org', 'TXEC');
        }

        if (empty($event['name']) && strpos((string) ($event['pulse_id'] ?? ''), 'honeypot.') === 0) {
            $date = substr((string) $event['pulse_id'], strlen('honeypot.'));
            $parsed = \DateTimeImmutable::createFromFormat('Y-m-d', $date, new \DateTimeZone('Asia/Bangkok'));
            $displayDate = $parsed ? $parsed->format('d M Y') : $date;
            $event['name'] = 'Honeypot Thailand TXEC - ' . $displayDate;
        }

        if (empty($event['indicator_count']) && $refCount > 0) {
            $event['indicator_count'] = $refCount;
        }

        return $event;
    }
}
