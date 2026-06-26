<?php

namespace App\Console\Commands;

use App\Services\HoneypotMongoService;
use Illuminate\Console\Command;

class EnsureHoneypotIndexes extends Command
{
    protected $signature = 'honeypot:ensure-indexes {--ping : Only test MongoDB connectivity}';

    protected $description = 'Verify honeypot MongoDB connectivity and ensure TTL/query indexes';

    public function handle(HoneypotMongoService $mongo)
    {
        $this->line('Honeypot Center — MongoDB setup');
        $this->line('Target   : ' . $mongo->getTarget());
        $this->line('URI      : ' . $this->maskUri($mongo->getUri()));
        $this->line('Database : ' . $mongo->getDatabaseName());
        $this->line('Alerts   : ' . config('honeypot.mongodb.collections.alerts'));
        $this->line('Summaries: ' . config('honeypot.mongodb.collections.summaries'));
        $this->line('TTL      : ' . config('honeypot.mongodb.ttl_days') . ' day(s)');

        try {
            $mongo->ping();
            $this->info('MongoDB connection: OK');
        } catch (\Throwable $e) {
            $this->error('MongoDB connection failed: ' . $e->getMessage());

            return 1;
        }

        if ($this->option('ping')) {
            return 0;
        }

        try {
            $created = $mongo->ensureIndexes();
        } catch (\Throwable $e) {
            $this->error('Index setup failed: ' . $e->getMessage());

            return 1;
        }

        foreach ($created as $collection => $indexes) {
            $this->info('Indexes on ' . $collection . ': ' . implode(', ', $indexes));
        }

        $this->info('Honeypot MongoDB indexes are ready.');

        return 0;
    }

    protected function maskUri(string $uri): string
    {
        return (string) preg_replace('/(mongodb(?:\+srv)?:\/\/)([^:]+):([^@]+)@/i', '$1$2:***@', $uri);
    }
}
