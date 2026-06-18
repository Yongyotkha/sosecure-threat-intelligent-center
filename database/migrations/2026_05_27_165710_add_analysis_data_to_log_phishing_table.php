<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAnalysisDataToLogPhishingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('log_phishing', function (Blueprint $table) {
            $table->longText('analysis_data')->nullable()->after('url_is_work');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('log_phishing', function (Blueprint $table) {
            $table->dropColumn('analysis_data');
        });
    }
}
