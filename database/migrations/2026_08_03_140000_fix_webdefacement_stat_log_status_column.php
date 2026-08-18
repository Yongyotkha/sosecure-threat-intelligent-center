<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FixWebdefacementStatLogStatusColumn extends Migration
{
    /**
     * Allow all scan outcomes used by WebDefacementProccess:
     * Normal | Medium | High | Down | Skipped | Error
     *
     * Previous enum only allowed lowercase normal/medium/high, so Down/Skipped/Error inserts failed.
     */
    public function up()
    {
        if (!Schema::hasTable('webdefacement_stat_log')) {
            Schema::create('webdefacement_stat_log', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('site_id')->index();
                $table->unsignedBigInteger('webdefacement_setting_id')->nullable()->index();
                $table->unsignedBigInteger('result_id')->nullable();
                $table->string('status', 32)->default('Normal');
                $table->double('score')->nullable();
                $table->float('diff_percent')->nullable();
                $table->boolean('hash_changed')->default(false);
                $table->boolean('image_changed')->default(false);
                $table->boolean('alert_sent')->default(false);
                $table->string('reason', 500)->nullable();
                $table->timestamp('checked_at')->nullable();
                $table->timestamps();
            });

            return;
        }

        // Raw ALTER must include connection table prefix (e.g. fx_).
        $table = DB::getTablePrefix() . 'webdefacement_stat_log';
        DB::statement("ALTER TABLE `{$table}` MODIFY COLUMN `status` VARCHAR(32) NOT NULL DEFAULT 'Normal'");

        if (!Schema::hasColumn('webdefacement_stat_log', 'webdefacement_setting_id')) {
            Schema::table('webdefacement_stat_log', function (Blueprint $table) {
                $table->unsignedBigInteger('webdefacement_setting_id')->nullable()->after('site_id')->index();
            });
        }

        // Normalize any legacy lowercase enum values to Title Case.
        $map = [
            'normal'  => 'Normal',
            'medium'  => 'Medium',
            'high'    => 'High',
            'down'    => 'Down',
            'skipped' => 'Skipped',
            'error'   => 'Error',
        ];

        foreach ($map as $from => $to) {
            DB::table('webdefacement_stat_log')
                ->whereRaw('LOWER(status) = ?', [$from])
                ->where('status', '!=', $to)
                ->update(['status' => $to]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (!Schema::hasTable('webdefacement_stat_log')) {
            return;
        }

        DB::table('webdefacement_stat_log')->whereIn('status', ['Down', 'down'])->update(['status' => 'normal']);
        DB::table('webdefacement_stat_log')->whereIn('status', ['Skipped', 'skipped', 'Error', 'error'])->update(['status' => 'normal']);
        DB::table('webdefacement_stat_log')->where('status', 'Normal')->update(['status' => 'normal']);
        DB::table('webdefacement_stat_log')->where('status', 'Medium')->update(['status' => 'medium']);
        DB::table('webdefacement_stat_log')->where('status', 'High')->update(['status' => 'high']);

        $table = DB::getTablePrefix() . 'webdefacement_stat_log';
        DB::statement("ALTER TABLE `{$table}` MODIFY COLUMN `status` ENUM('normal','medium','high') NOT NULL DEFAULT 'normal'");
    }
}
