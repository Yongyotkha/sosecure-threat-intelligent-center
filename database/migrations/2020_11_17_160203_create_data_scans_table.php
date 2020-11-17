<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDataScansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data_scans', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code')->nullable();
            $table->string('data_type')->nullable();
            $table->integer('total')->nullable();
            $table->integer('site_id')->nullable();
            $table->integer('domain_id')->nullable();
            $table->tinyInteger('status')->default(1)->nullable();
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
        Schema::dropIfExists('data_scans');
    }
}
