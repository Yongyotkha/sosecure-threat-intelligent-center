<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWebdefacementSettingIdToStatLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('webdefacement_stat_log', function (Blueprint $table) {
            $table->unsignedBigInteger('webdefacement_setting_id')->nullable()->after('site_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('webdefacement_stat_log', function (Blueprint $table) {
            $table->dropColumn('webdefacement_setting_id');
        });
    }
}
