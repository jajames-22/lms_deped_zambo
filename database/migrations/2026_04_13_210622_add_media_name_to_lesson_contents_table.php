<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('lesson_contents', function (Blueprint $table) {
            // Add the media_name column, placing it logically after the URL
            $table->string('media_name')->nullable()->after('media_url');
        });

        Schema::table('exams', function (Blueprint $table) {
            // Add the media_name column, placing it logically after the URL
            $table->string('media_name')->nullable()->after('media_url');
        });
    }

    public function down()
    {
        Schema::table('lesson_contents', function (Blueprint $table) {
            $table->dropColumn('media_name');
        });

        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn('media_name');
        });
    }

};
