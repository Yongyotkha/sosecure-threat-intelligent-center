<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateCveAssetsProgressTable extends Migration
{
    public function up(): void
    {
        Schema::create('cve_assets_progress', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('last_asset_id')->default(0)->comment('ID ล่าสุดของ cve_assets ที่ประมวลผลถึง');
            $table->timestamps();
        });

        // เพิ่มข้อมูลเริ่มต้น
        DB::table('cve_assets_progress')->insert([
            'last_asset_id' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('cve_assets_progress');
    }
}
