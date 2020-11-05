<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionScansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_scans', function (Blueprint $table) {
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
        Schema::dropIfExists('transaction_scans');
    }
}
