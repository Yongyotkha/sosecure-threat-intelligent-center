<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReadTopicsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('read_topics', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code')->nullable();
            $table->integer('site_id')->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('topic_id')->nullable();
            $table->integer('news_id')->nullable();
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
        Schema::dropIfExists('read_topics');
    }
}
