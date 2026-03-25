<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateIocFeed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ioc-feed:update {--all : Sync all data instead of just today} {--limit= : Limit the number of indicators to sync}';
    protected $description = 'Sync IoCs from production OTX data to the IocFeed collection';

    public function handle()
    {
        $this->info('Starting IoC Feed Update...');

        $mongo_uri = config('app.DB_MONGO_DEV');
        $client = new \MongoDB\Client($mongo_uri);
        
        $sourceDb = 'sosecure_threatintelligent';
        $targetDb = config('iocfeed.mongodb.database', 'sosecure_threatintelligent_dev');
        
        $sourceColl = $client->{$sourceDb}->fx_transaction_otx_indicators_data;
        $targetColl = $client->{$targetDb}->fx_ioc_feeds;

        // 1. กำหนดช่วงเวลา (วันนี้)
        $query = [];
        if (!$this->option('all')) {
            $startOfDay = new \MongoDB\BSON\UTCDateTime(strtotime('today midnight') * 1000);
            $query = [
                'updated_at' => ['$gte' => $startOfDay]
            ];
            $this->info('Syncing data from today...');
        } else {
            $this->info('Syncing ALL data...');
        }

        $limit = (int) $this->option('limit');
        $options = [];
        if ($limit > 0) {
            $options['limit'] = $limit;
            $this->info("Limited to {$limit} records.");
        }

        $cursor = $sourceColl->find($query, $options);
        $count = 0;
        $batch = [];
        $batchSize = 500;

        foreach ($cursor as $doc) {
            $type = $this->mapType($doc['type'] ?? '');
            if (!$type) continue;

            $batch[] = [
                'updateOne' => [
                    ['indicator' => $doc['indicator']],
                    ['$set' => [
                        'indicator' => $doc['indicator'],
                        'type' => $type,
                        'score' => $doc['score'] ?? 0,
                        'ioc_timestamp' => date('d/m/Y H:i', ($doc['created_at'] ? $doc['created_at']->toDateTime()->getTimestamp() : time())),
                        'sending_timestamp' => date('d/m/Y H:i', ($doc['updated_at'] ? $doc['updated_at']->toDateTime()->getTimestamp() : time())),
                        'category' => $doc['type'] ?? '-',
                        'severity' => $this->mapSeverity($doc['score'] ?? 0),
                        'indicator_id' => $doc['indicator_id'] ?? '',
                        'event_id' => (string) ($doc['transcation_id'] ?? ''),
                        'status' => 1,
                        'updated_at' => new \MongoDB\BSON\UTCDateTime(),
                    ],
                    '$setOnInsert' => [
                        'created_at' => new \MongoDB\BSON\UTCDateTime(),
                    ]],
                    ['upsert' => true]
                ]
            ];

            if (count($batch) >= $batchSize) {
                $targetColl->bulkWrite($batch);
                $count += count($batch);
                $batch = [];
                $this->info("Synced {$count} indicators...");
            }
        }

        if (!empty($batch)) {
            $targetColl->bulkWrite($batch);
            $count += count($batch);
        }

        $this->info("Successfully synced total {$count} indicators.");
    }

    private function mapType($otxType)
    {
        $mapping = [
            'IPv4' => 'ip_address',
            'IPv6' => 'ip_address',
            'domain' => 'domain',
            'hostname' => 'domain',
            'FileHash-MD5' => 'hashfile',
            'FileHash-SHA1' => 'hashfile',
            'FileHash-SHA256' => 'hashfile',
        ];

        return $mapping[$otxType] ?? null;
    }

    private function mapSeverity($score)
    {
        if ($score >= 8) return 'Critical';
        if ($score >= 6) return 'High';
        if ($score >= 4) return 'Medium';
        return 'Low';
    }
}
