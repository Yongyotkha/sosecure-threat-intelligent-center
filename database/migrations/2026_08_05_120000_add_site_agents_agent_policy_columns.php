<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Persist Control Agent / getConfig policy fields on site_agents.
 * Without these columns, Schema::hasColumn skips save and getConfig falls back
 * to code defaults (auto_scan looked "off" in UI but never stored).
 */
class AddSiteAgentsAgentPolicyColumns extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('site_agents')) {
            return;
        }

        Schema::table('site_agents', function (Blueprint $table) {
            if (!Schema::hasColumn('site_agents', 'ssdeep_enabled')) {
                $table->char('ssdeep_enabled', 1)->default('Y');
            }
            if (!Schema::hasColumn('site_agents', 'ssdeep_threshold')) {
                $table->unsignedSmallInteger('ssdeep_threshold')->default(85);
            }
            if (!Schema::hasColumn('site_agents', 'ssdeep_report_api')) {
                $table->char('ssdeep_report_api', 1)->default('Y');
            }
            if (!Schema::hasColumn('site_agents', 'quarantine_on_detect')) {
                $table->char('quarantine_on_detect', 1)->default('Y');
            }
            if (!Schema::hasColumn('site_agents', 'send_ssdeep_candidate')) {
                $table->char('send_ssdeep_candidate', 1)->default('N');
            }
            if (!Schema::hasColumn('site_agents', 'auto_scan_on_login')) {
                $table->char('auto_scan_on_login', 1)->default('N');
            }
            if (!Schema::hasColumn('site_agents', 'exclusion_paths')) {
                $table->text('exclusion_paths')->nullable();
            }
            if (!Schema::hasColumn('site_agents', 'scan_extensions')) {
                $table->text('scan_extensions')->nullable();
            }
            if (!Schema::hasColumn('site_agents', 'quick_scan_paths')) {
                $table->text('quick_scan_paths')->nullable();
            }
            if (!Schema::hasColumn('site_agents', 'log_level')) {
                $table->string('log_level', 16)->default('info');
            }
            if (!Schema::hasColumn('site_agents', 'cache_expiry_hours')) {
                $table->unsignedInteger('cache_expiry_hours')->default(168);
            }
            if (!Schema::hasColumn('site_agents', 'config_updated_at')) {
                $table->dateTime('config_updated_at')->nullable();
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('site_agents')) {
            return;
        }

        $cols = [
            'ssdeep_enabled', 'ssdeep_threshold', 'ssdeep_report_api',
            'quarantine_on_detect', 'send_ssdeep_candidate', 'auto_scan_on_login',
            'exclusion_paths', 'scan_extensions', 'quick_scan_paths',
            'log_level', 'cache_expiry_hours', 'config_updated_at',
        ];
        Schema::table('site_agents', function (Blueprint $table) use ($cols) {
            foreach ($cols as $col) {
                if (Schema::hasColumn('site_agents', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
