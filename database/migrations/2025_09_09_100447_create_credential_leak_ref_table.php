<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCredentialLeakRefTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('credential_leak_ref', function (Blueprint $table) {
            $table->bigIncrements('id'); 

            $table->unsignedBigInteger('data_leak_feed_id');
            $table->unsignedBigInteger('site_id')->nullable();

            $table->string('keyword')->nullable();     
            $table->string('feel_type')->nullable();  

            $table->longText('content')->nullable();

            $table->unsignedTinyInteger('status')->default(1);

            $table->timestamps();

            $table->index('data_leak_feed_id');
            $table->index('site_id');
            $table->index('feel_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('credential_leak_ref');
    }
}
