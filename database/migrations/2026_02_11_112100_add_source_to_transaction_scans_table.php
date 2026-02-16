<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSourceToTransactionScansTable extends Migration
{
    public function up()
    {
        Schema::table('transaction_scans', function (Blueprint $table) {
            $table->string('source')->nullable()->after('raw_data');
        });
    }

    public function down()
    {
        Schema::table('transaction_scans', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
}
