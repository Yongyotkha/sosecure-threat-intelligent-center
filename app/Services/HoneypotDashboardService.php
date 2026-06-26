<?php

namespace App\Services;

use MongoDB\BSON\UTCDateTime;
use MongoDB\Model\BSONDocument;

class HoneypotDashboardService
{
    protected $mongo;

    protected $severityWeights = [
        'critical' => 100,
        'high' => 50,
        'medium' => 20,
        'low' => 5,
    ];

    public function __construct(HoneypotMongoService $mongo)
    {
        $this->mongo = $mongo;
    }

    public function getDashboardData(?int $siteId = null, array $window = [], ?int $sensorTokenId = null, ?array $allowedSiteIds = null): array
    {
        $context = $this->buildQueryContext($siteId, $window, $sensorTokenId, $allowedSiteIds);
        $windowLabel = $this->buildWindowLabel($context['window']);

        return [
            'window' => $context['window'],
            'window_label' => $windowLabel,
            'site_id' => $siteId,
            'sensor_token_id' => $sensorTokenId,
            'stats' => $this->getStats($context['match']),
            'hourly_chart' => $this->getHourlyChart($context['match']),
            'trend_7day' => $this->getDailyTrend($context['match'], $context['window']),
            'threat_types_chart' => $this->getThreatTypesChart($context['match']),
            'top_paths' => $this->getTopPaths($context['match'], 10),
            'top_attackers' => $this->getTopAttackers($context['match'], 10),
            'recent_logs' => $this->getRecentAlerts($context['match'], 10),
        ];
    }

    public function getSectionData(string $section, ?int $siteId = null, array $window = [], ?int $sensorTokenId = null, ?array $allowedSiteIds = null): array
    {
        $context = $this->buildQueryContext($siteId, $window, $sensorTokenId, $allowedSiteIds);
        $windowLabel = $this->buildWindowLabel($context['window']);
        $match = $context['match'];

        switch ($section) {
            case 'stats':
                return ['stats' => $this->getStats($match)];
            case 'hourly':
                return [
                    'window_label' => $windowLabel,
                    'hourly_chart' => $this->getHourlyChart($match),
                ];
            case 'daily-trend':
                return [
                    'window_label' => $windowLabel,
                    'trend_7day' => $this->getDailyTrend($match, $context['window']),
                ];
            case 'threat-types':
                return ['threat_types_chart' => $this->getThreatTypesChart($match)];
            case 'top-paths':
                return ['top_paths' => $this->getTopPaths($match, 10)];
            case 'top-attackers':
                return ['top_attackers' => $this->getTopAttackers($match, 10)];
            case 'recent-logs':
                return [
                    'window_label' => $windowLabel,
                    'recent_logs' => $this->getRecentAlerts($match, 10),
                ];
            default:
                throw new \InvalidArgumentException('Unknown dashboard section: ' . $section);
        }
    }

    protected function buildQueryContext(?int $siteId, array $window, ?int $sensorTokenId, ?array $allowedSiteIds): array
    {
        $window = $this->normalizeWindowConfig($window);

        return [
            'window' => $window,
            'match' => array_merge(
                $this->siteFilter($siteId, $allowedSiteIds),
                $this->sensorFilter($sensorTokenId),
                $this->timeFilter($window)
            ),
        ];
    }

    protected function normalizeWindowConfig(array $window): array
    {
        if (empty($window) || !isset($window['mode'])) {
            return ['mode' => 'today', 'label' => 'Today'];
        }

        if ($window['mode'] === 'custom') {
            $timezone = new \DateTimeZone('Asia/Bangkok');
            $from = (string) ($window['from'] ?? '');
            $to = (string) ($window['to'] ?? '');
            $fromDt = \DateTimeImmutable::createFromFormat('Y-m-d', $from, $timezone);
            $toDt = \DateTimeImmutable::createFromFormat('Y-m-d', $to, $timezone);

            if (!$fromDt || !$toDt) {
                $toDt = new \DateTimeImmutable('today', $timezone);
                $fromDt = $toDt->modify('-6 days');
            }

            if ($fromDt > $toDt) {
                $swap = $fromDt;
                $fromDt = $toDt;
                $toDt = $swap;
            }

            if ($fromDt->diff($toDt)->days > 90) {
                $fromDt = $toDt->modify('-90 days');
            }

            $window['from'] = $fromDt->format('Y-m-d');
            $window['to'] = $toDt->format('Y-m-d');
            $window['label'] = $fromDt->format('d/m/Y') . ' - ' . $toDt->format('d/m/Y');

            return $window;
        }

        if ($window['mode'] === 'hours') {
            $hours = max(1, min((int) ($window['hours'] ?? 24), 168));
            $window['hours'] = $hours;
            $window['label'] = 'Last ' . $hours . 'h';

            return $window;
        }

        return ['mode' => 'today', 'label' => 'Today'];
    }

