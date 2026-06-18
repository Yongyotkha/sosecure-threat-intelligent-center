<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUrlToApiTokensTable extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('api_tokens', 'url')) {
            Schema::table('api_tokens', function (Blueprint $table) {
                $table->string('url', 500)->nullable()->after('type');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('api_tokens', 'url')) {
            Schema::table('api_tokens', function (Blueprint $table) {
                $table->dropColumn('url');
            });
        }
    }
}
