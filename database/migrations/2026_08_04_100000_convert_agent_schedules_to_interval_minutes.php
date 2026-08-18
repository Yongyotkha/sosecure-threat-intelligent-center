<?php

use App\Support\AgentScheduleInterval;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ConvertAgentSchedulesToIntervalMinutes extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('site_agents')) {
            return;
        }

        $widen = function ($col, $default) {
            if (!Schema::hasColumn('site_agents', $col)) {
                Schema::table('site_agents', function (Blueprint $table) use ($col, $default) {
                    $table->string($col, 16)->default((string) $default)->nullable(false);
                });
                return;
            }
            try {
                Schema::table('site_agents', function (Blueprint $table) use ($col, $default) {
                    $table->string($col, 16)->default((string) $default)->change();
                });
            } catch (\Exception $e) {
                // change() may need doctrine/dbal; skip widen if unavailable.
            }
        };

        $widen('batchjob_everydate', AgentScheduleInterval::DEFAULT_BATCH);
        $widen('ti_sync_everydate', AgentScheduleInterval::DEFAULT_TI_SYNC);
        $widen('agent_update_schedule', AgentScheduleInterval::DEFAULT_AGENT_UPDATE);

        $this->normalizeColumn('batchjob_everydate', AgentScheduleInterval::DEFAULT_BATCH);
        $this->normalizeColumn('ti_sync_everydate', AgentScheduleInterval::DEFAULT_TI_SYNC);
        $this->normalizeColumn('agent_update_schedule', AgentScheduleInterval::DEFAULT_AGENT_UPDATE);
    }

    protected function normalizeColumn($col, $default)
    {
        if (!Schema::hasColumn('site_agents', $col)) {
            return;
        }
        $rows = DB::table('site_agents')->select('id', $col)->get();
        foreach ($rows as $row) {
            $raw = isset($row->{$col}) ? $row->{$col} : null;
            $norm = AgentScheduleInterval::normalize($raw, $default);
            if ((string) $raw !== $norm) {
                DB::table('site_agents')->where('id', $row->id)->update([$col => $norm]);
            }
        }
    }

    public function down()
    {
        // Irreversible semantic change (minutes vs HH:mm); leave columns as-is.
    }
}