    protected function buildWindowLabel(array $window): string
    {
        return (string) ($window['label'] ?? 'Today');
    }

    protected function timeFilter(array $window): array
    {
        $timezone = new \DateTimeZone('Asia/Bangkok');

        if (($window['mode'] ?? '') === 'today') {
            $start = (new \DateTimeImmutable('now', $timezone))->setTime(0, 0, 0);

            return ['timestamp' => ['$gte' => new UTCDateTime($start->getTimestamp() * 1000)]];
        }

        if (($window['mode'] ?? '') === 'custom') {
            $from = new \DateTimeImmutable($window['from'] . ' 00:00:00', $timezone);
            $to = new \DateTimeImmutable($window['to'] . ' 23:59:59', $timezone);

            return [
                'timestamp' => [
                    '$gte' => new UTCDateTime($from->getTimestamp() * 1000),
                    '$lte' => new UTCDateTime($to->getTimestamp() * 1000),
                ],
            ];
        }

        return $this->sinceFilter((int) ($window['hours'] ?? 24));
    }

    protected function buildDayBuckets(array $window): array
    {
        $timezone = new \DateTimeZone('Asia/Bangkok');
        $days = [];

        if (($window['mode'] ?? '') === 'custom') {
            $from = new \DateTimeImmutable($window['from'] . ' 00:00:00', $timezone);
            $to = new \DateTimeImmutable($window['to'] . ' 00:00:00', $timezone);
            $cursor = $from;

            while ($cursor <= $to) {
                $key = $cursor->format('Y-m-d');
                $days[$key] = [
                    'label' => $cursor->format('d/m'),
                    'count' => 0,
                ];
                $cursor = $cursor->modify('+1 day');
            }

            return $days;
        }

        for ($i = 6; $i >= 0; $i--) {
            $date = (new \DateTimeImmutable('now', $timezone))->modify("-{$i} days");
            $days[$date->format('Y-m-d')] = [
                'label' => $date->format('d/m'),
                'count' => 0,
            ];
        }

        return $days;
    }

    protected function siteFilter(?int $siteId, ?array $allowedSiteIds = null): array
    {
        if ($siteId) {
            return ['site_id' => $siteId];
        }

        if ($allowedSiteIds === null) {
            return [];
        }

        if (empty($allowedSiteIds)) {
            return ['site_id' => -1];
        }

        return ['site_id' => ['$in' => array_values($allowedSiteIds)]];
    }

    protected function sensorFilter(?int $sensorTokenId): array
    {
        if ($sensorTokenId) {
            return ['sensor_token_id' => $sensorTokenId];
        }

        return [];
    }

    public function getAgentsSeenInAlerts(int $siteId): array
    {
        $collection = $this->mongo->collection('alerts');
        $pipeline = [
            ['$match' => [
                'site_id' => $siteId,
                'sensor_token_id' => ['$exists' => true, '$gt' => 0],
            ]],
            ['$group' => [
                '_id' => '$sensor_token_id',
                'sensor_name' => ['$last' => '$sensor_name'],
                'last_seen' => ['$max' => '$timestamp'],
            ]],
            ['$sort' => ['sensor_name' => 1]],
        ];

        $agents = [];
        foreach ($collection->aggregate($pipeline) as $row) {
            $agents[] = [
                'sensor_token_id' => (int) ($row['_id'] ?? 0),
                'sensor_name' => (string) ($row['sensor_name'] ?? ''),
                'last_seen' => $this->formatMongoDate($row['last_seen'] ?? null),
            ];
        }

        return $agents;
    }

    protected function sinceFilter(int $hours): array
    {
        $since = new UTCDateTime((time() - ($hours * 3600)) * 1000);

        return ['timestamp' => ['$gte' => $since]];
    }

