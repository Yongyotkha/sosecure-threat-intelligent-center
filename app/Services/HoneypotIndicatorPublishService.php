<?php

namespace App\Services;

use MongoDB\BSON\UTCDateTime;
use Modules\SiteSettings\Entities\SiteCategory;

class HoneypotIndicatorPublishService
{
    protected $honeypotMongo;

    public function __construct(HoneypotMongoService $honeypotMongo)
    {
        $this->honeypotMongo = $honeypotMongo;
    }

    public function publishForDate(string $date, bool $force = false): array
    {
        $date = $this->normalizeDate($date);
        $pulseId = $this->buildPulseId($date);
        $window = $this->buildDayWindow($date);

        $aggregated = $this->aggregateDailyIps($window['start'], $window['end']);
        if (empty($aggregated)) {
            return [
                'published' => false,
                'pulse_id' => $pulseId,
                'date' => $date,
                'reason' => 'no_alerts',
                'indicator_count' => 0,
            ];
        }

        $this->honeypotMongo->getDatabase();

        $events = $this->honeypotMongo->indicatorCollection('events');
        $existing = $events->findOne(['pulse_id' => $pulseId], ['projection' => ['pulse_id' => 1]]);

        if ($existing && !$force) {
            return [
                'published' => false,
                'pulse_id' => $pulseId,
                'date' => $date,
                'reason' => 'already_exists',
                'indicator_count' => count($aggregated),
            ];
        }

        $siteIds = [];
        foreach ($aggregated as $row) {
            foreach ($row['site_ids'] as $siteId) {
                if ($siteId) {
                    $siteIds[] = (int) $siteId;
                }
            }
        }
        $industries = $this->resolveIndustries(array_values(array_unique($siteIds)));
        $tags = $this->buildEventTags();

        if ($force) {
            $this->honeypotMongo->indicatorCollection('events_indicator_ref')->deleteMany(['pulse_id' => $pulseId]);
        }

        $activity = $this->resolveActivityBounds($aggregated);
        $typeCounts = $this->publishIndicators($pulseId, $aggregated);
        $this->publishEvent($pulseId, $date, $window, $industries, $tags, $typeCounts, count($aggregated), $activity);
        $this->touchDailyStats($pulseId);

        return [
            'published' => true,
            'pulse_id' => $pulseId,
            'date' => $date,
            'indicator_count' => count($aggregated),
            'industries' => $industries,
            'type_counts' => $typeCounts,
        ];
    }

    protected function normalizeDate(string $date): string
    {
        $timezone = new \DateTimeZone('Asia/Bangkok');
        $parsed = \DateTimeImmutable::createFromFormat('Y-m-d', $date, $timezone);

        if (!$parsed || $parsed->format('Y-m-d') !== $date) {
            throw new \InvalidArgumentException('Invalid date format, expected Y-m-d.');
        }

        return $date;
    }

    protected function buildPulseId(string $date): string
    {
        return 'honeypot.' . $date;
    }

    protected function buildDayWindow(string $date): array
    {
        $timezone = new \DateTimeZone('Asia/Bangkok');
        $start = new \DateTimeImmutable($date . ' 00:00:00', $timezone);
        $end = new \DateTimeImmutable($date . ' 23:59:59', $timezone);

        return [
            'start' => new UTCDateTime($start->getTimestamp() * 1000),
            'end' => new UTCDateTime($end->getTimestamp() * 1000),
            'start_dt' => $start,
            'end_dt' => $end,
        ];
    }

