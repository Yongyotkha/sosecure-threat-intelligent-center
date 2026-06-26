<?php

namespace App\Services;

use MongoDB\BSON\UTCDateTime;

class HoneypotIngestService
{
    protected $mongo;

    public function __construct(HoneypotMongoService $mongo)
    {
        $this->mongo = $mongo;
    }

    public function ingest(int $siteId, array $alerts = [], ?array $summary = null, array $sensor = []): array
    {
        $result = [
            'alerts_ingested' => 0,
            'alerts_updated' => 0,
            'summary_ingested' => false,
        ];

        if (!empty($alerts)) {
            $result = array_merge($result, $this->storeAlerts($siteId, $alerts, $sensor));
        }

        if ($summary !== null) {
            $result['summary_ingested'] = $this->storeSummary($siteId, $summary, $sensor);
        }

        return $result;
    }

    protected function storeAlerts(int $siteId, array $alerts, array $sensor = []): array
    {
        $collection = $this->mongo->collection('alerts');
        $ingested = 0;
        $updated = 0;
        $now = new UTCDateTime();
        $sensorTokenId = (int) ($sensor['sensor_token_id'] ?? 0);
        $sensorName = (string) ($sensor['sensor_name'] ?? '');

        foreach ($alerts as $alert) {
            $alertId = (string) $alert['alert_id'];
            $timestamp = $this->toUtcDateTime($alert['timestamp']);

            $document = [
                'site_id' => $siteId,
                'alert_id' => $alertId,
                'timestamp' => $timestamp,
                'attacker_ip' => (string) $alert['attacker_ip'],
                'severity' => (string) $alert['severity'],
                'threat_name' => (string) $alert['threat_name'],
                'payload' => (string) ($alert['payload'] ?? ''),
                'request_path' => (string) ($alert['request_path'] ?? $alert['path'] ?? ''),
                'title' => (string) ($alert['title'] ?? ''),
                'summary' => (string) ($alert['summary'] ?? ''),
                'origin' => (string) ($alert['origin'] ?? ''),
                'path' => (string) ($alert['path'] ?? $alert['request_path'] ?? ''),
                'threat_level' => (string) ($alert['threat_level'] ?? ''),
                'current_risk_score' => (int) ($alert['current_risk_score'] ?? 0),
                'raw_details' => (array) ($alert['raw_details'] ?? []),
                'ingested_at' => $now,
            ];

            if ($sensorTokenId > 0) {
                $document['sensor_token_id'] = $sensorTokenId;
                $document['sensor_name'] = $sensorName;
            }

            $filter = $sensorTokenId > 0
                ? ['site_id' => $siteId, 'sensor_token_id' => $sensorTokenId, 'alert_id' => $alertId]
                : ['site_id' => $siteId, 'alert_id' => $alertId];

            $writeResult = $collection->updateOne(
                $filter,
                ['$set' => $document],
                ['upsert' => true]
            );

            if ($writeResult->getUpsertedCount() > 0) {
                $ingested++;
            } elseif ($writeResult->getModifiedCount() > 0 || $writeResult->getMatchedCount() > 0) {
                $updated++;
            }
        }

        return [
            'alerts_ingested' => $ingested,
            'alerts_updated' => $updated,
        ];
    }

    protected function storeSummary(int $siteId, array $summary, array $sensor = []): bool
    {
        $collection = $this->mongo->collection('summaries');
        $periodStart = $this->toUtcDateTime($summary['period_start']);
        $timestamp = $this->toUtcDateTime($summary['timestamp'] ?? $summary['period_start']);
        $sensorTokenId = (int) ($sensor['sensor_token_id'] ?? 0);
        $sensorName = (string) ($sensor['sensor_name'] ?? '');

        $document = [
            'site_id' => $siteId,
            'period_start' => $periodStart,
            'timestamp' => $timestamp,
            'total_hits' => (int) ($summary['total_hits'] ?? 0),
            'unique_ips' => (int) ($summary['unique_ips'] ?? 0),
            'by_severity' => (array) ($summary['by_severity'] ?? []),
            'ingested_at' => new UTCDateTime(),
        ];

        if ($sensorTokenId > 0) {
            $document['sensor_token_id'] = $sensorTokenId;
            $document['sensor_name'] = $sensorName;
        }

        $filter = $sensorTokenId > 0
            ? ['site_id' => $siteId, 'sensor_token_id' => $sensorTokenId, 'period_start' => $periodStart]
            : ['site_id' => $siteId, 'period_start' => $periodStart];

        $collection->updateOne(
            $filter,
            ['$set' => $document],
            ['upsert' => true]
        );

        return true;
    }

    protected function toUtcDateTime($value): UTCDateTime
    {
        if ($value instanceof UTCDateTime) {
            return $value;
        }

        if (is_numeric($value)) {
            $seconds = (int) $value;

            if ($seconds > 9999999999) {
                return new UTCDateTime($seconds);
            }

            return new UTCDateTime($seconds * 1000);
        }

        $date = new \DateTimeImmutable((string) $value);

        return new UTCDateTime($date->getTimestamp() * 1000);
    }
}
