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
            if (!Schema::hasColumn('webdefacment_data_check', 'scope_snapshot')) {
                $table->string('scope_snapshot', 16)->nullable();
            }
            if (!Schema::hasColumn('webdefacment_data_check', 'selectors_snapshot')) {
                $table->json('selectors_snapshot')->nullable()->after('scope_snapshot');
            }
            if (!Schema::hasColumn('webdefacment_data_check', 'merkle_old')) {
                $table->char('merkle_old', 64)->nullable()->after('hash_old');
                $table->char('merkle_new', 64)->nullable()->after('merkle_old');
                $table->integer('simhash_bits')->nullable()->after('hash_percent');
            }
            if (!Schema::hasColumn('webdefacment_data_check', 'section_diffs')) {
                $table->json('section_diffs')->nullable()->after('element_percent'); // [{section,old,new,changed}]
            }
            if (!Schema::hasColumn('webdefacment_data_check', 'assets_add')) {
                $table->json('assets_add')->nullable()->after('section_diffs');
                $table->json('assets_del')->nullable()->after('assets_add');
            }
            if (!Schema::hasColumn('webdefacment_data_check', 'outbound_new_not_whitelisted')) {
                $table->json('outbound_new_not_whitelisted')->nullable()->after('assets_del');
            }
            if (!Schema::hasColumn('webdefacment_data_check', 'score')) {
                $table->float('score')->nullable()->after('keyword_percent');
                $table->string('reason', 50)->nullable()->after('score'); // new_outbound_domain | assets_delta | score_threshold | fuzzy_text_distance
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
            $table->dropColumn([
                'scope_snapshot',
                'selectors_snapshot',
                'merkle_old',
                'merkle_new',
                'simhash_bits',
                'section_diffs',
                'assets_add',
                'assets_del',
                'outbound_new_not_whitelisted',
                'score',
                'reason'
            ]);
        });
    }
}
