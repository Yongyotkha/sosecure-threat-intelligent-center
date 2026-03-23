<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCpeFieldsToTransactionScansCveTempTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transaction_scans_cve_temp', function (Blueprint $table) {
            $table->string('cpe_code')->nullable()->after('affected_cpe');
            $table->string('cpe_uri')->nullable()->after('cpe_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transaction_scans_cve_temp', function (Blueprint $table) {
            $table->dropColumn(['cpe_code', 'cpe_uri']);
        });
    }
}
