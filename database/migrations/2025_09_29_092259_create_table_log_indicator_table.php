<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableLogIndicatorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_log_indicator', function (Blueprint $table) {
            $table->increments('id');      // Primary Key
            $table->string('log');
            $table->string('type');
            $table->dateTime('date');
            $table->integer('status');
            $table->string('status_text');
            $table->timestamps();          // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('table_log_indicator');
        
    }
}
