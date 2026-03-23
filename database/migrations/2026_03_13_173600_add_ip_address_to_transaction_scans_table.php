<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIpAddressToTransactionScansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('transaction_scans', 'ip_address')) {
            Schema::table('transaction_scans', function (Blueprint $table) {
                $table->string('ip_address')->nullable()->after('referent');
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
        Schema::table('transaction_scans', function (Blueprint $table) {
            $table->dropColumn('ip_address');
        });
    }
}
