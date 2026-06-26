<?php

namespace App\Services;

use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Database;

class HoneypotMongoService
{
    protected $clients = [];

    protected $databases = [];

    public function getTarget(): string
    {
        $target = strtolower((string) config('honeypot.mongodb.target', 'dev'));

        return in_array($target, ['dev', 'prod'], true) ? $target : 'dev';
    }

    public function getProfile(?string $target = null): array
    {
        $target = $target ?? $this->getTarget();
        $profile = config("honeypot.mongodb.profiles.{$target}");

        if (!is_array($profile)) {
            throw new \RuntimeException('Unknown honeypot MongoDB profile: ' . $target);
        }

        $uri = trim((string) ($profile['uri'] ?? ''));
        $database = trim((string) ($profile['database'] ?? ''));

        if ($uri === '' || $database === '') {
            throw new \RuntimeException(
                'Honeypot MongoDB profile [' . $target . '] is incomplete. Check HONEYPOT_MONGO_* in .env.'
            );
        }

        return [
            'target' => $target,
            'uri' => $uri,
            'database' => $database,
        ];
    }

    public function getUri(?string $target = null): string
    {
        return $this->getProfile($target)['uri'];
    }

    public function getDatabaseName(?string $target = null): string
    {
        return $this->getProfile($target)['database'];
    }

    public function getClient(?string $target = null): Client
    {
        $target = $target ?? $this->getTarget();

        if (!isset($this->clients[$target])) {
            $this->clients[$target] = new Client($this->getUri($target));
        }

        return $this->clients[$target];
    }

    public function getDatabase(?string $target = null): Database
    {
        $target = $target ?? $this->getTarget();

        if (!isset($this->databases[$target])) {
            $profile = $this->getProfile($target);
            $this->databases[$target] = $this->getClient($target)->selectDatabase($profile['database']);
        }

        return $this->databases[$target];
    }

    public function collection(string $key, ?string $target = null): Collection
    {
        $collectionName = config("honeypot.mongodb.collections.{$key}");

        if (!$collectionName) {
            throw new \InvalidArgumentException("Unknown honeypot collection key: {$key}");
        }

        return $this->getDatabase($target)->selectCollection($collectionName);
    }

    public function indicatorCollection(string $key, ?string $target = null): Collection
    {
        $collectionName = config("honeypot.mongodb.indicator_collections.{$key}");

        if (!$collectionName) {
            throw new \InvalidArgumentException("Unknown honeypot indicator collection key: {$key}");
        }

        return $this->getDatabase($target)->selectCollection($collectionName);
    }

    public function getTtlSeconds(): int
    {
        $days = (int) config('honeypot.mongodb.ttl_days', 30);

        return max(1, $days) * 86400;
    }

    public function ensureIndexes(): array
    {
        $ttlSeconds = $this->getTtlSeconds();
        $created = [
            'alerts' => [],
            'summaries' => [],
        ];

        $alerts = $this->collection('alerts');
        $alerts->createIndex(
            ['timestamp' => 1],
            ['name' => 'honeypot_alerts_timestamp_ttl', 'expireAfterSeconds' => $ttlSeconds]
        );
        $created['alerts'][] = 'honeypot_alerts_timestamp_ttl';

        $alerts->createIndex(
            ['site_id' => 1, 'timestamp' => -1],
            ['name' => 'honeypot_alerts_site_timestamp']
        );
        $created['alerts'][] = 'honeypot_alerts_site_timestamp';

        $alerts->createIndex(
            ['site_id' => 1, 'sensor_token_id' => 1, 'timestamp' => -1],
            ['name' => 'honeypot_alerts_site_sensor_timestamp']
        );
        $created['alerts'][] = 'honeypot_alerts_site_sensor_timestamp';

        $this->dropIndexIfExists($alerts, 'honeypot_alerts_site_alert_id');
        $this->dropIndexIfExists($alerts, 'honeypot_alerts_site_alert_legacy');

        $alerts->createIndex(
            ['site_id' => 1, 'sensor_token_id' => 1, 'alert_id' => 1],
            [
                'name' => 'honeypot_alerts_site_sensor_alert',
                'unique' => true,
                'partialFilterExpression' => ['sensor_token_id' => ['$exists' => true]],
            ]
        );
        $created['alerts'][] = 'honeypot_alerts_site_sensor_alert';

        $summaries = $this->collection('summaries');
        $summaries->createIndex(
            ['timestamp' => 1],
            ['name' => 'honeypot_summaries_timestamp_ttl', 'expireAfterSeconds' => $ttlSeconds]
        );
        $created['summaries'][] = 'honeypot_summaries_timestamp_ttl';

        $summaries->createIndex(
            ['site_id' => 1, 'timestamp' => -1],
            ['name' => 'honeypot_summaries_site_timestamp']
        );
        $created['summaries'][] = 'honeypot_summaries_site_timestamp';

        $this->dropIndexIfExists($summaries, 'honeypot_summaries_site_period');
        $this->dropIndexIfExists($summaries, 'honeypot_summaries_site_period_legacy');

        $summaries->createIndex(
            ['site_id' => 1, 'sensor_token_id' => 1, 'period_start' => 1],
            [
                'name' => 'honeypot_summaries_site_sensor_period',
                'unique' => true,
                'partialFilterExpression' => ['sensor_token_id' => ['$exists' => true]],
            ]
        );
        $created['summaries'][] = 'honeypot_summaries_site_sensor_period';

        return $created;
    }

    protected function dropIndexIfExists(Collection $collection, string $indexName): void
    {
        try {
            $collection->dropIndex($indexName);
        } catch (\Throwable $e) {
            // Index may not exist yet on first deploy.
        }
    }

    public function ping(?string $target = null): bool
    {
        $this->getDatabase($target)->command(['ping' => 1]);

        return true;
    }
}