    protected function aggregateDailyIps(UTCDateTime $start, UTCDateTime $end): array
    {
        $collection = $this->honeypotMongo->collection('alerts');
        $pipeline = [
            [
                '$match' => [
                    'timestamp' => [
                        '$gte' => $start,
                        '$lte' => $end,
                    ],
                    'attacker_ip' => ['$nin' => [null, '']],
                ],
            ],
            [
                '$group' => [
                    '_id' => '$attacker_ip',
                    'hit_count' => ['$sum' => 1],
                    'threats' => ['$addToSet' => '$threat_name'],
                    'severities' => ['$addToSet' => '$severity'],
                    'site_ids' => ['$addToSet' => '$site_id'],
                    'first_seen' => ['$min' => '$timestamp'],
                    'last_seen' => ['$max' => '$timestamp'],
                ],
            ],
            ['$sort' => ['hit_count' => -1]],
        ];

        $rows = [];
        foreach ($collection->aggregate($pipeline) as $document) {
            if ($document instanceof \MongoDB\Model\BSONDocument) {
                $document = $document->getArrayCopy();
            }

            $ip = trim((string) ($document['_id'] ?? ''));
            if (!$this->isValidIp($ip)) {
                continue;
            }

            $rows[] = [
                'ip' => $ip,
                'type' => $this->resolveIpType($ip),
                'hit_count' => (int) ($document['hit_count'] ?? 0),
                'threats' => $this->normalizeStringList($document['threats'] ?? []),
                'severities' => $this->normalizeStringList($document['severities'] ?? []),
                'site_ids' => $this->normalizeIntList($document['site_ids'] ?? []),
                'first_seen' => $document['first_seen'] ?? null,
                'last_seen' => $document['last_seen'] ?? null,
            ];
        }

        return $rows;
    }

    /**
     * Event created/modified from actual alert activity (not calendar day boundaries).
     */
    protected function resolveActivityBounds(array $aggregated): array
    {
        $created = null;
        $modified = null;

        foreach ($aggregated as $row) {
            $first = $row['first_seen'] ?? null;
            $last = $row['last_seen'] ?? null;

            if ($first instanceof UTCDateTime) {
                if ($created === null || $first->toDateTime()->getTimestamp() < $created->toDateTime()->getTimestamp()) {
                    $created = $first;
                }
            }

            if ($last instanceof UTCDateTime) {
                if ($modified === null || $last->toDateTime()->getTimestamp() > $modified->toDateTime()->getTimestamp()) {
                    $modified = $last;
                }
            }
        }

        $fallback = new UTCDateTime();

        return [
            'created' => $created ?? $fallback,
            'modified' => $modified ?? $fallback,
        ];
    }

