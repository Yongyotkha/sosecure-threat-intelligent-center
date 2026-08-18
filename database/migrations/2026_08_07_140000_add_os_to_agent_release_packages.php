<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddOsToAgentReleasePackages extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('agent_release_packages')) {
            return;
        }

        if (!Schema::hasColumn('agent_release_packages', 'os')) {
            Schema::table('agent_release_packages', function (Blueprint $table) {
                $table->string('os', 32)->default('windows')->after('kind');
            });
        }

        try {
            DB::table('agent_release_packages')
                ->where(function ($q) {
                    $q->whereNull('os')->orWhere('os', '');
                })
                ->update(['os' => 'windows']);
        } catch (\Exception $e) {
        }

        // Allow same version+kind for different OS (e.g. Windows + Ubuntu Setup 5.7.0).
        try {
            Schema::table('agent_release_packages', function (Blueprint $table) {
                $table->dropUnique('agent_release_packages_version_kind_uq');
            });
        } catch (\Exception $e) {
        }
        try {
            Schema::table('agent_release_packages', function (Blueprint $table) {
                $table->unique(['version', 'kind', 'os'], 'agent_release_packages_version_kind_os_uq');
            });
        } catch (\Exception $e) {
        }
        try {
            Schema::table('agent_release_packages', function (Blueprint $table) {
                $table->index('os', 'agent_release_packages_os');
            });
        } catch (\Exception $e) {
        }
    }

    public function down()
    {
        if (!Schema::hasTable('agent_release_packages') || !Schema::hasColumn('agent_release_packages', 'os')) {
            return;
        }
        try {
            Schema::table('agent_release_packages', function (Blueprint $table) {
                $table->dropUnique('agent_release_packages_version_kind_os_uq');
            });
        } catch (\Exception $e) {
        }
        try {
            Schema::table('agent_release_packages', function (Blueprint $table) {
                $table->dropIndex('agent_release_packages_os');
            });
        } catch (\Exception $e) {
        }
        try {
            Schema::table('agent_release_packages', function (Blueprint $table) {
                $table->unique(['version', 'kind'], 'agent_release_packages_version_kind_uq');
            });
        } catch (\Exception $e) {
        }
        Schema::table('agent_release_packages', function (Blueprint $table) {
            $table->dropColumn('os');
        });
    }
}
