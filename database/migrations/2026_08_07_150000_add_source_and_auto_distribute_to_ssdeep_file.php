<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSourceAndAutoDistributeToSsdeepFile extends Migration
{
    /**
     * source: master (manual upload) | auto (candidate promote)
     * Global auto-distribute flag lives in ssdeep_settings (not per-pack).
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('ssdeep_file')) {
            Schema::table('ssdeep_file', function (Blueprint $table) {
                if (!Schema::hasColumn('ssdeep_file', 'source')) {
                    $table->string('source', 16)->default('master')->after('status');
                }
            });

            // Drop leftover per-pack column from earlier draft if present.
            if (Schema::hasColumn('ssdeep_file', 'auto_distribute')) {
                Schema::table('ssdeep_file', function (Blueprint $table) {
                    $table->dropColumn('auto_distribute');
                });
            }

            DB::table('ssdeep_file')
                ->where(function ($q) {
                    $q->where('file_name', 'like', '%_auto%')
                        ->orWhere('file_name', 'like', '%auto.zip')
                        ->orWhere('description', 'like', 'Auto-promoted%')
                        ->orWhere('title', 'like', '%(auto)%');
                })
                ->where(function ($q) {
                    $q->whereNull('source')->orWhere('source', '')->orWhere('source', 'master');
                })
                ->update(['source' => 'auto']);
        }

        if (!Schema::hasTable('ssdeep_settings')) {
            Schema::create('ssdeep_settings', function (Blueprint $table) {
                $table->increments('id');
                $table->string('key', 64)->unique();
                $table->string('value', 255)->nullable();
                $table->timestamps();
            });
        }

        $exists = DB::table('ssdeep_settings')->where('key', 'auto_distribute_sites')->exists();
        if (!$exists) {
            DB::table('ssdeep_settings')->insert([
                'key' => 'auto_distribute_sites',
                'value' => 'N',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ssdeep_settings');

        if (Schema::hasTable('ssdeep_file') && Schema::hasColumn('ssdeep_file', 'source')) {
            Schema::table('ssdeep_file', function (Blueprint $table) {
                $table->dropColumn('source');
            });
        }
    }
}
