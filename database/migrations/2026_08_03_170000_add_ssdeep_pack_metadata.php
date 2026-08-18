<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSsdeepPackMetadata extends Migration
{
    /**
     * Readable pack labels: title / category / description.
     * Table name is ssdeep_file (DB prefix fx_ applied by connection).
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('ssdeep_file')) {
            return;
        }

        Schema::table('ssdeep_file', function (Blueprint $table) {
            if (!Schema::hasColumn('ssdeep_file', 'title')) {
                $table->string('title', 255)->nullable()->after('file_name');
            }
            if (!Schema::hasColumn('ssdeep_file', 'category')) {
                $table->string('category', 64)->nullable()->after('title');
            }
            if (!Schema::hasColumn('ssdeep_file', 'description')) {
                $table->text('description')->nullable()->after('category');
            }
        });
    }

    /**
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('ssdeep_file')) {
            return;
        }

        Schema::table('ssdeep_file', function (Blueprint $table) {
            if (Schema::hasColumn('ssdeep_file', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('ssdeep_file', 'category')) {
                $table->dropColumn('category');
            }
            if (Schema::hasColumn('ssdeep_file', 'title')) {
                $table->dropColumn('title');
            }
        });
    }
}
