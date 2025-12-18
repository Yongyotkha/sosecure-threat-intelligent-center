<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\IndicatorCheckService;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\EachPromise;

class CheckIndicators extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'indicators:check {--file= : Path to CSV file} {--manual : Manual input mode} {--limit=100 : Maximum number of indicators to process} {--resume : Resume from last checkpoint}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check IOCs against Threat Intelligence APIs (VT, AbuseIPDB, OTX, ThreatFox, RSTCloud)';

    /**
     * The indicator check service instance.
     *
     * @var IndicatorCheckService
     */
    protected $service;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(IndicatorCheckService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info("\n=== IOC Threat Checker ===\n");
        $this->info("Fetching indicators from events...\n");

        $results = $this->processMongoInput();
        
        if (empty($results)) {
            $this->error("No IOCs processed. Exiting.");
            return 1;
        }

        $this->info("\n=== Final Summary ===");
        $this->displaySummary($results);
        
        return 0;
    }

    // --- Input Processing Methods ---

    protected function processFileInput()
    {
        $path = $this->option('file') ?? $this->ask('Please enter the full path to the CSV file');
        $path = str_replace(['"', "'"], '', $path); 

        if (!file_exists($path)) {
            $this->error("❌ File not found: $path");
            return [[], null];
        }

        $iocs = [];
        if (($handle = fopen($path, "r")) !== FALSE) {
            // Read Header
            $header = fgetcsv($handle, 1000, ",");
            
            // Analyze a few rows to find the IOC column
            $rows = [];
            for ($i = 0; $i < 5; $i++) {
                $row = fgetcsv($handle, 1000, ",");
                if ($row) $rows[] = $row;
            }
            
            if (empty($rows)) { 
                 fclose($handle);
                 return [[], $path];
            }

            $targetColIndex = $this->detectIocColumn($rows);
            
            // Rewind and skip header
            rewind($handle);
            fgetcsv($handle); 

            while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                 if (isset($row[$targetColIndex])) {
                     $val = trim($row[$targetColIndex]);
                     if ($val) {
                        $iocs[] = [
                            'ioc' => $val, 
                            'type' => $this->detectType($val)
                        ];
                     }
                 }
            }
            fclose($handle);
        }

        // Limit Option logic
        $totalIocs = count($iocs);
        $limit = $this->ask("Enter the number of IOCs to analyze (1-{$totalIocs}, Enter for all)");
        
        if ($limit !== null && is_numeric($limit) && $limit > 0 && $limit < $totalIocs) {
            $iocs = array_slice($iocs, 0, (int)$limit);
        }

        return [$iocs, $path];
    }

    protected function processMongoInput()
    {
        try {
            $DB_MONGO_KEY = env("DB_MONGO_STOREDATAB", "");
            if (empty($DB_MONGO_KEY)) {
                $this->error("DB_MONGO_STOREDATAB not found in .env");
                return [];
            }
            if (!class_exists(\MongoDB\Client::class)) {
                $this->error("MongoDB library not found.");
                return [];
            }

            $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
            
            // Fetch events within today's date range
            $eventsCollection = $clientMD->sosecure_threatintelligent_dev->fx_otx_events;
            
            // Get the most recent event to determine the latest date
            $latestEvent = $eventsCollection->findOne([], ['sort' => ['modified' => -1]]);
            
            if (!$latestEvent || !isset($latestEvent['modified'])) {
                $this->warn("No events found in collection. Fetching random indicators instead...");
                return $this->processMongoInputFallback($clientMD);
            }
            
            // Get the latest modified date
            $latestDate = $latestEvent['modified']->toDateTime();
            $latestDate->setTimezone(new \DateTimeZone('Asia/Bangkok'));
            
            // Fetch events from the last 24 hours of the latest date
            $startDate = clone $latestDate;
            $startDate->modify('-24 hours');
            
            $this->info("Fetching events from " . $startDate->format('Y-m-d H:i:s') . " to " . $latestDate->format('Y-m-d H:i:s'));
            $this->info("(Using latest event date as reference)");
            
            $query = [
                'modified' => [
                    '$gte' => new \MongoDB\BSON\UTCDateTime($startDate->getTimestamp() * 1000),
                    '$lte' => new \MongoDB\BSON\UTCDateTime($latestDate->getTimestamp() * 1000)
                ]
            ];
            
            $events = $eventsCollection->find($query, ['sort' => ['modified' => -1]])->toArray();
            
            if (empty($events)) {
                $this->warn("No events found for today. Fetching 10 random indicators instead...");
                return $this->processMongoInputFallback($clientMD);
            }
            
            $this->info("Found " . count($events) . " events");
            
            // Get limit from option
            $limit = (int)$this->option('limit');
            if ($limit <= 0) $limit = 100;
            
            // Job progress tracking
            $jobProgressCollection = $clientMD->sosecure_threatintelligent_dev->job_progress;
            $jobName = 'indicators_check';
            $isResume = $this->option('resume');
            
            // Load or create job progress
            $jobProgress = $jobProgressCollection->findOne(['job_name' => $jobName]);
            $startEventIndex = 0;
            $totalProcessed = 0;
            
            if ($isResume && $jobProgress && $jobProgress['status'] === 'running') {
                // Resume from previous job
                $startEventIndex = $jobProgress['last_event_index'] + 1;
                $totalProcessed = $jobProgress['total_processed'] ?? 0;
                
                // Use the same date range as the original job
                $startDate = $jobProgress['date_range_start']->toDateTime();
                $endDate = $jobProgress['date_range_end']->toDateTime();
                
                $this->info("Resuming from Event " . ($startEventIndex + 1) . " (Previously processed: {$totalProcessed} indicators)");
                $this->info("Using original date range: " . $startDate->format('Y-m-d H:i:s') . " to " . $endDate->format('Y-m-d H:i:s'));
                
                // Re-fetch events with the same date range
                $query = [
                    'modified' => [
                        '$gte' => new \MongoDB\BSON\UTCDateTime($startDate->getTimestamp() * 1000),
                        '$lte' => new \MongoDB\BSON\UTCDateTime($endDate->getTimestamp() * 1000)
                    ]
                ];
                $events = $eventsCollection->find($query, ['sort' => ['modified' => -1]])->toArray();
                
            } else {
                // Start new job - use latest events
                $this->info("Starting new job...");
                
                // Initialize new job progress with date range
                $jobProgressCollection->updateOne(
                    ['job_name' => $jobName],
                    ['$set' => [
                        'job_name' => $jobName,
                        'started_at' => new \MongoDB\BSON\UTCDateTime(),
                        'date_range_start' => new \MongoDB\BSON\UTCDateTime($startDate->getTimestamp() * 1000),
                        'date_range_end' => new \MongoDB\BSON\UTCDateTime($latestDate->getTimestamp() * 1000),
                        'last_event_index' => -1,
                        'total_events' => count($events),
                        'total_processed' => 0,
                        'total_skipped' => 0,
                        'status' => 'running'
                    ]],
                    ['upsert' => true]
                );
            }
            
            // For each event, fetch its indicators
            $indicatorRefCollection = $clientMD->sosecure_threatintelligent_dev->fx_otx_events_indicator_ref;
            $iocs = [];
            $totalIndicators = $totalProcessed;
            $skippedCount = 0;
            
            // Process events one by one
            foreach ($events as $eventIndex => $event) {
                // Skip already processed events if resuming
                if ($eventIndex < $startEventIndex) {
                    continue;
                }
                
                if ($totalIndicators >= $limit) {
                    $this->warn("Reached limit of {$limit} indicators. Stopping...");
                    break;
                }
                
                $pulseId = $event['pulse_id'] ?? null;
                if (!$pulseId) continue;
                
                $eventName = $event['name'] ?? 'Unknown Event';
                $eventTags = $event['tags'] ?? [];
                
                $this->info("\nProcessing Event " . ($eventIndex + 1) . "/" . count($events) . ": " . substr($eventName, 0, 50));
                
                // Fetch indicators for this event
                $indicators = $indicatorRefCollection->find([
                    'pulse_id' => $pulseId,
                    'status' => 1
                ])->toArray();
                
                $eventIocs = [];
                foreach ($indicators as $doc) {
                    if ($totalIndicators >= $limit) {
                        break;
                    }
                    
                    // Skip if already has score/severity
                    if (isset($doc['threat_score']) || isset($doc['risk_level']) || isset($doc['severity'])) {
                        $skippedCount++;
                        continue;
                    }
                    
                    if (isset($doc['indicator'])) {
                        $val = $doc['indicator'];
                        $rawType = $doc['type'] ?? 'unknown';
                        
                        $type = 'unknown';
                        if (strpos($rawType, 'ip') !== false) $type = 'ip';
                        elseif (strpos($rawType, 'domain') !== false) $type = 'domain';
                        elseif (strpos($rawType, 'md5') !== false) $type = 'md5';
                        elseif (strpos($rawType, 'sha') !== false) $type = 'sha256';
                        elseif (strpos($rawType, 'url') !== false) $type = 'url';
                        else $type = $this->detectType($val);
                        
                        if ($type !== 'unknown') {
                            $eventIocs[] = [
                                'ioc' => $val, 
                                'type' => $type,
                                'pulse_id' => $pulseId,
                                'event_name' => $eventName,
                                'event_tags' => is_array($eventTags) ? $eventTags : [],
                                'indicator_id' => (string)$doc['_id']
                            ];
                            $totalIndicators++;
                        }
                    }
                }
                
                // Process this event's indicators immediately
                if (!empty($eventIocs)) {
                    $this->info("  Found " . count($eventIocs) . " new indicators (skipped {$skippedCount} already scored)");
                    $this->processEventIndicators($eventIocs, $iocs);
                }
                
                // Save checkpoint after each event
                $jobProgressCollection->updateOne(
                    ['job_name' => $jobName],
                    ['$set' => [
                        'last_event_index' => $eventIndex,
                        'total_processed' => $totalIndicators,
                        'total_skipped' => $skippedCount,
                        'last_updated' => new \MongoDB\BSON\UTCDateTime(),
                        'status' => 'running'
                    ]]
                );
            }
            
            // Mark job as completed
            $jobProgressCollection->updateOne(
                ['job_name' => $jobName],
                ['$set' => [
                    'status' => 'completed',
                    'completed_at' => new \MongoDB\BSON\UTCDateTime(),
                    'total_processed' => $totalIndicators,
                    'total_skipped' => $skippedCount
                ]]
            );
            
            if ($skippedCount > 0) {
                $this->info("\nTotal skipped (already scored): {$skippedCount}");
            }
            
            return $iocs;
        } catch (\Exception $e) {
            $this->error("MongoDB Error: " . $e->getMessage());
            return [];
        }
    }
    
    protected function processMongoInputFallback($clientMD)
    {
        try {
            $collection = $clientMD->sosecure_threatintelligent_dev->fx_otx_events_indicator_ref;
            
            $pipeline = [
                ['$sample' => ['size' => 10]]
            ];
            $cursor = $collection->aggregate($pipeline);
            
            $iocs = [];
            foreach ($cursor as $doc) {
                if (isset($doc['indicator'])) {
                    $val = $doc['indicator'];
                    $rawType = $doc['type'] ?? 'unknown';
                    
                    $type = 'unknown';
                    if (strpos($rawType, 'ip') !== false) $type = 'ip';
                    elseif (strpos($rawType, 'domain') !== false) $type = 'domain';
                    elseif (strpos($rawType, 'md5') !== false) $type = 'md5';
                    elseif (strpos($rawType, 'sha') !== false) $type = 'sha256';
                    elseif (strpos($rawType, 'url') !== false) $type = 'url';
                    else $type = $this->detectType($val);
                    
                    if ($type !== 'unknown') {
                        $iocs[] = [
                            'ioc' => $val, 
                            'type' => $type,
                            'pulse_id' => $doc['pulse_id'] ?? null,
                            'event_name' => 'Random Sample',
                            'event_tags' => []
                        ];
                    }
                }
            }
            
            return $iocs;
        } catch (\Exception $e) {
            $this->error("Fallback Error: " . $e->getMessage());
            return [];
        }
    }
    
    protected function processEventIndicators($eventIocs, &$allIocs)
    {
        if (empty($eventIocs)) return;
        
        $this->line("  Processing " . count($eventIocs) . " indicators...");
        
        // One client for all requests
        $client = new Client(['verify' => false, 'timeout' => 15]);
        $eventResults = [];
        
        // Create progress bar for this event
        $bar = $this->output->createProgressBar(count($eventIocs));
        $bar->setFormat('  %current%/%max% [%bar%] %percent:3s%%');
        $bar->start();
        
        // Create a generator for promises
        $promises = (function () use ($client, $eventIocs) {
            foreach ($eventIocs as $item) {
                yield $this->service->checkIocAsync($client, $item['ioc'], $item['type'])
                    ->then(function ($result) use ($item) {
                        return array_merge($result, [
                            'event_name' => $item['event_name'] ?? 'N/A',
                            'event_tags' => $item['event_tags'] ?? [],
                            'pulse_id' => $item['pulse_id'] ?? null,
                            'indicator_id' => $item['indicator_id'] ?? null
                        ]);
                    });
            }
        })();
        
        // Process with concurrency
        $each = new EachPromise($promises, [
            'concurrency' => 5,
            'fulfilled' => function ($result) use ($bar, &$eventResults, &$allIocs) {
                $eventResults[] = $result;
                $allIocs[] = $result;
                $bar->advance();
            },
            'rejected' => function ($reason) {
                // Handle failure
            }
        ]);
        
        $each->promise()->wait();
        $bar->finish();
        $this->line("");
        
        // Show summary for this event
        $this->displayEventSummary($eventResults);
    }
    
    protected function displayEventSummary($results)
    {
        if (empty($results)) return;
        
        $highRisk = 0;
        $mediumRisk = 0;
        $lowRisk = 0;
        
        foreach ($results as $res) {
            $risk = $res['risk_level'] ?? 'Informational';
            if (in_array($risk, ['Critical', 'High'])) $highRisk++;
            elseif ($risk === 'Medium') $mediumRisk++;
            elseif (in_array($risk, ['Low', 'Very Low'])) $lowRisk++;
        }
        
        $this->line("  Results: " . 
            ($highRisk > 0 ? "<fg=red>{$highRisk} High</> " : "") .
            ($mediumRisk > 0 ? "<fg=yellow>{$mediumRisk} Medium</> " : "") .
            ($lowRisk > 0 ? "<fg=green>{$lowRisk} Low</> " : "") .
            (($highRisk + $mediumRisk + $lowRisk) === 0 ? "All Informational" : "")
        );
    }

    protected function processManualInput()
    {
        $this->info("\nEnter IOC one by one. Type 'done' to finish.\n");
        $iocs = [];
        $count = 1;
        while (true) {
            $val = $this->ask("Enter IOC {$count}");
            if (strtolower($val) === 'done' || $val === null) break;
            if (trim($val) !== '') {
                $iocs[] = ['ioc' => $val, 'type' => $this->detectType($val)];
                $count++;
            }
        }
        return $iocs;
    }

    protected function detectIocColumn($rows)
    {
        $scores = [];
        foreach ($rows as $row) {
            foreach ($row as $index => $col) {
                $val = trim($col);
                if (filter_var($val, FILTER_VALIDATE_IP)) $scores[$index] = ($scores[$index] ?? 0) + 5;
                elseif (filter_var($val, FILTER_VALIDATE_URL)) $scores[$index] = ($scores[$index] ?? 0) + 4;
                elseif ($this->detectType($val) === 'domain' && strpos($val, ' ') === false) $scores[$index] = ($scores[$index] ?? 0) + 2;
            }
        }
        if (empty($scores)) return 0;
        arsort($scores);
        return array_key_first($scores);
    }

    protected function detectType($ioc)
    {
        if (filter_var($ioc, FILTER_VALIDATE_IP)) return 'ip';
        if (filter_var($ioc, FILTER_VALIDATE_URL)) return 'url';
        if (preg_match('/^[a-f0-9]{32}$/i', $ioc)) return 'md5';
        if (preg_match('/^[a-f0-9]{40}$/i', $ioc)) return 'sha1';
        if (preg_match('/^[a-f0-9]{64}$/i', $ioc)) return 'sha256';
        if (preg_match('/^([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}$/i', $ioc)) return 'domain';
        return 'unknown';
    }

    protected function displaySummary($results)
    {
        $headers = ['IOC', 'Type', 'Event', 'VT', 'Abuse', 'TF', 'OTX', 'RST Score', 'Summary Score', 'Risk', 'Tags'];
        $data = [];

        foreach ($results as $res) {
            $iocType = $res['type'];
            if (in_array($iocType, ['md5', 'sha1', 'sha256'])) $iocType = 'hash';

            // VT
            $vt = $res['vt']['malicious'] ?? 0;

            // AbuseIPDB (IP Only)
            $abuse = ($res['type'] === 'ip') ? ($res['abuse']['score'] ?? '-') : '-';

            // ThreatFox
            $tfKey = $res['threatfox']['confidence_level'] ?? 0;
            $tf = ($tfKey > 0) ? $tfKey : '0';

            // OTX
            $otxKey = $res['otx']['pulse_count'] ?? 0;
            $otx = ($otxKey > 0) ? $otxKey : '0';

            // RST Score
            $rstKey = $res['rstcloud_score'];
            $rst = ($rstKey !== null && $rstKey !== 'N/A' && $rstKey > 0) ? $rstKey : 'N/A';

            // Event Info
            $eventName = $res['event_name'] ?? 'N/A';
            $eventDisplay = strlen($eventName) > 30 ? substr($eventName, 0, 30) . '...' : $eventName;

            // Tags - Combine event tags with threat intelligence tags
            $tags = [];
            
            // Add event tags first
            if (!empty($res['event_tags']) && is_array($res['event_tags'])) {
                $tags = array_merge($tags, $res['event_tags']);
            }
            
            // Add VT tags
            if (!empty($res['vt']['unique_results'])) {
                $tags = array_merge($tags, $res['vt']['unique_results']);
            }
            
            // Add RST Cloud tags
            if (!empty($res['rstcloud_threat'])) {
                 $tags = array_merge($tags, $res['rstcloud_threat']);
            }
            
            $tags = array_unique($tags);
            // Limit tags for display
            $displayTags = implode(', ', array_slice($tags, 0, 5));
            if (count($tags) > 5) {
                $displayTags .= " ... (" . (count($tags) - 5) . " more)";
            }

            $data[] = [
                $res['ioc'],
                $iocType,
                $eventDisplay,
                $vt,
                $abuse,
                $tf,
                $otx,
                $rst,
                number_format($res['total_score'], 1),
                $res['risk_level'],
                $displayTags
            ];
        }

        $this->info("=== Summary ===");
        $this->table($headers, $data);
        $this->info("--- Analysis Complete ---");
    }
}
