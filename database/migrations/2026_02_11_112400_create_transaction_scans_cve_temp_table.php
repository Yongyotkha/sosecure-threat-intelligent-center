<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionScansCveTempTable extends Migration
{
    public function up()
    {
        Schema::create('transaction_scans_cve_temp', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code')->nullable();
            $table->integer('site_id')->nullable();
            $table->integer('domain_id')->nullable();
            $table->string('namecve', 50)->nullable()->index();
            $table->string('severity', 20)->nullable();
            $table->float('cvss_score')->nullable();
            $table->text('description')->nullable();
            $table->string('target')->nullable();
            $table->string('affected_cpe')->nullable();
            $table->string('source', 50)->nullable();
            $table->tinyInteger('is_mapped')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('transaction_scans_cve_temp');
    }
}
