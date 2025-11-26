<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddServerityAndStatusMonitoringToCredentialLeakRef extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('credential_leak_ref', function (Blueprint $table) {
            if (!Schema::hasColumn('credential_leak_ref', 'serverity')) {
                $table->string('serverity', 45)
                    ->nullable()
                    ->default('information')
                    ->after('status');
            }
            if (!Schema::hasColumn('credential_leak_ref', 'status_monitoring')) {
                $table->string('status_monitoring', 45)
                    ->nullable()
                    ->default('in_progress')
                    ->after('serverity');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('credential_leak_ref', function (Blueprint $table) {
            if (Schema::hasColumn('credential_leak_ref', 'serverity')) {
                $table->dropColumn('serverity');
            }
            if (Schema::hasColumn('credential_leak_ref', 'status_monitoring')) {
                $table->dropColumn('status_monitoring');
            }
        });
    }
}
