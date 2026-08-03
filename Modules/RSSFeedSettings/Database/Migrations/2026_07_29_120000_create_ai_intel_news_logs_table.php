<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAiIntelNewsLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ai_intel_news_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('code')->index();
            $table->string('title')->nullable();
            $table->string('source')->nullable();
            $table->string('source_url', 1000)->nullable();
            $table->string('category')->nullable();
            $table->longText('executive_summary')->nullable();
            $table->longText('intelligence_context')->nullable();
            $table->longText('indicators_json')->nullable();
            $table->longText('vulnerabilities_json')->nullable();
            $table->dateTime('published_at')->nullable();
            $table->string('status', 32)->default('new')->index(); // new | reviewed | promoted | dismissed
            $table->unsignedInteger('transaction_rss_id')->nullable()->index();
            $table->unsignedInteger('rss_news_id')->nullable()->index();
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
        Schema::dropIfExists('ai_intel_news_logs');
    }
}
