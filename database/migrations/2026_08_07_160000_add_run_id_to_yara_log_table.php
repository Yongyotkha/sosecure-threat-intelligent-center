<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRunIdToYaraLogTable extends Migration
{
    /**
     * Link detections to a scan history run (agent_scan_log.run_id).
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yara_log')) {
            return;
        }
        if (!Schema::hasColumn('yara_log', 'run_id')) {
            Schema::table('yara_log', function (Blueprint $table) {
                $table->string('run_id', 64)->nullable()->after('agent_id');
                $table->index(['run_id', 'agent_id'], 'yara_log_run_agent');
            });
        }
    }

    /**
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('yara_log') || !Schema::hasColumn('yara_log', 'run_id')) {
            return;
        }
        Schema::table('yara_log', function (Blueprint $table) {
            $table->dropIndex('yara_log_run_agent');
            $table->dropColumn('run_id');
        });
    }
}
