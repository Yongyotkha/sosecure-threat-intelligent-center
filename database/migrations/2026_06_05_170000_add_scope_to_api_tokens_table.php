<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddScopeToApiTokensTable extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('api_tokens', 'scope')) {
            Schema::table('api_tokens', function (Blueprint $table) {
                $table->string('scope', 20)->default('site')->after('site_id')->index();
            });
        }

        if (Schema::hasColumn('api_tokens', 'scope')) {
            DB::table('api_tokens')
                ->whereNull('site_id')
                ->update(['scope' => 'system']);

            DB::table('api_tokens')
                ->whereNotNull('site_id')
                ->update(['scope' => 'site']);
        }
    }

    public function down()
    {
        if (Schema::hasColumn('api_tokens', 'scope')) {
            Schema::table('api_tokens', function (Blueprint $table) {
                $table->dropColumn('scope');
            });
        }
    }
}
