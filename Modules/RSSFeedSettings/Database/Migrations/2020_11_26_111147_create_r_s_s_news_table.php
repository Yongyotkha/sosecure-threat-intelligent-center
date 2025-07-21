<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRSSNewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('r_s_s_news', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('code')->index();
            $table->string('title_th');
            $table->longText('detail_th');
            $table->string('title_en')->nullable();
            $table->longText('detail_en')->nullable();
            $table->unsignedInteger('transaction_rss_id')->nullable();
            $table->integer('status')->default(0);
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
        Schema::dropIfExists('r_s_s_news');
    }
}
