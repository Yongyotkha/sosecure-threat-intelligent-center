<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWebdefacementStatDailyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('webdefacement_stat_daily', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('site_id')->index();
            $table->date('date'); // วันที่ของข้อมูล (เช่น 2025-11-07)
            $table->integer('scan_count')->default(0); // จำนวนครั้งที่สแกนในวันนั้น
            $table->integer('alert_count')->default(0); // จำนวนครั้งที่เจอสถานะ medium/high

            $table->float('avg_score')->nullable(); // ค่าเฉลี่ย score ทั้งวัน
            $table->float('max_diff_percent')->nullable(); // ความแตกต่างสูงสุดของวันนั้น
            $table->enum('max_status', ['normal', 'medium', 'high'])->default('normal'); // สถานะสูงสุดของวันนั้น

            $table->timestamps();

            $table->unique(['site_id', 'date']); // ป้องกันข้อมูลซ้ำในวันเดียวกัน
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
