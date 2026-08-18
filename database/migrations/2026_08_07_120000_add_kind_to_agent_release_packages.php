<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddKindToAgentReleasePackages extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('agent_release_packages')) {
            return;
        }
        if (!Schema::hasColumn('agent_release_packages', 'kind')) {
            Schema::table('agent_release_packages', function (Blueprint $table) {
                $table->string('kind', 32)->default('agent_binary')->after('version');
            });
        }

        // Classify existing rows by filename (Setup vs agent binary).
        try {
            DB::table('agent_release_packages')
                ->where(function ($q) {
                    $q->where('file_name', 'like', '%setup%')
                        ->orWhere('file_name', 'like', '%Setup%')
                        ->orWhere('file_name', 'like', '%installer%');
                })
                ->update(['kind' => 'installer']);
            DB::table('agent_release_packages')
                ->where(function ($q) {
                    $q->whereNull('kind')->orWhere('kind', '');
                })
                ->update(['kind' => 'agent_binary']);
        } catch (\Exception $e) {
            // ignore backfill errors
        }

        // Allow same semver for installer + OTA binary.
        try {
            Schema::table('agent_release_packages', function (Blueprint $table) {
                $table->dropUnique('agent_release_packages_version_uq');
            });
        } catch (\Exception $e) {
            // index may already be gone
        }
        try {
            Schema::table('agent_release_packages', function (Blueprint $table) {
                $table->unique(['version', 'kind'], 'agent_release_packages_version_kind_uq');
            });
        } catch (\Exception $e) {
            // composite may already exist
        }
    }

    public function down()
    {
        if (!Schema::hasTable('agent_release_packages') || !Schema::hasColumn('agent_release_packages', 'kind')) {
            return;
        }
        try {
            Schema::table('agent_release_packages', function (Blueprint $table) {
                $table->dropUnique('agent_release_packages_version_kind_uq');
            });
        } catch (\Exception $e) {
        }
        Schema::table('agent_release_packages', function (Blueprint $table) {
            $table->dropColumn('kind');
        });
    }
}
