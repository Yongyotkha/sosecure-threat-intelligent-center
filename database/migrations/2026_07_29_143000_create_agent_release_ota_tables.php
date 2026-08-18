<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAgentReleaseOtaTables extends Migration
{
    /**
     * OTA agent binary releases + site targets + per-agent update history.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('agent_release_packages')) {
            Schema::create('agent_release_packages', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('version', 64);
                $table->string('file_name')->nullable();
                $table->string('path', 512);
                $table->string('sha256', 64)->nullable();
                $table->bigInteger('size_bytes')->nullable();
                $table->char('status', 1)->default('Y');
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique('version', 'agent_release_packages_version_uq');
                $table->index('status', 'agent_release_packages_status');
            });
        }

        if (!Schema::hasTable('agent_release_targets')) {
            Schema::create('agent_release_targets', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('site_id')->nullable(); // null = global default
                $table->unsignedBigInteger('package_id');
                $table->string('target_version', 64);
                $table->char('status', 1)->default('Y');
                $table->timestamps();

                $table->index('site_id', 'agent_release_targets_site');
                $table->index('package_id', 'agent_release_targets_pkg');
            });
        }

        if (!Schema::hasTable('agent_release_events')) {
            Schema::create('agent_release_events', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('site_id')->nullable();
                $table->unsignedBigInteger('agent_id')->nullable();
                $table->string('ip_private', 64)->nullable();
                $table->string('current_version', 64)->nullable();
                $table->string('target_version', 64)->nullable();
                $table->string('status', 32)->default('checking');
                $table->text('message')->nullable();
                $table->dateTime('started_at')->nullable();
                $table->dateTime('finished_at')->nullable();
                $table->timestamps();

                $table->index('site_id', 'agent_release_events_site');
                $table->index('agent_id', 'agent_release_events_agent');
                $table->index('status', 'agent_release_events_status');
            });
        }

        if (Schema::hasTable('site_agents')) {
            Schema::table('site_agents', function (Blueprint $table) {
                if (!Schema::hasColumn('site_agents', 'agent_version_current')) {
                    $table->string('agent_version_current', 64)->nullable();
                }
                if (!Schema::hasColumn('site_agents', 'agent_version_target')) {
                    $table->string('agent_version_target', 64)->nullable();
                }
                if (!Schema::hasColumn('site_agents', 'agent_update_status')) {
                    $table->string('agent_update_status', 32)->nullable();
                }
                if (!Schema::hasColumn('site_agents', 'agent_update_checked_at')) {
                    $table->dateTime('agent_update_checked_at')->nullable();
                }
                if (!Schema::hasColumn('site_agents', 'agent_update_applied_at')) {
                    $table->dateTime('agent_update_applied_at')->nullable();
                }
                if (!Schema::hasColumn('site_agents', 'agent_update_schedule')) {
                    $table->string('agent_update_schedule', 5)->default('04:00');
                }
            });
        }
    }

    /**
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('site_agents')) {
            Schema::table('site_agents', function (Blueprint $table) {
                $cols = [];
                foreach ([
                    'agent_version_current', 'agent_version_target', 'agent_update_status',
                    'agent_update_checked_at', 'agent_update_applied_at', 'agent_update_schedule',
                ] as $col) {
                    if (Schema::hasColumn('site_agents', $col)) {
                        $cols[] = $col;
                    }
                }
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }

        Schema::dropIfExists('agent_release_events');
        Schema::dropIfExists('agent_release_targets');
        Schema::dropIfExists('agent_release_packages');
    }
}
