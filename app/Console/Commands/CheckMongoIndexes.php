<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;

class CheckMongoIndexes extends Command {
    protected $signature = 'mongo:check-indexes';
    public function handle() {
        $mongo_uri = config('app.DB_MONGO_DEV');
        $client = new \MongoDB\Client($mongo_uri);
        $db = $client->sosecure_threatintelligent;
        
        $collections = ['fx_otx_indicator_detail', 'fx_otx_events_indicator_ref'];
        
        foreach ($collections as $collName) {
            $this->info("Indexes for $collName:");
            $indexes = $db->{$collName}->listIndexes();
            foreach ($indexes as $index) {
                $this->line(" - " . json_encode($index->getKey()));
            }
            $this->line("");
        }
    }
}
