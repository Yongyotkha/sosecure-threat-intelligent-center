<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Create ssdeep tables that already exist on production (sosecure_insight)
 * but were never added as CREATE migrations. Safe to re-run: skips existing tables.
 *
 * Laravel connection prefix fx_ is applied automatically.
 */
class CreateSsdeepAgentTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('ssdeep_file')) {
            Schema::create('ssdeep_file', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('version', 64);
                $table->string('path', 512);
                $table->string('file_name', 191)->nullable();
                $table->string('format', 32)->default('sqlite_zip');
                $table->string('sha256', 64)->nullable();
                $table->bigInteger('size_bytes')->nullable();
                $table->integer('signature_count')->default(0);
                $table->char('status', 1)->default('Y');
                $table->string('source', 16)->default('master');
                $table->char('auto_distribute', 1)->default('N');
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->string('title', 255)->nullable();
                $table->string('category', 64)->nullable();
                $table->text('description')->nullable();

                $table->index('version', 'ssdeep_file_version');
                $table->index('status', 'ssdeep_file_status');
            });
        }

        if (!Schema::hasTable('ssdeep_file_site')) {
            Schema::create('ssdeep_file_site', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('site_id');
                $table->unsignedBigInteger('ssdeep_file_id');
                $table->tinyInteger('transaction_download_client')->default(0);
                $table->char('status', 1)->default('Y');
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();

                $table->index(['site_id', 'ssdeep_file_id'], 'ssdeep_file_site_lookup');
            });
        }

        if (!Schema::hasTable('ssdeep_file_site_agent_download')) {
            Schema::create('ssdeep_file_site_agent_download', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('site_id');
                $table->bigInteger('agent_id');
                $table->unsignedBigInteger('ssdeep_file_id');
                $table->tinyInteger('transaction_download_client')->default(0);
                $table->string('version', 64)->nullable();
                $table->char('status', 1)->default('Y');
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();

                $table->index(
                    ['site_id', 'agent_id', 'ssdeep_file_id'],
                    'ssdeep_agent_dl_lookup'
                );
            });
        }

        if (!Schema::hasTable('ssdeep_candidate')) {
            Schema::create('ssdeep_candidate', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('site_id');
                $table->bigInteger('agent_id')->nullable();
                $table->string('ip_private', 64)->nullable();
                $table->text('path')->nullable();
                $table->string('file_name', 191)->nullable();
                $table->string('hash_md5', 64)->nullable();
                $table->string('hash_sha256', 64)->nullable();
                $table->text('ssdeep')->nullable();
                $table->string('engine', 32)->nullable();
                $table->string('rule', 191)->nullable();
                $table->integer('score')->default(0);
                $table->string('scan_mode', 64)->nullable();
                $table->dateTime('detected_at')->nullable();
                $table->string('source', 64)->nullable();
                $table->string('status', 32)->default('queued');
                $table->text('note')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();

                $table->index('site_id', 'ssdeep_candidate_site');
                $table->index('status', 'ssdeep_candidate_status');
            });
        }

        if (!Schema::hasTable('ssdeep_log')) {
            Schema::create('ssdeep_log', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('site_id');
                $table->bigInteger('agent_id');
                $table->text('path')->nullable();
                $table->string('file_name', 191)->nullable();
                $table->string('hash_md5', 64)->nullable();
                $table->text('ssdeep')->nullable();
                $table->string('engine', 32)->nullable();
                $table->string('rule', 191)->nullable();
                $table->integer('score')->default(0);
                $table->text('description')->nullable();
                $table->string('device_name', 191)->nullable();
                $table->dateTime('detected_at')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();

                $table->index(['site_id', 'agent_id'], 'ssdeep_log_site_agent');
                $table->index('detected_at', 'ssdeep_log_detected_at');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('ssdeep_log');
        Schema::dropIfExists('ssdeep_candidate');
        Schema::dropIfExists('ssdeep_file_site_agent_download');
        Schema::dropIfExists('ssdeep_file_site');
        Schema::dropIfExists('ssdeep_file');
    }
}
