<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMonitorFieldsToWebdefacmentSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('webdefacment_setting', function (Blueprint $table) {
            if (!Schema::hasColumn('webdefacment_setting', 'hash_scope')) {
                $table->enum('hash_scope', ['page', 'sections'])->default('sections')->nullable();
            }
            if (!Schema::hasColumn('webdefacment_setting', 'hash_selectors')) {
                $table->json('hash_selectors')->nullable()->after('hash_scope');            // ["#main",".article","footer"]
            }
            if (!Schema::hasColumn('webdefacment_setting', 'hash_ignore_selectors')) {
                $table->json('hash_ignore_selectors')->nullable()->after('hash_selectors');  // [".time","#banner-rotator"]
            }

            // baselines
            if (!Schema::hasColumn('webdefacment_setting', 'baseline_section_hashes')) {
                $table->json('baseline_section_hashes')->nullable()->after('hash_ignore_selectors');
            }
            if (!Schema::hasColumn('webdefacment_setting', 'baseline_merkle')) {
                $table->char('baseline_merkle', 64)->nullable()->after('baseline_section_hashes');
            }
            if (!Schema::hasColumn('webdefacment_setting', 'baseline_text_fuzzy')) {
                $table->unsignedBigInteger('baseline_text_fuzzy')->nullable()->after('baseline_merkle'); // 64-bit
            }

            // baselines: assets/outbound
            if (!Schema::hasColumn('webdefacment_setting', 'baseline_assets')) {
                $table->json('baseline_assets')->nullable()->after('baseline_text_fuzzy');   // ["/assets/app.js", ...]
            }
            if (!Schema::hasColumn('webdefacment_setting', 'baseline_h_assets')) {
                $table->char('baseline_h_assets', 64)->nullable()->after('baseline_assets');
            }
            if (!Schema::hasColumn('webdefacment_setting', 'baseline_outbound')) {
                $table->json('baseline_outbound')->nullable()->after('baseline_h_assets');   // ["cdn.example.com", ...]
            }
            if (!Schema::hasColumn('webdefacment_setting', 'baseline_h_outbound')) {
                $table->char('baseline_h_outbound', 64)->nullable()->after('baseline_outbound');
            }

            // filters
            if (!Schema::hasColumn('webdefacment_setting', 'asset_allow_patterns')) {
                $table->json('asset_allow_patterns')->nullable()->after('baseline_h_outbound'); // ["\\.css$","\\.js$","^/assets/"]
            }
            if (!Schema::hasColumn('webdefacment_setting', 'domain_whitelist')) {
                $table->json('domain_whitelist')->nullable()->after('asset_allow_patterns');   // ["cdn.example.com"]
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
        Schema::table('webdefacment_setting', function (Blueprint $table) {
            $table->dropColumn([
                'hash_scope',
                'hash_selectors',
                'hash_ignore_selectors',
                'baseline_section_hashes',
                'baseline_merkle',
                'baseline_text_fuzzy',
                'baseline_assets',
                'baseline_h_assets',
                'baseline_outbound',
                'baseline_h_outbound',
                'asset_allow_patterns',
                'domain_whitelist'
            ]);
        });
    }
}
