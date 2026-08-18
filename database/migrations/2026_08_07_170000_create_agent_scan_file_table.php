<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAgentScanFileTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('agent_scan_file')) {
            return;
        }
        Schema::create('agent_scan_file', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('site_id')->nullable()->index();
            $table->unsignedBigInteger('agent_id')->nullable()->index();
            $table->string('run_id', 64)->nullable();
            $table->text('path');
            $table->string('result', 16)->default('clean'); // clean|infected
            $table->string('rule', 255)->nullable();
            $table->string('engine', 32)->nullable();
            $table->decimal('score', 10, 2)->nullable();
            $table->dateTime('scanned_at')->nullable();
            $table->timestamps();

            $table->index(['run_id', 'agent_id'], 'agent_scan_file_run_agent');
            $table->index(['agent_id', 'scanned_at'], 'agent_scan_file_agent_time');
        });
    }

    public function down()
    {
        Schema::dropIfExists('agent_scan_file');
    }
}
