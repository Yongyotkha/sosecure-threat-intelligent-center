<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCveAssetsProgressTableAddLastCve extends Migration
{
    public function up(): void
    {
        Schema::table('cve_assets_progress', function (Blueprint $table) {
            $table->string('last_cve_name', 255)->nullable()->after('last_asset_id')
                  ->comment('ชื่อ CVE ล่าสุดที่ประมวลผลถึง');
        });
    }

    public function down(): void
    {
        Schema::table('cve_assets_progress', function (Blueprint $table) {
            $table->dropColumn('last_cve_name');
        });
    }
}

