<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSourceToDataCvevenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('data_cveven', 'source')) {
            Schema::table('data_cveven', function (Blueprint $table) {
                $table->string('source', 50)->nullable()->after('rawtext');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('data_cveven', 'source')) {
            Schema::table('data_cveven', function (Blueprint $table) {
                $table->dropColumn('source');
            });
        }
    }
}
