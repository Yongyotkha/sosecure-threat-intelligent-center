<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeletedAtToCredentialLeakRef extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('credential_leak_ref', function (Blueprint $table) {
            $table->softDeletes(); // เพิ่ม deleted_at TIMESTAMP NULL
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('credential_leak_ref', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
}
