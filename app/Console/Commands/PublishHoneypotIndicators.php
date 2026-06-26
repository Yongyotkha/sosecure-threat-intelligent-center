<?php

namespace App\Console\Commands;

use App\Services\HoneypotIndicatorPublishService;
use App\Services\HoneypotMongoService;
use Illuminate\Console\Command;

class PublishHoneypotIndicators extends Command
{
    protected $signature = 'honeypot:publish-indicators
                            {date? : Report date in Y-m-d (default: yesterday, Asia/Bangkok)}
                            {--force : Re-publish and replace indicators for that day}';

    protected $description = 'Publish daily honeypot attacker IPs as indicator event (1 event per day, no site disclosure)';

    public function handle(HoneypotIndicatorPublishService $publisher, HoneypotMongoService $mongo): int
    {
        $timezone = new \DateTimeZone('Asia/Bangkok');
        $date = $this->argument('date');

        if ($date === null || $date === '') {
            $date = (new \DateTimeImmutable('now', $timezone))->modify('-1 day')->format('Y-m-d');
        }

        $this->line('Honeypot indicator publish');
        $this->line('Target : ' . $mongo->getTarget());
        $this->line('DB     : ' . $mongo->getDatabaseName());
        $this->line('Date   : ' . $date);
        $this->line('Force: ' . ($this->option('force') ? 'yes' : 'no'));

        try {
            $result = $publisher->publishForDate($date, (bool) $this->option('force'));
        } catch (\Throwable $e) {
            $this->error('Publish failed: ' . $e->getMessage());

            return 1;
        }

        if (empty($result['published'])) {
            $reason = (string) ($result['reason'] ?? 'unknown');
            if ($reason === 'already_exists') {
                $this->warn('Event already exists for ' . $result['pulse_id'] . '. Use --force to re-publish.');
            } elseif ($reason === 'no_alerts') {
                $this->warn('No honeypot alerts found for ' . $date . '. Nothing published.');
            } else {
                $this->warn('Nothing published (' . $reason . ').');
            }

            return 0;
        }

        $this->info('Published ' . $result['pulse_id']);
        $this->line('Indicators: ' . (int) $result['indicator_count']);

        if (!empty($result['industries'])) {
            $this->line('Industries : ' . implode(', ', $result['industries']));
        } else {
            $this->line('Industries : (none mapped — check site categories)');
        }

        if (!empty($result['type_counts'])) {
            $parts = [];
            foreach ($result['type_counts'] as $type => $count) {
                $parts[] = $type . '=' . $count;
            }
            $this->line('Types      : ' . implode(', ', $parts));
        }

        return 0;
    }
}
