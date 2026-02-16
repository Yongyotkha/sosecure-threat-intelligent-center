<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPublishedModifiedToTransactionScansCveTempTable extends Migration
{
    public function up()
    {
        Schema::table('transaction_scans_cve_temp', function (Blueprint $table) {
            $table->string('published')->nullable()->after('description');
            $table->string('modified')->nullable()->after('published');
        });
    }

    public function down()
    {
        Schema::table('transaction_scans_cve_temp', function (Blueprint $table) {
            $table->dropColumn(['published', 'modified']);
        });
    }
}
