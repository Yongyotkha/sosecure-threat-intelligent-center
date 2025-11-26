<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDataCveSourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data_cve_sources', function (Blueprint $table) {
            $table->increments('id');
            $table->string('namecve', 50)->index();
            $table->string('source', 20);

            $table->timestamps();

            $table->unique(['namecve', 'source'], 'unique_namecve_source');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('data_cve_sources');
    }
}
