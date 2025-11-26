<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDatacveIdToDataCveSourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('data_cve_sources', function (Blueprint $table) {
            // เพิ่มแค่คอลัมน์ ไม่มี foreign key
            $table->unsignedBigInteger('datacve_id')->nullable()->after('source');

            // เพิ่ม index เพื่อความเร็ว
            $table->index('datacve_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('data_cve_sources', function (Blueprint $table) {
            $table->dropIndex(['datacve_id']);
            $table->dropColumn('datacve_id');
        });
    }
}
