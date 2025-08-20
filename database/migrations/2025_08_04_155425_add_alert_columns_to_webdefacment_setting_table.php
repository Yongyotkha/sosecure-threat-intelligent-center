<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAlertColumnsToWebdefacmentSettingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('webdefacment_setting', function (Blueprint $table) {
            $table->boolean('is_alert_sent')->default(false)->index();
            $table->timestamp('alert_sent_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('webdefacment_setting', function (Blueprint $table) {
            $table->dropColumn('alert_sent_at');
            $table->dropColumn('is_alert_sent');
        });
    }
}
