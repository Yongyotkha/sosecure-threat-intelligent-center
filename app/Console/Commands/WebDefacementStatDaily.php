<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WebDefacementStatDaily extends Command
{
    /**
     * The name and signature of the console command.
     *
     * php artisan webdefacement:stat-daily --date=YYYY-MM-DD
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

        // 1) ลองดึงจาก webdefacement_stat_log ก่อน (แหล่งหลัก)
        $stats = DB::table('webdefacement_stat_log')
            ->selectRaw('
                    webdefacement_setting_id AS webdefacement_id,
                    COUNT(*) AS scan_count,
                    SUM(CASE WHEN score > 0 THEN 1 ELSE 0 END) AS alert_count,
                    AVG(score) AS avg_score,
                    MAX(diff_percent) AS max_diff_percent,
                    MAX(status) AS max_status
                ')
            ->whereDate('created_at', $targetDate)
            ->groupBy('webdefacement_setting_id')
            ->get();

        // 2) Fallback: ถ้าไม่มีใน stat_log ให้ดึงจาก webdefacment_data_check แทน
        if ($stats->isEmpty()) {
            $this->info("ไม่พบข้อมูลใน stat_log, ลองดึงจาก webdefacment_data_check...");
            
            $stats = DB::table('webdefacment_data_check')
                ->selectRaw('
                        webdefacment_setting_id AS webdefacement_id,
                        COUNT(*) AS scan_count,
                        SUM(CASE WHEN percent_all > 0 THEN 1 ELSE 0 END) AS alert_count,
                        AVG(percent_all) AS avg_score,
                        MAX(percent_all) AS max_diff_percent,
                        MAX(status_code) AS max_status
                    ')
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
            DB::table('webdefacement_stat_daily')->updateOrInsert(
                [
                    'date' => $targetDate,
                    'webdefacement_id' => $row->webdefacement_id,
                ],
                [
                    'scan_count' => $row->scan_count ?? 0,
                    'alert_count' => 0,
                    'avg_score' => round($row->avg_score, 3),
                    'max_diff_percent' => round($row->max_diff_percent, 3),
                    'max_status' => $row->max_status ?? null,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            $insertCount++;
        }

        $this->info("✅ บันทึกสรุปข้อมูลสำเร็จ: {$insertCount} แถว สำหรับวันที่ {$targetDate}");
        Log::info("[WebDefacementStatDaily] สรุปข้อมูล {$insertCount} แถว สำหรับวันที่ {$targetDate}");
    }
}
