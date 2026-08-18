<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddImageHighlightToWebdefacmentSettingTable extends Migration
{
    public function up()
    {
        Schema::table('webdefacment_setting', function (Blueprint $table) {
            if (!Schema::hasColumn('webdefacment_setting', 'image_highlight')) {
                $table->string('image_highlight', 500)->nullable()->after('image_last');
            }
        });
    }

    public function down()
    {
        Schema::table('webdefacment_setting', function (Blueprint $table) {
            if (Schema::hasColumn('webdefacment_setting', 'image_highlight')) {
                $table->dropColumn('image_highlight');
            }
        });
    }
}
