<?php

namespace App\Console\Commands;

use App\Services\WebDefacementService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WebDefacementStatDaily extends Command
{
    /**
     * The name and signature of the console command.
     *
     * php artisan app:webdefacement_stat_daily --date=YYYY-MM-DD
     */
    protected $signature = 'app:webdefacement_stat_daily {--date=}';

    /**
     * The console command description.
     */
    protected $description = 'สรุปข้อมูล web defacement รายวัน';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dateOption = $this->option('date');
        $targetDate = $dateOption
            ? Carbon::parse($dateOption)->toDateString()
            : now()->subDay()->toDateString();

        $this->info("🧩 เริ่มสรุปข้อมูลประจำวันที่ {$targetDate}");

        $severity = WebDefacementService::sqlSeverityCase('status');
        $maxStatus = WebDefacementService::sqlStatusFromSeverity($severity);

        // 1) Primary source: webdefacement_stat_log
        $stats = DB::table('webdefacement_stat_log')
            ->selectRaw("
                    webdefacement_setting_id AS webdefacement_id,
                    COUNT(*) AS scan_count,
                    SUM(CASE WHEN alert_sent = 1 THEN 1 ELSE 0 END) AS alert_count,
                    AVG(score) AS avg_score,
                    MAX(diff_percent) AS max_diff_percent,
                    {$maxStatus} AS max_status
                ")
            ->whereDate('created_at', $targetDate)
            ->whereNotNull('webdefacement_setting_id')
            ->groupBy('webdefacement_setting_id')
            ->get();

        // 2) Fallback: webdefacment_data_check
        if ($stats->isEmpty()) {
            $this->info('ไม่พบข้อมูลใน stat_log, ลองดึงจาก webdefacment_data_check...');

            $dcSeverity = WebDefacementService::sqlSeverityCase('status_code');
            $dcMaxStatus = WebDefacementService::sqlStatusFromSeverity($dcSeverity);

            $stats = DB::table('webdefacment_data_check')
                ->selectRaw("
                        webdefacment_setting_id AS webdefacement_id,
                        COUNT(*) AS scan_count,
                        SUM(CASE WHEN LOWER(status_code) IN ('high', 'medium') THEN 1 ELSE 0 END) AS alert_count,
                        AVG(percent_all) AS avg_score,
                        MAX(percent_all) AS max_diff_percent,
                        {$dcMaxStatus} AS max_status
                    ")
                ->whereDate('last_update', $targetDate)
                ->groupBy('webdefacment_setting_id')
                ->get();
        }

        if ($stats->isEmpty()) {
            $this->warn("ไม่มีข้อมูลในวันที่ {$targetDate}");
            return;
        }

        $insertCount = 0;

        foreach ($stats as $row) {
            $payload = [
                'scan_count' => (int) ($row->scan_count ?? 0),
                'alert_count' => (int) ($row->alert_count ?? 0),
                'avg_score' => round((float) ($row->avg_score ?? 0), 3),
                'max_diff_percent' => round((float) ($row->max_diff_percent ?? 0), 3),
                'max_status' => WebDefacementService::normalizeStatus($row->max_status ?? null),
                'updated_at' => now(),
            ];

            $existing = DB::table('webdefacement_stat_daily')
                ->where('date', $targetDate)
                ->where('webdefacement_id', $row->webdefacement_id)
                ->exists();

            if ($existing) {
                DB::table('webdefacement_stat_daily')
                    ->where('date', $targetDate)
                    ->where('webdefacement_id', $row->webdefacement_id)
                    ->update($payload);
            } else {
                DB::table('webdefacement_stat_daily')->insert(array_merge($payload, [
                    'date' => $targetDate,
                    'webdefacement_id' => $row->webdefacement_id,
                    'created_at' => now(),
                ]));
            }

            $insertCount++;
        }

        $this->info("✅ บันทึกสรุปข้อมูลสำเร็จ: {$insertCount} แถว สำหรับวันที่ {$targetDate}");
        Log::info("[WebDefacementStatDaily] สรุปข้อมูล {$insertCount} แถว สำหรับวันที่ {$targetDate}");
    }
}
