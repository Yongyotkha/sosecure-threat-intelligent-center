<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSourceToDataDatacveTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('data_datacve', 'source')) {
            Schema::table('data_datacve', function (Blueprint $table) {
                $table->string('source', 50)->nullable()->after('severity');
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
        if (Schema::hasColumn('data_datacve', 'source')) {
            Schema::table('data_datacve', function (Blueprint $table) {
                $table->dropColumn('source');
            });
        }
    }
}