    protected function getStats(array $match): array
    {
        $collection = $this->mongo->collection('alerts');
        $pipeline = [];

        if (!empty($match)) {
            $pipeline[] = ['$match' => $match];
        }

        $pipeline[] = [
            '$group' => [
                '_id' => null,
                'total_alerts' => ['$sum' => 1],
                'unique_ips' => ['$addToSet' => '$attacker_ip'],
                'high_risk' => [
                    '$sum' => [
                        '$cond' => [
                            [
                                '$in' => [
                                    ['$toLower' => '$severity'],
                                    ['critical', 'high'],
                                ],
                            ],
                            1,
                            0,
                        ],
                    ],
                ],
            ],
        ];

        $rows = iterator_to_array($collection->aggregate($pipeline));
        $row = $rows[0] ?? null;

        if (!$row) {
            return [
                'high_risk_alerts' => 0,
                'active_attackers' => 0,
                'logs_processed' => 0,
            ];
        }

        $uniqueIps = $row['unique_ips'] ?? [];
        if ($uniqueIps instanceof \Traversable) {
            $uniqueIps = iterator_to_array($uniqueIps);
        }

        return [
            'high_risk_alerts' => (int) ($row['high_risk'] ?? 0),
            'active_attackers' => is_array($uniqueIps) ? count($uniqueIps) : 0,
            'logs_processed' => (int) ($row['total_alerts'] ?? 0),
        ];
    }

    protected function getHourlyChart(array $match): array
    {
        $collection = $this->mongo->collection('alerts');
        $pipeline = [];

        if (!empty($match)) {
            $pipeline[] = ['$match' => $match];
        }

        $pipeline[] = [
            '$group' => [
                '_id' => [
                    '$hour' => [
                        'date' => '$timestamp',
                        'timezone' => 'Asia/Bangkok',
                    ],
                ],
                'count' => ['$sum' => 1],
            ],
        ];
        $pipeline[] = ['$sort' => ['_id' => 1]];

        $hourCounts = array_fill(0, 24, 0);
        foreach ($collection->aggregate($pipeline) as $row) {
            $hour = (int) ($row['_id'] ?? 0);
            if ($hour >= 0 && $hour < 24) {
                $hourCounts[$hour] = (int) ($row['count'] ?? 0);
            }
        }

        $labels = [];
        for ($i = 0; $i < 24; $i++) {
            $labels[] = sprintf('%02d:00', $i);
        }

        return [
            'labels' => $labels,
            'values' => $hourCounts,
        ];
    }

    protected function getDailyTrend(array $match, array $window = []): array
    {
        $window = $this->normalizeWindowConfig($window);
        $collection = $this->mongo->collection('alerts');
        $days = $this->buildDayBuckets($window);
        $options = ['projection' => ['timestamp' => 1]];
        $cursor = !empty($match)
            ? $collection->find($match, $options)
            : $collection->find([], $options);

        foreach ($cursor as $doc) {
            if ($doc instanceof BSONDocument) {
                $doc = $doc->getArrayCopy();
            }

            $timestamp = $doc['timestamp'] ?? null;
            if (!$timestamp instanceof UTCDateTime) {
                continue;
            }

            $key = $timestamp->toDateTime()
                ->setTimezone(new \DateTimeZone('Asia/Bangkok'))
                ->format('Y-m-d');

            if (isset($days[$key])) {
                $days[$key]['count']++;
            }
        }

        return [
            'labels' => array_column($days, 'label'),
            'values' => array_map(function ($day) {
                return (int) ($day['count'] ?? 0);
            }, array_values($days)),
        ];
    }

