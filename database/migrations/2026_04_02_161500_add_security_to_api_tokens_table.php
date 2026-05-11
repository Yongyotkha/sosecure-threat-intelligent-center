<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSecurityToApiTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('api_tokens', function (Blueprint $table) {
            $table->string('last_ip')->nullable()->after('last_used_at');
            $table->text('whitelist_ips')->nullable()->after('last_ip'); // Comma-separated or JSON string
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('api_tokens', function (Blueprint $table) {
            $table->dropColumn(['last_ip', 'whitelist_ips']);
        });
    }
}
