<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUpdatedAtToDataCveven extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('data_cveven', function (Blueprint $table) {
            if (!Schema::hasColumn('data_cveven', 'updated_at')) {
                $table->timestamp('updated_at')->nullable()->after('created_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('data_cveven', function (Blueprint $table) {
            if (Schema::hasColumn('data_cveven', 'updated_at')) {
                $table->dropColumn('updated_at');
            }
        });
    }
}
