<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RecreateWebdefacementStatDailyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('webdefacement_stat_daily');

        // ✅ สร้างตารางใหม่ (type ถูกต้อง)
        Schema::create('webdefacement_stat_daily', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('webdefacement_id')->nullable()->index();
            $table->unsignedBigInteger('site_id')->nullable()->index();
            $table->date('date')->index();
            $table->integer('scan_count')->default(0);
            $table->integer('alert_count')->default(0);
            $table->decimal('avg_score', 8, 3)->default(0);
            $table->decimal('max_diff_percent', 8, 3)->default(0);
            $table->string('max_status', 50)->nullable();
            $table->timestamps(); 
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
        Schema::dropIfExists('webdefacement_stat_daily');
    }
}