    protected function getThreatTypesChart(array $match): array
    {
        $collection = $this->mongo->collection('alerts');
        $pipeline = [];

        if (!empty($match)) {
            $pipeline[] = ['$match' => $match];
        }

        $pipeline[] = [
            '$group' => [
                '_id' => '$threat_name',
                'count' => ['$sum' => 1],
            ],
        ];
        $pipeline[] = ['$sort' => ['count' => -1]];
        $pipeline[] = ['$limit' => 10];

        $labels = [];
        $values = [];

        foreach ($collection->aggregate($pipeline) as $row) {
            $label = (string) ($row['_id'] ?? 'Unknown');
            if ($label === '') {
                $label = 'Unknown';
            }
            $labels[] = $label;
            $values[] = (int) ($row['count'] ?? 0);
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    protected function getTopPaths(array $match, int $limit = 10): array
    {
        $collection = $this->mongo->collection('alerts');
        $pipeline = [];

        if (!empty($match)) {
            $pipeline[] = ['$match' => $match];
        }

        $pipeline[] = [
            '$match' => [
                'request_path' => ['$nin' => [null, '']],
            ],
        ];
        $pipeline[] = [
            '$group' => [
                '_id' => '$request_path',
                'count' => ['$sum' => 1],
            ],
        ];
        $pipeline[] = ['$sort' => ['count' => -1]];
        $pipeline[] = ['$limit' => $limit];

        $rows = [];
        foreach ($collection->aggregate($pipeline) as $row) {
            $rows[] = [
                'path' => (string) ($row['_id'] ?? ''),
                'count' => (int) ($row['count'] ?? 0),
            ];
        }

        return $rows;
    }

    protected function getTopAttackers(array $match, int $limit = 10): array
    {
        $collection = $this->mongo->collection('alerts');
        $pipeline = [];

        if (!empty($match)) {
            $pipeline[] = ['$match' => $match];
        }

        $pipeline[] = [
            '$group' => [
                '_id' => '$attacker_ip',
                'count' => ['$sum' => 1],
                'threats' => ['$addToSet' => '$threat_name'],
                'severities' => ['$push' => ['$toLower' => '$severity']],
                'last_seen' => ['$max' => '$timestamp'],
            ],
        ];
        $pipeline[] = ['$sort' => ['count' => -1]];

        $attackers = [];
        foreach ($collection->aggregate($pipeline) as $row) {
            $severities = $row['severities'] ?? [];
            if ($severities instanceof \Traversable) {
                $severities = iterator_to_array($severities);
            }

            $threats = $row['threats'] ?? [];
            if ($threats instanceof \Traversable) {
                $threats = iterator_to_array($threats);
            }

            $riskScore = 0;
            foreach ($severities as $severity) {
                $riskScore += $this->severityWeights[strtolower((string) $severity)] ?? 10;
            }

            $tags = [];
            foreach ($threats as $threat) {
                $tag = strtoupper(preg_replace('/[^a-zA-Z0-9]+/', '_', (string) $threat));
                $tag = trim($tag, '_');
                if ($tag !== '' && !in_array($tag, $tags, true)) {
                    $tags[] = $tag;
                }
            }

            $attackers[] = [
                'ip' => (string) ($row['_id'] ?? ''),
                'risk_score' => $riskScore,
                'count' => (int) ($row['count'] ?? 0),
                'tags' => array_slice($tags, 0, 6),
                'summary_note' => sprintf(
                    'Last seen: %s · %d events',
                    $this->formatMongoDate($row['last_seen'] ?? null, 'H:i:s'),
                    (int) ($row['count'] ?? 0)
                ),
            ];
        }

        usort($attackers, function ($a, $b) {
            return $b['risk_score'] <=> $a['risk_score'];
        });

        return array_slice($attackers, 0, $limit);
    }

    protected function getRecentAlerts(array $match, int $limit = 10): array
    {
        $collection = $this->mongo->collection('alerts');
        $options = [
            'sort' => ['timestamp' => -1],
            'limit' => $limit,
        ];

        $cursor = !empty($match)
            ? $collection->find($match, $options)
            : $collection->find([], $options);

        $alerts = [];
        foreach ($cursor as $document) {
            $alerts[] = $this->normalizeAlert($document);
        }

        return $alerts;
    }

    protected function normalizeAlert($document): array
    {
        if ($document instanceof BSONDocument) {
            $document = $document->getArrayCopy();
        }

        return [
            'alert_id' => (string) ($document['alert_id'] ?? ''),
            'timestamp' => $this->formatMongoDate($document['timestamp'] ?? null),
            'time_display' => $this->formatMongoDate($document['timestamp'] ?? null, 'g:i:s A'),
            'attacker_ip' => (string) ($document['attacker_ip'] ?? ''),
            'severity' => strtoupper((string) ($document['severity'] ?? '')),
            'threat_level' => strtoupper((string) ($document['threat_level'] ?? '')),
            'threat_name' => (string) ($document['threat_name'] ?? ''),
            'title' => (string) ($document['title'] ?? ''),
            'summary' => (string) ($document['summary'] ?? ''),
            'origin' => (string) ($document['origin'] ?? ''),
            'payload' => (string) ($document['payload'] ?? ''),
            'request_path' => (string) ($document['request_path'] ?? $document['path'] ?? ''),
            'current_risk_score' => isset($document['current_risk_score']) ? (int) $document['current_risk_score'] : null,
            'raw_details' => (array) ($document['raw_details'] ?? []),
            'site_id' => isset($document['site_id']) ? (int) $document['site_id'] : null,
            'sensor_token_id' => isset($document['sensor_token_id']) ? (int) $document['sensor_token_id'] : null,
            'sensor_name' => (string) ($document['sensor_name'] ?? ''),
        ];
    }

    protected function formatMongoDate($value, string $format = 'Y-m-d H:i:s'): string
    {
        if ($value instanceof UTCDateTime) {
            return $value->toDateTime()->setTimezone(new \DateTimeZone('Asia/Bangkok'))->format($format);
        }

        return '';
    }
}
