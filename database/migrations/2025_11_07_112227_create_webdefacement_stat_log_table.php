<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWebdefacementStatLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('webdefacement_stat_log', function (Blueprint $table) {
            $table->bigIncrements('id');

            // ✅ site_id แค่ครั้งเดียว
            $table->unsignedBigInteger('site_id')->index();
            $table->unsignedBigInteger('result_id')->nullable();

            // Title Case: Normal|Medium|High|Down|Skipped|Error (see WebDefacementService)
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
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('webdefacement_stat_log');
    }
}
