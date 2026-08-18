<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAgentScanLogRunId extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('agent_scan_log')) {
            return;
        }
        if (!Schema::hasColumn('agent_scan_log', 'run_id')) {
            Schema::table('agent_scan_log', function (Blueprint $table) {
                $table->string('run_id', 64)->nullable()->after('mode')->index();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('agent_scan_log') && Schema::hasColumn('agent_scan_log', 'run_id')) {
            Schema::table('agent_scan_log', function (Blueprint $table) {
                $table->dropColumn('run_id');
            });
        }
    }
}
