<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\IndicatorCheckService;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\EachPromise;
use Illuminate\Support\Facades\Log;

class CheckIndicators extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:indicatorscheck {--file= : Path to CSV file} {--manual : Manual input mode} {--limit=0 : Maximum number of indicators to process (0 = unlimited)} {--resume : Resume from last checkpoint} {--force : Force start new job (ignore existing progress)} {--event= : Process single event by pulse_id} {--job= : Job ID for tracking progress} {--test= : Test single IOC with debug output (format: ioc:type)}';

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
        
        // Test mode - single IOC with full debug
        $testIoc = $this->option('test');
        if ($testIoc) {
            return $this->testSingleIoc($testIoc);
        }
        
        // Check if processing single event
        $eventId = $this->option('event');
        if ($eventId) {
            $this->info("Processing single event: {$eventId}\n");
            $results = $this->processSingleEvent($eventId);
        } else {
            $this->info("Fetching indicators from events...\n");
            $results = $this->processMongoInput();
        }
        
        if (empty($results)) {
            $this->error("No IOCs processed. Exiting.");
            return 1;
        }

        $this->info("\n=== Final Summary ===");
        $this->displaySummary($results);
        
        return 0;
    }
    
    /**
     * Test single IOC with full debug output
     * Usage: php artisan app:indicatorscheck --test="student56.ru.com:hostname"
     */
    protected function testSingleIoc($testInput)
    {
        // Parse input (format: ioc:type or just ioc)
        $parts = explode(':', $testInput, 2);
        $ioc = trim($parts[0]);
        $type = isset($parts[1]) ? trim($parts[1]) : $this->detectType($ioc);
        
        $this->info("Testing IOC: {$ioc}");
        $this->info("Type: {$type}");
        $this->info(str_repeat('-', 60));
        
        $client = new \GuzzleHttp\Client(['verify' => false, 'timeout' => 30]);
        
        // Call IOC check with debug
        $result = $this->service->checkIocAsync($client, $ioc, $type, null)->wait();
        
        // Display raw API responses
        $this->info("\n=== API Responses ===");
        
        // VirusTotal
        $vt = $result['vt'] ?? [];
        $this->line("\n<fg=cyan>VirusTotal:</>");
        $this->line("  Malicious: " . ($vt['malicious'] ?? 0));
        $this->line("  Suspicious: " . ($vt['suspicious'] ?? 0));
        $this->line("  Unique Results: " . implode(', ', array_slice($vt['unique_results'] ?? [], 0, 5)));
        
        // AbuseIPDB (IP only)
        if ($type === 'ip') {
            $abuse = $result['abuse'] ?? [];
            $this->line("\n<fg=cyan>AbuseIPDB:</>");
            $this->line("  Score: " . ($abuse['score'] ?? '-'));
            $this->line("  Total Reports: " . ($abuse['total_reports'] ?? 0));
        }
        
        // ThreatFox
        $tf = $result['threatfox'] ?? [];
        $this->line("\n<fg=cyan>ThreatFox:</>");
        $this->line("  Confidence Level: " . ($tf['confidence_level'] ?? 0));
        $this->line("  Tags: " . implode(', ', $tf['tags'] ?? []));
        
        // OTX
        $otx = $result['otx'] ?? [];
        $this->line("\n<fg=cyan>OTX AlienVault:</>");
        $this->line("  Pulse Count: " . ($otx['pulse_count'] ?? 0));
        
        // RSTCloud
        $this->line("\n<fg=cyan>RSTCloud:</>");
        $this->line("  Score: " . ($result['rstcloud_score'] ?? 'N/A'));
        $this->line("  Threat: " . implode(', ', $result['rstcloud_threat'] ?? []));
        
        // Score Calculation Debug
        $this->info("\n=== Score Calculation ===");
        $debugScores = $result['debug_scores'] ?? [];
        $debugWeights = $result['debug_weights'] ?? [];
        
        $this->line("\n<fg=yellow>Active Source Scores:</>");
        foreach ($debugScores as $source => $score) {
            $weight = $debugWeights[$source] ?? 0;
            $this->line("  {$source}: score={$score}, weight={$weight}");
        }
        
        $this->line("\n<fg=yellow>Calculation:</>");
        $totalWeight = array_sum($debugWeights);
        $weightedSum = 0;
        foreach ($debugScores as $source => $score) {
            $weight = $debugWeights[$source] ?? 0;
            $contrib = $score * $weight;
            $weightedSum += $contrib;
            $this->line("  {$source}: {$score} × {$weight} = {$contrib}");
        }
        $this->line("  Total weight: {$totalWeight}");
        $this->line("  Weighted sum: {$weightedSum}");
        if ($totalWeight > 0) {
            $rawScore = $weightedSum / $totalWeight;
            $this->line("  Raw score: {$weightedSum} / {$totalWeight} = " . number_format($rawScore, 2));
            $this->line("  Final score: round(" . number_format($rawScore, 2) . ") = " . round($rawScore));
        }
        
        // Final Result
        $this->info("\n=== Final Result ===");
        $this->line("<fg=green>Score: " . $result['total_score'] . "</>");
        $this->line("<fg=green>Risk Level: " . $result['risk_level'] . "</>");
        
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
            
            // Get or create data_key for this batch
            $dataKeyCollection = $clientMD->sosecure_threatintelligent_dev->fx_data_key;
            
            // Generate file_name with timestamp
            $timestamp = date('Y-m-d_H.i.s');
            $fileName = "Sosecure-Threat-Insight-Indicators-{$timestamp}-program";
            
            // Create new data_key for this batch
            $dataKey = md5(date('Y-m-d H:i:s') . uniqid());
            $dataKeyDoc = [
                'date' => date('Y-m-d H:i:s'),
                'data_key' => $dataKey,
                'imported_at' => date('Y-m-d H:i:s'),
                'record_count' => 0,
                'type' => 'attribute',
                'file_name' => $fileName,
                'timestamp' => time()
            ];
            
            $dataKeyCollection->insertOne($dataKeyDoc);
            $this->info("Created new data_key: {$dataKey}");
            $this->info("File name: {$fileName}");
            
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
            
            // Get limit from option (0 = unlimited)
            $limit = (int)$this->option('limit');
            if ($limit < 0) $limit = 0; // Negative values = unlimited
            $isUnlimited = ($limit === 0);
            if ($isUnlimited) {
                $this->info("Processing ALL indicators (unlimited mode)");
            } else {
                $this->info("Limit: {$limit} indicators");
            }
            
            // Job progress tracking
            $jobProgressCollection = $clientMD->sosecure_threatintelligent_dev->job_progress;
            $jobName = 'indicators_check';
            $isResume = $this->option('resume');
            $isForce = $this->option('force');
            
            // Load or create job progress
            $jobProgress = $jobProgressCollection->findOne(['job_name' => $jobName]);
            $startEventIndex = 0;
            $totalProcessed = 0;
            $autoResume = false;
            
            // Force start new job
            if ($isForce) {
                $this->warn("Force mode: Starting new job (ignoring existing progress)");
                $isResume = false;
                $autoResume = false;
            }
            // Check if we should auto-resume
            elseif (!$isResume && $jobProgress && $jobProgress['status'] === 'running') {
                // Check if the job's date range matches current latest events
                $jobStartDate = $jobProgress['date_range_start']->toDateTime();
                $jobEndDate = $jobProgress['date_range_end']->toDateTime();
                
                // Compare timestamps (within 1 hour tolerance for same batch)
                $startDiff = abs($jobStartDate->getTimestamp() - $startDate->getTimestamp());
                $endDiff = abs($jobEndDate->getTimestamp() - $latestDate->getTimestamp());
                
                if ($startDiff < 3600 && $endDiff < 3600) {
                    // Same date range (within 1 hour)
                    $autoResume = true;
                    $isResume = true;
                    $this->info("Auto-resuming: Found incomplete job for the same date range");
                } else {
                    $this->warn("Starting new job: Previous job was for a different date range");
                    $this->line("  Previous: " . $jobStartDate->format('Y-m-d H:i') . " to " . $jobEndDate->format('Y-m-d H:i'));
                    $this->line("  Current:  " . $startDate->format('Y-m-d H:i') . " to " . $latestDate->format('Y-m-d H:i'));
                }
            }
            
            if ($isResume && $jobProgress && $jobProgress['status'] === 'running') {
                // Resume from previous job
                $startEventIndex = $jobProgress['last_event_index'] + 1;
                $totalProcessed = $jobProgress['total_processed'] ?? 0;
                
                // Use the same date range as the original job
                $startDate = $jobProgress['date_range_start']->toDateTime();
                $endDate = $jobProgress['date_range_end']->toDateTime();
                
                if (!$autoResume) {
                    $this->info("Resuming from Event " . ($startEventIndex + 1) . " (Previously processed: {$totalProcessed} indicators)");
                } else {
                    $this->info("Continuing from Event " . ($startEventIndex + 1) . " ({$totalProcessed} indicators already processed)");
                }
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
                
                if (!$isUnlimited && $totalIndicators >= $limit) {
                    $this->warn("Reached limit of {$limit} indicators. Stopping...");
                    break;
                }
                
                $pulseId = $event['pulse_id'] ?? null;
                if (!$pulseId) continue;
                
                $eventName = $event['name'] ?? 'Unknown Event';
                $eventTags = $event['tags'] ?? [];
                $eventPublic = $event['public'] ?? 1; // Default to public
                
                $this->info("\nProcessing Event " . ($eventIndex + 1) . "/" . count($events) . ": " . substr($eventName, 0, 50));
                
                // Fetch indicators for this event
                $indicators = $indicatorRefCollection->find([
                    'pulse_id' => $pulseId,
                    'status' => 1
                ])->toArray();
                
                $eventIocs = [];
                foreach ($indicators as $doc) {
                    if (!$isUnlimited && $totalIndicators >= $limit) {
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
                                'event_public' => $eventPublic,
                                'indicator_id' => (string)$doc['_id']
                            ];
                            $totalIndicators++;
                        }
                    }
                }
                
                // Process this event's indicators immediately
                if (!empty($eventIocs)) {
                    $this->info("  Found " . count($eventIocs) . " new indicators (skipped {$skippedCount} already scored)");
                    $this->processEventIndicators($eventIocs, $iocs, $dataKey);
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
            
            // Update record_count in fx_data_key
            $dataKeyCollection->updateOne(
                ['data_key' => $dataKey],
                ['$set' => ['record_count' => $totalIndicators]]
            );
            
            if ($skippedCount > 0) {
                $this->info("\nTotal skipped (already scored): {$skippedCount}");
            }
            
            $this->info("Updated fx_data_key record_count: {$totalIndicators}");
            
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
    
    /**
     * Process a single event by pulse_id
     * 
     * @param string $pulseId
     * @return array
     */
    protected function processSingleEvent($pulseId)
    {
        $jobId = $this->option('job');
        $jobsCollection = null;
        
        try {
            $DB_MONGO_KEY = env("DB_MONGO_STOREDATAB", "");
            if (empty($DB_MONGO_KEY)) {
                $this->error("DB_MONGO_STOREDATAB not found in .env");
                return [];
            }
            
            $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
            
            // Setup job tracking if job_id provided
            if ($jobId) {
                $jobsCollection = $clientMD->sosecure_threatintelligent_dev->ioc_enrichment_jobs;
                $jobsCollection->updateOne(
                    ['job_id' => $jobId],
                    ['$set' => [
                        'status' => 'processing',
                        'started_at' => new \MongoDB\BSON\UTCDateTime()
                    ]]
                );
            }
            
            // Fetch the specific event first to get its date
            $eventsCollection = $clientMD->sosecure_threatintelligent_dev->fx_otx_events;
            $event = $eventsCollection->findOne(['pulse_id' => $pulseId]);
            
            if (!$event) {
                $this->error("Event not found with pulse_id: {$pulseId}");
                if ($jobsCollection && $jobId) {
                    $jobsCollection->updateOne(
                        ['job_id' => $jobId],
                        ['$set' => ['status' => 'failed', 'error' => 'Event not found', 'failed_at' => new \MongoDB\BSON\UTCDateTime()]]
                    );
                }
                return [];
            }
            
            $eventName = $event['name'] ?? 'Unknown Event';
            $eventTags = $event['tags'] ?? [];
            $eventPublic = $event['public'] ?? 1;
            
            // Get event date (use modified or created date)
            $eventDate = null;
            if (isset($event['modified']) && $event['modified'] instanceof \MongoDB\BSON\UTCDateTime) {
                $eventDate = $event['modified']->toDateTime();
            } elseif (isset($event['created']) && $event['created'] instanceof \MongoDB\BSON\UTCDateTime) {
                $eventDate = $event['created']->toDateTime();
            } else {
                $eventDate = new \DateTime();
            }
            $eventDate->setTimezone(new \DateTimeZone('Asia/Bangkok'));
            
            $this->info("Found event: " . substr($eventName, 0, 60));
            $this->info("Event date: " . $eventDate->format('Y-m-d'));
            
            // Get or create data_key for this batch
            $dataKeyCollection = $clientMD->sosecure_threatintelligent_dev->fx_data_key;
            
            // Generate file_name with timestamp
            $timestamp = date('Y-m-d_H.i.s');
            $fileName = "Sosecure-Threat-Insight-Indicators-{$timestamp}-single-event";
            
            // Create new data_key for this batch
            $dataKey = md5(date('Y-m-d H:i:s') . uniqid());
            $dataKeyDoc = [
                'date' => date('Y-m-d H:i:s'),
                'data_key' => $dataKey,
                'imported_at' => date('Y-m-d H:i:s'),
                'record_count' => 0,
                'type' => 'attribute',
                'file_name' => $fileName,
                'timestamp' => time(),
                'pulse_id' => $pulseId,
                'event_date' => $eventDate->format('Y-m-d')
            ];
            
            $dataKeyCollection->insertOne($dataKeyDoc);
            $this->info("Created new data_key: {$dataKey}");
            
            $indicatorRefCollection = $clientMD->sosecure_threatintelligent_dev->fx_otx_events_indicator_ref;
            
            $indicators = $indicatorRefCollection->find([
                'pulse_id' => $pulseId,
                'status' => 1
            ])->toArray();
            
            $this->info("Found " . count($indicators) . " indicators for this event");
            
            if (empty($indicators)) {
                $this->warn("No indicators found for this event");
                if ($jobsCollection && $jobId) {
                    $jobsCollection->updateOne(
                        ['job_id' => $jobId],
                        ['$set' => ['status' => 'completed', 'message' => 'No indicators to process', 'completed_at' => new \MongoDB\BSON\UTCDateTime()]]
                    );
                }
                return [];
            }
            
            $eventIocs = [];
            $skippedCount = 0;
            
            foreach ($indicators as $doc) {
                if (isset($doc['indicator'])) {
                    $hasScore = isset($doc['attribute_score']) && $doc['attribute_score'] !== null && $doc['attribute_score'] !== '' && $doc['attribute_score'] !== '0';
                    $hasSeverity = isset($doc['attribute_serverity']) && $doc['attribute_serverity'] !== null && $doc['attribute_serverity'] !== '' && $doc['attribute_serverity'] !== 'Informational';
                    
                    if ($hasScore || $hasSeverity) {
                        $skippedCount++;
                        continue;
                    }
                    
                    $val = $doc['indicator'];
                    $rawType = $doc['type'] ?? 'unknown';
                    
                    $indicatorId = $doc['indicator_id'] ?? (string)$doc['_id'];
                    $mongoId = (string)$doc['_id'];
                    
                    $type = 'unknown';
                    if (strpos($rawType, 'ip') !== false) $type = 'ip';
                    elseif (strpos($rawType, 'domain') !== false) $type = 'domain';
                    elseif (strpos($rawType, 'md5') !== false) $type = 'md5';
                    elseif (strpos($rawType, 'sha') !== false) $type = 'sha256';
                    elseif (strpos($rawType, 'url') !== false) $type = 'url';
                    else $type = $this->detectType($val);
                    
                    $eventIocs[] = [
                        'ioc' => $val, 
                        'type' => $type !== 'unknown' ? $type : $rawType,
                        'pulse_id' => $pulseId,
                        'event_name' => $eventName,
                        'event_tags' => is_array($eventTags) ? $eventTags : [],
                        'event_public' => $eventPublic,
                        'indicator_id' => $indicatorId,
                        'mongo_id' => $mongoId,
                        'event_date' => $eventDate->format('Y-m-d')
                    ];
                }
            }
            
            if ($skippedCount > 0) {
                $this->info("Skipped {$skippedCount} indicators (already have score/severity)");
            }
            
            $totalToProcess = count($eventIocs);
            
            // Update job with total count
            if ($jobsCollection && $jobId) {
                $jobsCollection->updateOne(
                    ['job_id' => $jobId],
                    ['$set' => [
                        'total_indicators' => $totalToProcess,
                        'remaining' => $totalToProcess,
                        'event_name' => $eventName,
                        'data_key' => $dataKey
                    ]]
                );
            }
            
            if (empty($eventIocs)) {
                $this->warn("No indicators to process (all already have score/severity)");
                if ($jobsCollection && $jobId) {
                    $jobsCollection->updateOne(
                        ['job_id' => $jobId],
                        ['$set' => ['status' => 'completed', 'message' => 'All indicators already enriched', 'completed_at' => new \MongoDB\BSON\UTCDateTime()]]
                    );
                }
                return [];
            }
            
            $this->info("Processing " . count($eventIocs) . " indicators...");
            
            // Process indicators with progress tracking
            $allIocs = [];
            $this->processEventIndicatorsWithProgress($eventIocs, $allIocs, $dataKey, $jobsCollection, $jobId);
            
            // Update record_count in fx_data_key
            $dataKeyCollection->updateOne(
                ['data_key' => $dataKey],
                ['$set' => ['record_count' => count($allIocs)]]
            );
            
            $this->info("Updated fx_data_key record_count: " . count($allIocs));
            
            // Mark job as completed
            if ($jobsCollection && $jobId) {
                $jobsCollection->updateOne(
                    ['job_id' => $jobId],
                    ['$set' => [
                        'status' => 'completed',
                        'processed_count' => count($allIocs),
                        'remaining' => 0,
                        'completed_at' => new \MongoDB\BSON\UTCDateTime(),
                        'message' => 'Successfully processed ' . count($allIocs) . ' indicators'
                    ]]
                );
            }
            
            return $allIocs;
            
        } catch (\Exception $e) {
            $this->error("Error processing single event: " . $e->getMessage());
            if ($jobsCollection && $jobId) {
                $jobsCollection->updateOne(
                    ['job_id' => $jobId],
                    ['$set' => ['status' => 'failed', 'error' => $e->getMessage(), 'failed_at' => new \MongoDB\BSON\UTCDateTime()]]
                );
            }
            return [];
        }
    }
    
    protected function processEventIndicators($eventIocs, &$allIocs, $dataKey)
    {
        if (empty($eventIocs)) return;
        
        $this->line("  Processing " . count($eventIocs) . " indicators...");
        
        // One client for all requests
        $client = new Client(['verify' => false, 'timeout' => 15]);
        $eventResults = [];
        $batchResults = [];
        
        // Create progress bar for this event
        $bar = $this->output->createProgressBar(count($eventIocs));
        $bar->setFormat('  %current%/%max% [%bar%] %percent:3s%%');
        $bar->start();
        
        // Create a generator for promises
        $promises = (function () use ($client, $eventIocs, $dataKey) {
            foreach ($eventIocs as $item) {
                yield $this->service->checkIocAsync($client, $item['ioc'], $item['type'], $item['event_name'] ?? null)
                    ->then(function ($result) use ($item, $dataKey) {
                        // REMOVED extra usleep here to speed up
                        return array_merge($result, [
                            'event_name' => $item['event_name'] ?? 'N/A',
                            'event_tags' => $item['event_tags'] ?? [],
                            'pulse_id' => $item['pulse_id'] ?? null,
                            'indicator_id' => $item['indicator_id'] ?? null,
                            'mongo_id' => $item['mongo_id'] ?? $item['indicator_id'] ?? null,
                            'event_public' => $item['event_public'] ?? 1,
                            'event_date' => $item['event_date'] ?? null,
                            'data_key' => $dataKey
                        ]);
                    });
            }
        })();
        
        // Process with concurrency
        $each = new EachPromise($promises, [
            'concurrency' => 1, // Sequential
            'fulfilled' => function ($result) use ($bar, &$eventResults, &$batchResults, &$allIocs) {
                $eventResults[] = $result;
                $batchResults[] = $result;
                $allIocs[] = $result;
                $bar->advance();
                
                // Delay 4 seconds (SAFE: 5 keys * 4s = 20s cycle > 15s limit)
                usleep(4000000); 

                // Save batch every 50 items (Safety for Resume)
                if (count($batchResults) >= 50) {
                     $this->saveAndSyncResults($batchResults);
                     array_splice($batchResults, 0); // Clear array
                }
            },
            'rejected' => function ($reason) {
                // Handle failure
            }
        ]);
        
        $each->promise()->wait();
        $bar->finish();
        $this->line("");
        
        // Save remaining batch
        if (!empty($batchResults)) {
            $this->saveAndSyncResults($batchResults);
        }
        
        // Show summary for this event
        $this->displayEventSummary($eventResults);
    }
    
    protected function processEventIndicatorsWithProgress($eventIocs, &$allIocs, $dataKey, $jobsCollection = null, $jobId = null)
    {
        if (empty($eventIocs)) return;
        
        $this->line("  Processing " . count($eventIocs) . " indicators with progress tracking...");
        
        // One client for all requests with shorter timeout
        $client = new Client(['verify' => false, 'timeout' => 30, 'connect_timeout' => 15, 'headers' => ['Connection' => 'keep-alive']]);
        $eventResults = [];
        $batchResults = [];
        $totalCount = count($eventIocs);
        $processedCount = 0;
        
        // Create progress bar for this event
        $bar = $this->output->createProgressBar($totalCount);
        $bar->setFormat('  %current%/%max% [%bar%] %percent:3s%%');
        $bar->start();
        
        // Create a generator for promises
        $promises = (function () use ($client, $eventIocs, $dataKey) {
            foreach ($eventIocs as $item) {
                yield $this->service->checkIocAsync($client, $item['ioc'], $item['type'], $item['event_name'] ?? null)
                    ->then(function ($result) use ($item, $dataKey) {
                        return array_merge($result, [
                            'event_name' => $item['event_name'] ?? 'N/A',
                            'event_tags' => $item['event_tags'] ?? [],
                            'pulse_id' => $item['pulse_id'] ?? null,
                            'indicator_id' => $item['indicator_id'] ?? null,
                            'mongo_id' => $item['mongo_id'] ?? $item['indicator_id'] ?? null,
                            'event_public' => $item['event_public'] ?? 1,
                            'event_date' => $item['event_date'] ?? null,
                            'data_key' => $dataKey
                        ]);
                    });
            }
        })();
        
        // Process with concurrency
        $each = new EachPromise($promises, [
            'concurrency' => 1, // Sequential to avoid VT rate limiting (4 req/min)
            'fulfilled' => function ($result) use ($bar, &$eventResults, &$batchResults, &$allIocs, &$processedCount, $totalCount, $jobsCollection, $jobId) {
                $eventResults[] = $result;
                $batchResults[] = $result;
                $allIocs[] = $result;
                $processedCount++;
                $bar->advance();
                
                // Delay 4 seconds between requests (5 keys * 4s = 20s cycle > 15s limit)
                usleep(4000000); // 4 seconds
                
                // Save batch every 50 items
                if (count($batchResults) >= 50) {
                     $this->saveAndSyncResults($batchResults);
                     array_splice($batchResults, 0); // Clear array without breaking reference
                }

                // Update job progress every 10 items
                if ($jobsCollection && $jobId && ($processedCount % 10 === 0)) {
                    $remaining = max(0, $totalCount - $processedCount);
                    $jobsCollection->updateOne(
                        ['job_id' => $jobId],
                        ['$set' => [
                            'processed_count' => $processedCount,
                            'remaining' => $remaining,
                            'last_updated' => new \MongoDB\BSON\UTCDateTime()
                        ]]
                    );
                }
            },
            'rejected' => function ($reason) use ($bar, &$processedCount) {
                $processedCount++;
                $bar->advance();
            }
        ]);
        
        $each->promise()->wait();
        $bar->finish();
        $this->line("");
        
        // Save remaining batch
        if (!empty($batchResults)) {
             $this->saveAndSyncResults($batchResults);
        }
        
        // Final update for job progress
        if ($jobsCollection && $jobId) {
            $jobsCollection->updateOne(
                ['job_id' => $jobId],
                ['$set' => [
                    'processed_count' => $processedCount,
                    'remaining' => 0,
                    'last_updated' => new \MongoDB\BSON\UTCDateTime()
                ]]
            );
        }

        // Show summary for this event
        $this->displayEventSummary($eventResults);
    }
    
    protected function saveAndSyncResults($results)
    {
        if (empty($results)) return;
        
        try {
            $DB_MONGO_KEY = env("DB_MONGO_STOREDATAB", "");
            $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
            $db = $clientMD->sosecure_threatintelligent_dev;
            $temp = $db->fx_indicators_temp;
            $ref = $db->fx_otx_events_indicator_ref;
            $dataKeyCollection = $db->fx_data_key;
            
            // Generate data_key for this batch
            $dataKey = (string) \Illuminate\Support\Str::uuid();
            $batchTimestamp = date('Y-m-d H:i:s');
            
            $opsTemp = [];
            $opsRef = [];
            
            foreach ($results as $result) {
                // ... (tag processing logic remains same) ...
                $tags = [];
                if (!empty($result['event_tags']) && is_array($result['event_tags'])) {
                    $tags = array_merge($tags, $result['event_tags']);
                }
                if (!empty($result['vt']['unique_results'])) {
                    $tags = array_merge($tags, $result['vt']['unique_results']);
                }
                if (!empty($result['rstcloud_threat'])) {
                    $tags = array_merge($tags, $result['rstcloud_threat']);
                }
                if (!empty($result['threatfox']['tags'])) {
                    $tags = array_merge($tags, $result['threatfox']['tags']);
                }
                $tags = array_unique($tags);
                
                $doc = [
                    'data_key' => $dataKey, // Use generated key
                    'imported_at' => $batchTimestamp,
                    'event_id' => $result['pulse_id'] ?? null,
                    'public' => $result['event_public'] ?? 1,
                    'event_tags' => implode(', ', $result['event_tags'] ?? []),
                    'attribute_id' => $result['indicator_id'] ?? null,
                    'attribute_type' => $result['type'] ?? 'unknown',
                    'attribute_name' => $result['ioc'] ?? '',
                    'attribute_score' => (string)($result['total_score'] ?? 0),
                    'attribute_serverity' => $result['risk_level'] ?? 'Informational',
                    'attribute_datetime' => date('Y-m-d H:i:s'),
                    'attribute_tags' => implode(', ', $tags),
                    // All API results in one field
                    'enrichment_value' => [
                        'vt' => $result['vt'] ?? [],
                        'abuse' => $result['abuse'] ?? [],
                        'otx' => $result['otx'] ?? [],
                        'threatfox' => $result['threatfox'] ?? [],
                        'rstcloud' => [
                            'score' => $result['rstcloud_score'] ?? null,
                            'threat' => $result['rstcloud_threat'] ?? []
                        ]
                    ],
                    'status' => 'pending',
                    'last_error' => null,
                    'processed_at' => null
                ];
                
                $opsTemp[] = ['updateOne' => [
                    ['attribute_id' => $result['indicator_id']],
                    ['$set' => $doc],
                    ['upsert' => true] // Note: Using upsert=true might overwrite data_key if existing doc found
                    // Ideally for "new import" logic, insertOne is used, but here we update existing logic
                ]];
                
                if (!empty($result['indicator_id']) && !empty($result['pulse_id'])) {
                    $filterRef = [
                        'indicator_id' => (string)$result['indicator_id'],
                        'pulse_id' => (string)$result['pulse_id']
                    ];
                    $updateRef = ['$set' => [
                        'attribute_score' => (string)($result['total_score'] ?? 0),
                        'attribute_serverity' => $result['risk_level'] ?? 'Informational',
                        'tags' => implode(', ', $tags),
                        'imported_at' => $batchTimestamp
                    ]];
                    $opsRef[] = ['updateMany' => [$filterRef, $updateRef, ['upsert' => true]]];
                }
            }
            
            if ($opsTemp) $temp->bulkWrite($opsTemp);
            if ($opsRef) $ref->bulkWrite($opsRef);
            
            // Log data_key
            $dataKeyCollection->insertOne([
                'date' => $batchTimestamp,
                'data_key' => $dataKey,
                'imported_at' => $batchTimestamp,
                'record_count' => count($results),
                'type' => 'attribute',
                'note' => 'batch_enrichment',
                'timestamp' => time()
            ]);
            
            $this->info("Saved & Synced batch of " . count($results) . " (Key: {$dataKey})");
            
        } catch (\Exception $e) {
            $this->error("Save/Sync Error: " . $e->getMessage());
            Log::error($e);
        }
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
            
            // Add ThreatFox tags
            if (!empty($res['threatfox']['tags'])) {
                $tags = array_merge($tags, $res['threatfox']['tags']);
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
