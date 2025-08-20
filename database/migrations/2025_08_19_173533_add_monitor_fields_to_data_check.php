<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMonitorFieldsToDataCheck extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('webdefacment_data_check', function (Blueprint $table) {
            if (!Schema::hasColumn('fx_webdefacement_setting','hash_scope')) {
                $table->enum('hash_scope', ['page','sections'])->default('sections')->after('hash');
            }
            if (!Schema::hasColumn('fx_webdefacement_setting','hash_selectors')) {
                $table->json('hash_selectors')->nullable()->after('hash_scope');            // ["#main",".article","footer"]
            }
            if (!Schema::hasColumn('fx_webdefacement_setting','hash_ignore_selectors')) {
                $table->json('hash_ignore_selectors')->nullable()->after('hash_selectors');  // [".time","#banner-rotator"]
            }

            // baselines
            if (!Schema::hasColumn('fx_webdefacement_setting','baseline_section_hashes')) {
                $table->json('baseline_section_hashes')->nullable()->after('hash_ignore_selectors');
            }
            if (!Schema::hasColumn('fx_webdefacement_setting','baseline_merkle')) {
                $table->char('baseline_merkle', 64)->nullable()->after('baseline_section_hashes');
            }
            if (!Schema::hasColumn('fx_webdefacement_setting','baseline_text_fuzzy')) {
                $table->unsignedBigInteger('baseline_text_fuzzy')->nullable()->after('baseline_merkle'); // 64-bit
            }

            // baselines: assets/outbound
            if (!Schema::hasColumn('fx_webdefacement_setting','baseline_assets')) {
                $table->json('baseline_assets')->nullable()->after('baseline_text_fuzzy');   // ["/assets/app.js", ...]
            }
            if (!Schema::hasColumn('fx_webdefacement_setting','baseline_h_assets')) {
                $table->char('baseline_h_assets', 64)->nullable()->after('baseline_assets');
            }
            if (!Schema::hasColumn('fx_webdefacement_setting','baseline_outbound')) {
                $table->json('baseline_outbound')->nullable()->after('baseline_h_assets');   // ["cdn.example.com", ...]
            }
            if (!Schema::hasColumn('fx_webdefacement_setting','baseline_h_outbound')) {
                $table->char('baseline_h_outbound', 64)->nullable()->after('baseline_outbound');
            }

            // filters
            if (!Schema::hasColumn('fx_webdefacement_setting','asset_allow_patterns')) {
                $table->json('asset_allow_patterns')->nullable()->after('baseline_h_outbound'); // ["\\.css$","\\.js$","^/assets/"]
            }
            if (!Schema::hasColumn('fx_webdefacement_setting','domain_whitelist')) {
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
        Schema::table('webdefacment_data_check', function (Blueprint $table) {
            //
        });
    }
}
