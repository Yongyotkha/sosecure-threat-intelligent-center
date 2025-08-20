<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHighAlertToWebdefacmentSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('webdefacment_setting', function (Blueprint $table) {
            $table->timestamp('high_alert_sent_at')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('webdefacment_setting', function (Blueprint $table) {
            $table->dropColumn('high_alert_sent_at');
        });
    }
}
