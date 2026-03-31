<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('enrollments', function (Blueprint $table) {
            // Adds a nullable timestamp column right after the status column
            $table->timestamp('completed_at')->nullable()->after('status');
        });
    }

    public function down()
    {
        Schema::table('enrollments', function (Blueprint $table) {
            // Drops the column if we ever need to rollback
            $table->dropColumn('completed_at');
        });
    }
};