<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('assessment_categories', function (Blueprint $table) {
            $table->integer('sort_order')->default(1)->after('time_limit');
        });

        Schema::table('assessment_questions', function (Blueprint $table) {
            $table->integer('sort_order')->default(1)->after('is_case_sensitive');
        });
    }

    public function down()
    {
        Schema::table('assessment_categories', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });

        Schema::table('assessment_questions', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};