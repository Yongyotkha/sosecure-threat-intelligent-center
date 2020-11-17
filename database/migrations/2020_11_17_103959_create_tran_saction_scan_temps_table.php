<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTranSactionScanTempsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tran_saction_scan_temps', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('site_id')->nullable();
            $table->integer('domain_id')->nullable();
            $table->tinyInteger('status')->default(0)->nullable();
            $table->string('module')->nullable();
            $table->string('data_type')->nullable();
            $table->string('referent')->nullable();
            $table->longText('raw_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tran_saction_scan_temps');
    }
}
