<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWebdefacementIdToWebdefacementStatDailyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('webdefacement_stat_daily', function (Blueprint $table) {
            if (!Schema::hasColumn('webdefacement_stat_daily', 'webdefacement_id')) {
                $table->unsignedBigInteger('webdefacement_id')
                    ->nullable()
                    ->after('id')
                    ->index()
                    ->comment('อ้างอิงถึงเว็บที่ตรวจ');
            }
            $table->unique(['date', 'webdefacement_id'], 'ux_date_webdefacement');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('webdefacement_stat_daily', function (Blueprint $table) {
            if (Schema::hasColumn('webdefacement_stat_daily', 'webdefacement_id')) {
                $table->dropColumn('webdefacement_id');
            }
            $table->dropUnique('ux_date_webdefacement');
        });
    }
}
