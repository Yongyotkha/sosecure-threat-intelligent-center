<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusMonitoringAndSeverityToCredentialLeakRefTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('credential_leak_ref', function (Blueprint $table) {
            if (!Schema::hasColumn('credential_leak_ref', 'status_monitoring')) {
                $table->string('status_monitoring', 45)
                    ->nullable()
                    ->default('in_progress')
                    ->after('status');
            }

            if (!Schema::hasColumn('credential_leak_ref', 'severity')) {
                $table->string('severity', 45)
                    ->nullable()
                    ->default('Critical')
                    ->after('status_monitoring');
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
            if (Schema::hasColumn('credential_leak_ref', 'status_monitoring')) {
                $table->dropColumn('status_monitoring');
            }
            if (Schema::hasColumn('credential_leak_ref', 'severity')) {
                $table->dropColumn('severity');
            }
        });
    }
}
