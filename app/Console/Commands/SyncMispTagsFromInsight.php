<?php

namespace App\Console\Commands;

use App\Services\MispTagService;
use Illuminate\Console\Command;
use MongoDB\Client as MongoClient;
use MongoDB\BSON\ObjectId;
use Carbon\Carbon;

class SyncMispTagsFromInsight extends Command
{
    protected $signature = 'misp:tags:sync-insight
                            {--db=sosecure_threatintelligent}
                            {--events=fx_events_temp}
                            {--indicators=fx_indicators_temp}
                            {--batch=1000}
                            {--batch-size=500}
                            {--window=6}                 # ย้อนหลังกี่ชั่วโมง
                            {--overlap-min=15}           # บัฟเฟอร์กันตกหล่น
                            {--max-total=50000}
                            {--sleep-ms=0}
                            {--only= : event|indicator}
                            {--checkpoint=insight_sync_state}
                            {--ignore-checkpoint} 
                            {--dry-run}';

    protected $description = 'Sync tags จาก temp โดยอิง imported_at (string) + กันตกหล่น + ใช้อินเด็กซ์';

    private $gcEvery = 500;

    public function handle(MispTagService $svc)
    {
        $mongo      = new MongoClient(config('app.DB_MONGO_DEV'));
        $db         = $mongo->{$this->option('db')};
        $evCol      = $db->{$this->option('events')};
        $inCol      = $db->{$this->option('indicators')};
        $cpCol      = $db->{$this->option('checkpoint')};

        $batch      = (int) $this->option('batch') ?: 1000;
        $batchSize  = (int) $this->option('batch-size') ?: 500;
        $windowH    = (int) $this->option('window') ?: 6;
        $overlapMin = (int) $this->option('overlap-min') ?: 15;
        $maxTotal   = (int) $this->option('max-total') ?: 50000;
        $sleepMs    = (int) $this->option('sleep-ms') ?: 0;
        $only       = $this->option('only');
        $dry        = (bool) $this->option('dry-run');

        // คำนวณ from โดยอ้าง last_run_at - overlap  แล้วฟอร์แมตเป็นสตริง 'Y-m-d H:i:s'
        $lastRunAt  = $this->loadCheckpointTime($cpCol);
        $fromBase   = $lastRunAt ? Carbon::parse($lastRunAt) : Carbon::now()->subHours($windowH);
        $from       = $fromBase->copy()->subMinutes($overlapMin);
        $fromStr    = $from->format('Y-m-d H:i:s'); // สำคัญ: ตรงกับฟอร์แมตใน DB

        $this->info("Start (window={$windowH}h, overlap={$overlapMin}m) using imported_at >= {$fromStr}" . ($dry ? ' [DRY]' : ''));

        $total = 0;
        $capped = false;

        if ($only === null || $only === 'event') {
            $total += $this->process($evCol, 'event', $svc, $fromStr, $batch, $batchSize, $sleepMs, $maxTotal - $total, $cpCol, $dry);
            if ($total >= $maxTotal) $capped = true;
        }
        if (!$capped && ($only === null || $only === 'indicator')) {
            $total += $this->process($inCol, 'indicator', $svc, $fromStr, $batch, $batchSize, $sleepMs, $maxTotal - $total, $cpCol, $dry);
        }

        if (!$dry && !$capped) {
            $this->saveCheckpointTime($cpCol, Carbon::now()->toDateTimeString());
        } else {
            $this->warn('Skip updating last_run_at (dry/capped) → รอบหน้าจะครอบช่วงเดิมซ้ำ');
        }
        $this->info("ONLY={$only} batch={$batch} batchSize={$batchSize} maxTotal={$maxTotal}");


        $this->info("Done. total={$total}" . ($dry ? ' [DRY]' : ''));
    }

