<?php

namespace App\Services;

use MongoDB\BSON\UTCDateTime;

class PublishedFeedsService
{
    public static function mongoDatabase(): string
    {
        return 'sosecure_threatintelligent';
    }

    public static function database($client)
    {
        return $client->{self::mongoDatabase()};
    }

    public static function rollingWindow(): array
    {
        return [
            'start' => strtotime('-1 day') * 1000,
            'end' => (int) round(microtime(true) * 1000),
        ];
    }

    public static function buildQuery(int $start, int $end, ?string $sourceFilter = null): array
    {
        $query = [
            'status'          => ['$in' => [1, "1", true]],
            'public'          => ['$in' => [1, "1", true]],
            'modified'        => [
                '$gte' => new UTCDateTime($start),
                '$lte' => new UTCDateTime($end),
            ],
            'indicator_count' => ['$gt' => 0],
            '$or'             => [
                ['deleted_at' => null],
                ['deleted_at' => ['$exists' => false]],
            ],
            'creator_org'     => 'OTX',
        ];

        if ($sourceFilter) {
            $query['source'] = $sourceFilter;
        }

        return $query;
    }

    public static function eventFromDocument(array $document, ?string $mipsUuid = null): array
    {
        $uuid = $mipsUuid ?? ($document['mips_uuid'] ?? null);
        $indicatorCount = (int) ($document['attrCount'] ?? $document['indicator_count'] ?? 0);

        return [
            'pulse_id'  => $document['pulse_id'] ?? null,
            'mips_uuid' => $uuid,
            'uuid'      => $uuid,
            'name'      => $document['name'] ?? 'No Name',
            'info'      => $document['name'] ?? 'No Name',
            'count'     => $indicatorCount,
            'published' => 1,
        ];
    }

    public static function mergeEventsByPulseId(array $existingEvents, array $newEvents): array
    {
        $merged = [];

        foreach ($existingEvents as $event) {
            $event = (array) $event;
            if (!isset($event['pulse_id'])) {
                continue;
            }

            $merged[(string) $event['pulse_id']] = self::eventFromDocument($event, $event['mips_uuid'] ?? $event['uuid'] ?? null);
        }

        foreach ($newEvents as $event) {
            if (!isset($event['pulse_id'])) {
                continue;
            }

            $merged[(string) $event['pulse_id']] = $event;
        }

        $events = array_values($merged);
        $totalIndicators = 0;

        foreach ($events as $event) {
            $totalIndicators += (int) ($event['count'] ?? 0);
        }

        return [
            'events'              => $events,
            'total_public_events' => count($events),
            'total_indicators'    => $totalIndicators,
        ];
    }

    public static function ensureUniqueMipsUuid(array $document, $collection, array &$usedUuids): string
    {
        $mipsUuid = $document['mips_uuid'] ?? null;
        $pulseId = $document['pulse_id'] ?? null;

        if (!$mipsUuid || isset($usedUuids[(string) $mipsUuid])) {
            $mipsUuid = self::generateUuidV4();

            if ($pulseId) {
                $collection->updateOne(
                    ['pulse_id' => $pulseId],
                    ['$set' => ['mips_uuid' => $mipsUuid]]
                );
            }
        }

        $usedUuids[(string) $mipsUuid] = true;

        return $mipsUuid;
    }

    public static function generateUuidV4(): string
    {
        $data = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public static function todayLogFilter(): array
    {
        return ['action_time' => ['$regex' => '^' . date('Y-m-d')]];
    }

    public static function eventPullEntry(array $document, int $attributeCount): array
    {
        $entry = self::eventFromDocument($document);
        $entry['indicator_count'] = $attributeCount;
        $entry['count'] = $attributeCount;
        $entry['pulled_at'] = date('Y-m-d H:i:s');

        return $entry;
    }

    public static function persistLog(
        $logCol,
        string $logType,
        array $newEvents,
        string $source,
        array $extra = []
    ): array {
        $todayFilter = self::todayLogFilter();
        $existingLog = $logCol->findOne(array_merge(['type' => $logType], $todayFilter));
        $existingEvents = ($existingLog && isset($existingLog['events'])) ? (array) $existingLog['events'] : [];
        $merged = self::mergeEventsByPulseId($existingEvents, $newEvents);

        $logCol->updateOne(
            array_merge(['type' => $logType], $todayFilter),
            ['$set' => array_merge([
                'timestamp' => new UTCDateTime(strtotime(now()) * 1000),
                'action_time' => date('Y-m-d H:i:s'),
                'type' => $logType,
                'total_public_events' => $merged['total_public_events'],
                'total_indicators' => $merged['total_indicators'],
                'events' => $merged['events'],
                'source' => $source,
            ], $extra)],
            ['upsert' => true]
        );

        return $merged;
    }
}