    protected function resolveIndustries(array $siteIds): array
    {
        if (empty($siteIds)) {
            return [];
        }

        return SiteCategory::query()
            ->whereIn('site_id', $siteIds)
            ->where('active', 1)
            ->whereHas('category', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->with('category:id,name')
            ->get()
            ->pluck('category.name')
            ->filter(function ($name) {
                return trim((string) $name) !== '';
            })
            ->map(function ($name) {
                return trim((string) $name);
            })
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    protected function buildEventTags(): string
    {
        return 'honeypot';
    }

    protected function creatorOrg(): string
    {
        return (string) config('honeypot.indicator_publish.creator_org', 'Threat inSights');
    }

    protected function indicatorIsPublic(): int
    {
        return (int) config('honeypot.indicator_publish.public', 0) === 1 ? 1 : 0;
    }

    protected function publishIndicators(string $pulseId, array $aggregated): array
    {
        $detailCol = $this->honeypotMongo->indicatorCollection('indicator_detail');
        $refCol = $this->honeypotMongo->indicatorCollection('events_indicator_ref');
        $dataCol = $this->honeypotMongo->indicatorCollection('transaction_indicators_data');
        $typeCol = $this->honeypotMongo->indicatorCollection('otx_type');

        $dateNow = new UTCDateTime();
        $transactionDate = date('Y-m-d');
        $typeCounts = [];

        $opsDetail = [];
        $opsData = [];
        $opsType = [];
        $opsRef = [];
        $batchSize = 500;
        $count = 0;

        foreach ($aggregated as $row) {
            $ip = $row['ip'];
            $type = $row['type'];
            $indicatorId = $this->buildIndicatorId($ip);
            $typeCounts[$type] = ($typeCounts[$type] ?? 0) + 1;

            $allRow = [
                'detail' => $ip,
                'hit_count' => $row['hit_count'],
                'threats' => array_slice($row['threats'], 0, 10),
                'severities' => array_slice($row['severities'], 0, 5),
                'first_seen' => $this->formatMongoDate($row['first_seen']),
                'last_seen' => $this->formatMongoDate($row['last_seen']),
            ];

            $opsDetail[] = [
                'updateOne' => [
                    ['indicator_id' => $indicatorId],
                    [
                        '$set' => [
                            'indicator_name' => $ip,
                            'type' => $type,
                            'allrow' => $allRow,
                            'updated_by' => 'system',
                            'updated_at' => $dateNow,
                        ],
                        '$setOnInsert' => [
                            'transcation_id' => null,
                            'status' => 1,
                            'created_at' => $dateNow,
                            'created_by' => 'system',
                            'deleted_at' => null,
                            'transaction_date' => $transactionDate,
                            'source' => 'honeypot',
                            'creator_org' => $this->creatorOrg(),
                        ],
                    ],
                    ['upsert' => true],
                ],
            ];

            $opsData[] = [
                'updateOne' => [
                    ['indicator_id' => $indicatorId],
                    [
                        '$set' => [
                            'indicator' => $ip,
                            'type' => $type,
                            'updated_at' => $dateNow,
                            'updated_by' => 'system',
                        ],
                        '$setOnInsert' => [
                            'status' => 1,
                            'created_at' => $dateNow,
                            'created_by' => 'system',
                            'deleted_at' => null,
                            'transaction_date' => $transactionDate,
                            'source' => 'honeypot',
                        ],
                    ],
                    ['upsert' => true],
                ],
            ];

            $opsType[] = [
                'updateOne' => [
                    ['name' => $type],
                    [
                        '$setOnInsert' => [
                            'transcation_id' => null,
                            'updated_at' => $dateNow,
                            'updated_by' => 'system',
                            'slug' => null,
                            'description' => null,
                            'code' => generator_uuid(),
                            'remark' => 'honeypot',
                            'element_count' => 0,
                            'status' => 1,
                            'created_at' => $dateNow,
                            'created_by' => 'system',
                            'deleted_at' => null,
                            'source' => 'honeypot',
                        ],
                    ],
                    ['upsert' => true],
                ],
            ];

            $opsRef[] = [
                'updateOne' => [
                    [
                        'indicator_id' => $indicatorId,
                        'pulse_id' => $pulseId,
                    ],
                    [
                        '$set' => [
                            'pulse_modified' => $row['last_seen'] instanceof UTCDateTime ? $row['last_seen'] : $dateNow,
                            'role' => 'attacker',
                            'created' => $row['first_seen'] instanceof UTCDateTime ? $row['first_seen'] : $dateNow,
                            'expiration' => null,
                            'is_active' => 1,
                            'updated_at' => $dateNow,
                            'updated_by' => 'system',
                        ],
                        '$setOnInsert' => [
                            'status' => 1,
                            'created_at' => $dateNow,
                            'created_by' => 'system',
                            'deleted_at' => null,
                            'transaction_date' => $transactionDate,
                            'source' => 'honeypot',
                            'indicator' => $ip,
                            'type' => $type,
                            'is_count_attr' => 1,
                            'creator_org' => $this->creatorOrg(),
                        ],
                    ],
                    ['upsert' => true],
                ],
            ];

            $count++;
            if ($count % $batchSize === 0) {
                $this->flushBulkWrites($detailCol, $refCol, $dataCol, $typeCol, $opsDetail, $opsRef, $opsData, $opsType);
                $opsDetail = [];
                $opsData = [];
                $opsType = [];
                $opsRef = [];
            }
        }

        $this->flushBulkWrites($detailCol, $refCol, $dataCol, $typeCol, $opsDetail, $opsRef, $opsData, $opsType);

        return $typeCounts;
    }

    protected function publishEvent(
        string $pulseId,
        string $date,
        array $window,
        array $industries,
        string $tags,
        array $typeCounts,
        int $indicatorCount,
        array $activity
    ): void {
        $events = $this->honeypotMongo->indicatorCollection('events');
        $dateNow = new UTCDateTime();
        $displayDate = $window['start_dt']->format('d M Y');
        $industriesText = implode(', ', $industries);

        $events->updateOne(
            ['pulse_id' => $pulseId],
            [
                '$set' => [
                    'name' => 'Honeypot Threat Intelligence - ' . $displayDate,
                    'description' => 'Daily aggregated honeypot attacker IPs for ' . $displayDate . '. Customer site details are not disclosed.',
                    'modified' => $activity['modified'],
                    'created' => $activity['created'],
                    'public' => $this->indicatorIsPublic(),
                    'TLP' => 'amber',
                    'is_modified' => true,
                    'references' => '',
                    'tags' => $tags,
                    'industries' => $industriesText,
                    'malware_families' => '',
                    'groups' => 'Honeypot',
                    'author_username' => 'system',
                    'updated_at' => $dateNow,
                    'updated_by' => 'system',
                    'creator_org' => $this->creatorOrg(),
                    'indicator_count' => $indicatorCount,
                    'indicator_type_counts' => $typeCounts,
                    'source' => 'honeypot',
                    'status' => 1,
                    'transaction_date' => $date,
                    'deleted_at' => null,
                ],
                '$setOnInsert' => [
                    'transcation_id' => null,
                    'count_view' => 0,
                    'count_related_pulse' => 0,
                    'created_at' => $dateNow,
                    'created_by' => 'system',
                    'mips_uuid' => PublishedFeedsService::generateUuidV4(),
                ],
            ],
            ['upsert' => true]
        );
    }

    protected function touchDailyStats(string $pulseId): void
    {
        try {
            $this->honeypotMongo->indicatorCollection('events_daily_stats')->updateOne(
            ['date' => date('Y-m-d')],
            [
                '$addToSet' => ['pulse_ids' => $pulseId],
                '$setOnInsert' => [
                    'date' => date('Y-m-d'),
                    'created_at' => new UTCDateTime(),
                    'count' => 0,
                ],
            ],
            ['upsert' => true]
        );
        } catch (\Throwable $e) {
            // Optional collection — ignore if unavailable.
        }
    }

    protected function flushBulkWrites($detailCol, $refCol, $dataCol, $typeCol, array $opsDetail, array $opsRef, array $opsData, array $opsType): void
    {
        if (!empty($opsDetail)) {
            $detailCol->bulkWrite($opsDetail);
        }
        if (!empty($opsData)) {
            $dataCol->bulkWrite($opsData);
        }
        if (!empty($opsType)) {
            $typeCol->bulkWrite($opsType);
        }
        if (!empty($opsRef)) {
            $refCol->bulkWrite($opsRef);
        }
    }

    protected function buildIndicatorId(string $ip): string
    {
        return 'honeypot.ip.' . md5(strtolower($ip));
    }

    protected function resolveIpType(string $ip): string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return 'IPv6';
        }

        return 'IPv4';
    }

    protected function isValidIp(string $ip): bool
    {
        return (bool) filter_var($ip, FILTER_VALIDATE_IP);
    }

    protected function normalizeStringList($values): array
    {
        if ($values instanceof \Traversable) {
            $values = iterator_to_array($values);
        }

        if (!is_array($values)) {
            return [];
        }

        $normalized = [];
        foreach ($values as $value) {
            $value = trim((string) $value);
            if ($value !== '') {
                $normalized[] = $value;
            }
        }

        return array_values(array_unique($normalized));
    }

    protected function normalizeIntList($values): array
    {
        if ($values instanceof \Traversable) {
            $values = iterator_to_array($values);
        }

        if (!is_array($values)) {
            return [];
        }

        $normalized = [];
        foreach ($values as $value) {
            $value = (int) $value;
            if ($value > 0) {
                $normalized[] = $value;
            }
        }

        return array_values(array_unique($normalized));
    }

    protected function formatMongoDate($value): string
    {
        if ($value instanceof UTCDateTime) {
            return $value->toDateTime()->setTimezone(new \DateTimeZone('Asia/Bangkok'))->format('Y-m-d H:i:s');
        }

        return '';
    }
}
