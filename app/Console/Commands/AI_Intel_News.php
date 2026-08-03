<?php

namespace App\Console\Commands;

use App\Services\AiIntelNewsService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AI_Intel_News extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:AI_Intel_News
                            {--limit=1 : Max items per feed / CISA KEV}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch cyber news (RSS + CISA KEV), analyze with OpenAI, store in ai_intel_news_logs';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $limit = max(1, (int) $this->option('limit'));

        try {
            $this->info('Starting AI Intel sync (PHP only)...');
            $service = new AiIntelNewsService();
            $stats = $service->sync($limit, function ($level, $message) {
                if ($level === 'error') {
                    $this->error($message);
                } elseif ($level === 'warn') {
                    $this->warn($message);
                } else {
                    $this->line($message);
                }
            });

            $this->info(sprintf(
                'Done. created=%d updated=%d skipped=%d failed=%d',
                $stats['created'],
                $stats['updated'],
                $stats['skipped'],
                $stats['failed']
            ));

            return ($stats['failed'] > 0 && $stats['created'] === 0 && $stats['updated'] === 0) ? 1 : 0;
        } catch (Exception $e) {
            Log::error('app:AI_Intel_News failed: ' . $e->getMessage());
            $this->error($e->getMessage());
            return 1;
        }
    }
}
