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
        [$queryStart, $queryEnd] = HoneypotTimestamp::queryBoundsForLegacyStorage($start, $end);

        return [
            'start' => new UTCDateTime($queryStart->getTimestamp() * 1000),
            'end' => new UTCDateTime($queryEnd->getTimestamp() * 1000),
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
                    'header_user_agents' => ['$addToSet' => '$raw_details.header_user_agent'],
                    'detail_user_agents' => ['$addToSet' => '$raw_details.user_agent'],
                    'attacker_types' => ['$addToSet' => '$raw_details.attacker_type'],
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

            $severities = $this->normalizeStringList($document['severities'] ?? []);
            $maxSeverity = $this->resolveMaxSeverity($severities);
            $scoreMap = $this->severityToAttribute($maxSeverity);
            $userAgents = array_values(array_unique(array_merge(
                $this->normalizeStringList($document['header_user_agents'] ?? []),
                $this->normalizeStringList($document['detail_user_agents'] ?? [])
            )));
            $attackerTypes = $this->normalizeStringList($document['attacker_types'] ?? []);
            $attributeTags = $this->deriveAttributeTags($userAgents, $attackerTypes);

            $rows[] = [
                'ip' => $ip,
                'type' => $this->resolveIpType($ip),
                'hit_count' => (int) ($document['hit_count'] ?? 0),
                'threats' => $this->normalizeStringList($document['threats'] ?? []),
                'severities' => $severities,
                'max_severity' => $maxSeverity,
                'attribute_score' => $scoreMap['score'],
                'attribute_serverity' => $scoreMap['severity'],
                'attribute_tags' => $attributeTags,
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
        return 'honeypot,TXEC';
    }

    protected function creatorOrg(): string
    {
        return (string) config('honeypot.indicator_publish.creator_org', 'TXEC');
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

            $refSet = [
                'pulse_modified' => $row['last_seen'] instanceof UTCDateTime ? $row['last_seen'] : $dateNow,
                'role' => 'attacker',
                'created' => $row['first_seen'] instanceof UTCDateTime ? $row['first_seen'] : $dateNow,
                'expiration' => null,
                'is_active' => 1,
                'status' => 1,
                'attribute_score' => (string) ($row['attribute_score'] ?? '0'),
                'attribute_serverity' => (string) ($row['attribute_serverity'] ?? 'Informational'),
                'updated_at' => $dateNow,
                'updated_by' => 'system',
            ];

            // Attribute tags only when derived (human/automated/tool); omit field if unknown.
            $attributeTags = trim((string) ($row['attribute_tags'] ?? ''));
            if ($attributeTags !== '') {
                $refSet['tags'] = $attributeTags;
            }

            $opsRef[] = [
                'updateOne' => [
                    [
                        'indicator_id' => $indicatorId,
                        'pulse_id' => $pulseId,
                    ],
                    [
                        '$set' => $refSet,
                        '$setOnInsert' => [
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
                    'name' => 'Honeypot Thailand TXEC - ' . $displayDate,
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
        $values = $this->mongoListToArray($values);
        if ($values === []) {
            return [];
        }

        $normalized = [];
        foreach ($values as $value) {
            $value = $this->mongoValueToString($value);
            if ($value !== '') {
                $normalized[] = $value;
            }
        }

        return array_values(array_unique($normalized));
    }

    protected function normalizeIntList($values): array
    {
        $values = $this->mongoListToArray($values);
        if ($values === []) {
            return [];
        }

        $normalized = [];
        foreach ($values as $value) {
            if (is_object($value) && !($value instanceof \Stringable)) {
                continue;
            }
            $value = (int) $value;
            if ($value > 0) {
                $normalized[] = $value;
            }
        }

        return array_values(array_unique($normalized));
    }

    /**
     * Flatten Mongo aggregation $addToSet results (BSONArray / nested lists) into a plain PHP array.
     */
    protected function mongoListToArray($values): array
    {
        if ($values instanceof \MongoDB\Model\BSONArray || $values instanceof \MongoDB\Model\BSONDocument) {
            $values = $values->getArrayCopy();
        } elseif ($values instanceof \Traversable) {
            $values = iterator_to_array($values);
        }

        if (!is_array($values)) {
            return [];
        }

        return $values;
    }

    /**
     * Safely stringify Mongo scalar-ish values; skip BSONArray/Document to avoid cast fatals.
     */
    protected function mongoValueToString($value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_string($value)) {
            return trim($value);
        }

        if (is_int($value) || is_float($value) || is_bool($value)) {
            return trim((string) $value);
        }

        if ($value instanceof \Stringable) {
            return trim((string) $value);
        }

        // Nested BSONArray / object from aggregation — not a usable tag/UA scalar.
        return '';
    }

    protected function formatMongoDate($value): string
    {
        if ($value instanceof UTCDateTime) {
            return $value->toDateTime()->setTimezone(new \DateTimeZone('Asia/Bangkok'))->format('Y-m-d H:i:s');
        }

        return '';
    }

    /**
     * Pick the highest alert severity from a day's set for one IP.
     * Order: Critical > High > Medium > Low > Info/Informational.
     */
    protected function resolveMaxSeverity(array $severities): string
    {
        $max = 'informational';
        $maxRank = -1;

        foreach ($severities as $severity) {
            $normalized = $this->normalizeSeverity($this->mongoValueToString($severity));
            $rank = $this->severityRank($normalized);
            if ($rank > $maxRank) {
                $maxRank = $rank;
                $max = $normalized;
            }
        }

        return $max;
    }

    protected function normalizeSeverity(string $severity): string
    {
        $value = strtolower(trim($severity));

        if (in_array($value, ['critical', 'crit'], true)) {
            return 'critical';
        }
        if (in_array($value, ['high'], true)) {
            return 'high';
        }
        if (in_array($value, ['medium', 'med'], true)) {
            return 'medium';
        }
        if (in_array($value, ['low'], true)) {
            return 'low';
        }
        if (in_array($value, ['very low', 'verylow', 'very_low'], true)) {
            return 'very_low';
        }

        // Info / Informational / unknown → informational
        return 'informational';
    }

    protected function severityRank(string $normalized): int
    {
        return [
            'critical' => 5,
            'high' => 4,
            'medium' => 3,
            'low' => 2,
            'very_low' => 1,
            'informational' => 0,
        ][$normalized] ?? 0;
    }

    /**
     * Map honeypot/alert severity onto OTX indicator bands (0-10 / 6 levels).
     * Uses representative scores inside each IndicatorCheckService band.
     *
     * @return array{score: string, severity: string}
     */
    protected function severityToAttribute(string $normalized): array
    {
        $map = [
            'critical' => ['score' => '9', 'severity' => 'Critical'],
            'high' => ['score' => '7', 'severity' => 'High'],
            'medium' => ['score' => '4', 'severity' => 'Medium'],
            'low' => ['score' => '2', 'severity' => 'Low'],
            'very_low' => ['score' => '1', 'severity' => 'Very Low'],
            'informational' => ['score' => '0', 'severity' => 'Informational'],
        ];

        return $map[$normalized] ?? $map['informational'];
    }

    /**
     * Build attribute tags only from evidence we already have.
     * - explicit raw_details.attacker_type → actor:human / actor:automated
     * - known tool / scanner UA → actor:automated + tool:<name>
     * Returns empty string when nothing can be inferred (caller omits tags field).
     */
    protected function deriveAttributeTags(array $userAgents, array $attackerTypes): string
    {
        $tags = [];

        foreach ($attackerTypes as $type) {
            $normalized = strtolower(trim((string) $type));
            if (in_array($normalized, ['human'], true)) {
                $tags['actor:human'] = true;
            }
            if (in_array($normalized, ['automated', 'bot', 'machine', 'scanner'], true)) {
                $tags['actor:automated'] = true;
            }
        }

        foreach ($userAgents as $ua) {
            $tool = $this->matchToolFromUa((string) $ua);
            if ($tool === null) {
                continue;
            }
            $tags['actor:automated'] = true;
            $tags['tool:' . $tool] = true;
        }

        if (empty($tags)) {
            return '';
        }

        return implode(', ', array_keys($tags));
    }

    protected function matchToolFromUa(string $ua): ?string
    {
        $ua = trim($ua);
        if ($ua === '') {
            return null;
        }

        // Patterns aligned with agent automated_tool detector + common internet scanners.
        $patterns = [
            'sqlmap' => '/sqlmap/i',
            'nikto' => '/nikto/i',
            'acunetix' => '/acunetix/i',
            'wpscan' => '/wpscan/i',
            'nessus' => '/nessus/i',
            'netsparker' => '/netsparker/i',
            'arachni' => '/arachni/i',
            'nmap' => '/nmap scripting engine|nmap/i',
            'hydra' => '/hydra/i',
            'ffuf' => '/ffuf/i',
            'gobuster' => '/gobuster/i',
            'dirbuster' => '/dirbuster/i',
            'curl' => '/\bcurl\b/i',
            'wget' => '/\bwget\b/i',
            'python-requests' => '/python-requests/i',
            'libwww-perl' => '/libwww-perl/i',
            'go-http-client' => '/go-http-client/i',
            'java' => '/java\/\d+/i',
            'censys' => '/censys/i',
            'shodan' => '/shodan/i',
            'masscan' => '/masscan/i',
            'zgrab' => '/zgrab/i',
            'zmap' => '/zmap/i',
            'httpx' => '/\bhttpx\b/i',
            'nuclei' => '/nuclei/i',
        ];

        foreach ($patterns as $tool => $pattern) {
            if (preg_match($pattern, $ua)) {
                return $tool;
            }
        }

        return null;
    }
}
