<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->integer('sort_order')->default(1)->after('time_limit');
        });

        Schema::table('lesson_contents', function (Blueprint $table) {
            $table->integer('sort_order')->default(1)->after('is_case_sensitive');
        });

        Schema::table('exams', function (Blueprint $table) {
            $table->integer('sort_order')->default(1)->after('is_case_sensitive');
        });
    }

    public function down()
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });

        Schema::table('lesson_contents', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });

        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};