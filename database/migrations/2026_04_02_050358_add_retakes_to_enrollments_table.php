<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('enrollments', function (Blueprint $table) {
            // Adds the 'retakes' column after 'progress_data'
            $table->integer('retakes')->default(0)->after('progress_data');
        });
    }

    public function down()
    {
        Schema::table('enrollments', function (Blueprint $table) {
            // Allows you to safely rollback if needed
            $table->dropColumn('retakes');
        });
    }
};