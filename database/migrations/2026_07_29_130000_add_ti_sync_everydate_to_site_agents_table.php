<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTiSyncEverydateToSiteAgentsTable extends Migration
{
    /**
     * Daily HH:mm schedule for rules + ssdeep sync (mirrored via getConfig / updateConfig).
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('site_agents')) {
            return;
        }

        Schema::table('site_agents', function (Blueprint $table) {
            if (!Schema::hasColumn('site_agents', 'ti_sync_everydate')) {
                $table->string('ti_sync_everydate', 5)->default('03:00');
            }
        });
    }

    /**
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('site_agents')) {
            return;
        }

        Schema::table('site_agents', function (Blueprint $table) {
            if (Schema::hasColumn('site_agents', 'ti_sync_everydate')) {
                $table->dropColumn('ti_sync_everydate');
            }
        });
    }
}
