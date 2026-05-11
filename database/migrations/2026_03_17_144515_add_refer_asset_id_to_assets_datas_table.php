<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReferAssetIdToAssetsDatasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assets_datas', function (Blueprint $table) {
            $table->unsignedBigInteger('refer_asset_id')->nullable()->after('asset_id');
            $table->index('refer_asset_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('assets_datas', function (Blueprint $table) {
            $table->dropIndex(['refer_asset_id']);
            $table->dropColumn('refer_asset_id');
        });
    }
}
