<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWebStatusToWebdefacmentSettingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('webdefacment_setting', function (Blueprint $table) {
            $table->enum('web_status', ['Up', 'Down'])->nullable();
            $table->index('web_status');
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
            $table->dropIndex(['web_status']);
            $table->dropColumn('web_status');
        });
    }
}