    private function process(
        $col,
        $scope,
        MispTagService $svc,
        $fromStr,   // ใช้ค่าจาก handle() โดยตรง
        $batch,
        $batchSize,
        $sleepMs,
        $remainCap,
        $cpCol,
        $dry
    ) {
        $this->info("DEBUG current_time=" . now('Asia/Bangkok')->format('Y-m-d H:i:s'));
        $this->info("DEBUG using fromStr=" . $fromStr);

        if ($remainCap <= 0) return 0;


        $processed = 0;
        $round = 0;
        $ignoreCp = (bool) $this->option('ignore-checkpoint');
        $lastId = ($dry || $ignoreCp) ? null : $this->loadCheckpointLastId($cpCol, $scope);

        if (!$dry && !$ignoreCp && $lastId) {
            $this->saveCheckpointLastId($cpCol, $scope, $lastId);
        }


        // $lastId = $dry ? null : $this->loadCheckpointLastId($cpCol, $scope);

        // $lastId = $this->loadCheckpointLastId($cpCol, $scope);

        $base = [
            '$and' => [
                ['$or' => [
                    ['status' => ['$ne' => 'done']],
                    ['status' => ['$exists' => false]],
                ]],
                ['imported_at' => ['$gte' => $fromStr]],
            ]
        ];




        do {
            $round++;
            $filter = $base;

            if ($lastId) {
                $filter['$and'][] = ['_id' => ['$gt' => new \MongoDB\BSON\ObjectId($lastId)]];
            }

            $limitThis = min($batch, $remainCap - $processed);
            if ($limitThis <= 0) break;

            $this->line("== {$scope} batch#{$round} limit={$limitThis}");

            $projection = ($scope === 'event')
                ? ['event_id' => 1, 'event_tags' => 1]
                : ['event_id' => 1, 'attribute_id' => 1, 'attribute_tags' => 1];

            $cursor = $col->find(
                $filter,
                [
                    'projection'      => $projection,
                    'limit'           => $limitThis,
                    'batchSize'       => $batchSize,
                    'noCursorTimeout' => true,
                    'hint'            => 'status_importedAt__id',
                    'typeMap'         => ['root' => 'array', 'document' => 'array'],
                ]
            );

            $count = 0;
            foreach ($cursor as $doc) {
                $count++;
                $processed++;
                $lastId = (string) $doc['_id'];

                $this->handleOne($col, $scope, $doc, $svc, $dry);

                if ($sleepMs > 0) usleep($sleepMs * 1000);
                if ($processed % $this->gcEvery === 0) gc_collect_cycles();
                if ($processed >= $remainCap) break;
            }

            $this->saveCheckpointLastId($cpCol, $scope, $lastId);
        } while ($count > 0 && $processed < $remainCap);

        return $processed;
    }



    private function handleOne($col, $scope, array $doc, MispTagService $svc, $dry)
    {
        $id      = (string) $doc['_id'];
        $pulseId = isset($doc['event_id']) ? (string) $doc['event_id'] : '';

        if ($pulseId === '') {
            if (!$dry) $this->markFailed($col, $id, 'missing pulseId');
            return;
        }

        if ($scope === 'event') {
            $tags = isset($doc['event_tags']) ? (string) $doc['event_tags'] : '';
            if ($dry) {
                $this->line("DRY: event {$id} update('{$pulseId}','{$tags}')");
                return;
            }
            try {
                $ok = $svc->update($pulseId, $tags);
            } catch (\Exception $e) {
                $this->markFailed($col, $id, $e->getMessage());
                return;
            }
        } else {
            $attrId   = isset($doc['attribute_id']) ? $doc['attribute_id'] : null;
            $attrTags = isset($doc['attribute_tags']) ? (string) $doc['attribute_tags'] : '';
            if ($attrId === null || $attrId === '') {
                if (!$dry) $this->markFailed($col, $id, 'missing attribute_id');
                return;
            }
            if ($dry) {
                $this->line("DRY: ind {$id} updateFromIndicator('{$pulseId}','{$attrId}','{$attrTags}')");
                return;
            }
            try {
                $ok = $svc->updateFromIndicator($pulseId, $attrId, $attrTags);
            } catch (\Exception $e) {
                $this->markFailed($col, $id, $e->getMessage());
                return;
            }
        }

        isset($ok) && $ok ? $this->markDone($col, $id) : $this->markFailed($col, $id, 'service=false');
    }

    // --- checkpoint helpers ---
    private function saveCheckpointTime($cpCol, $time)
    {
        $cpCol->updateOne(['_id' => 'run_meta'], ['$set' => ['last_run_at' => $time]], ['upsert' => true]);
    }
    private function loadCheckpointTime($cpCol)
    {
        $d = $cpCol->findOne(['_id' => 'run_meta'], ['projection' => ['last_run_at' => 1]]);
        return ($d && isset($d['last_run_at'])) ? (string)$d['last_run_at'] : null;
    }
    private function saveCheckpointLastId($cpCol, $scope, $lastId)
    {
        if (!$lastId) return;
        $cpCol->updateOne(['_id' => 'last_id_' . $scope], ['$set' => ['value' => $lastId, 'updated_at' => Carbon::now()->toDateTimeString()]], ['upsert' => true]);
    }
    private function loadCheckpointLastId($cpCol, $scope)
    {
        $d = $cpCol->findOne(['_id' => 'last_id_' . $scope], ['projection' => ['value' => 1]]);
        return ($d && isset($d['value'])) ? (string)$d['value'] : null;
    }

    // --- mark result ---
    private function markDone($col, $id)
    {
        $col->updateOne(['_id' => new ObjectId($id)], [
            '$set' => ['status' => 'done', 'processed_at' => Carbon::now()->toDateTimeString(), 'last_error' => null]
        ]);
    }
    private function markFailed($col, $id, $reason)
    {
        $col->updateOne(['_id' => new ObjectId($id)], [
            '$set' => ['status' => 'failed', 'processed_at' => Carbon::now()->toDateTimeString(), 'last_error' => (string)$reason]
        ]);
    }
}
