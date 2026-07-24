<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class WidenApiTokensTokenColumn extends Migration
{
    protected function apiTokensTable()
    {
        return Schema::getConnection()->getTablePrefix() . 'api_tokens';
    }

    public function up()
    {
        if (!Schema::hasTable('api_tokens') || !Schema::hasColumn('api_tokens', 'token')) {
            return;
        }

        $table = $this->apiTokensTable();

        // AbuseIPDB and other provider keys exceed the original VARCHAR(64) limit.
        try {
            Schema::table('api_tokens', function (Blueprint $blueprint) {
                $blueprint->dropUnique(['token']);
            });
        } catch (\Exception $e) {
            // Index may already be absent on some environments.
        }

        DB::statement("ALTER TABLE `{$table}` MODIFY token VARCHAR(512) NOT NULL");
    }

    public function down()
    {
        if (!Schema::hasTable('api_tokens') || !Schema::hasColumn('api_tokens', 'token')) {
            return;
        }

        $table = $this->apiTokensTable();

        DB::statement("ALTER TABLE `{$table}` MODIFY token VARCHAR(64) NOT NULL");

        try {
            Schema::table('api_tokens', function (Blueprint $table) {
                $table->unique('token');
            });
        } catch (\Exception $e) {
            // Ignore if unique index cannot be restored.
        }
    }
}
