<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            // Cumulative study time in minutes, incremented on each session flush
            $table->unsignedInteger('calculated_time')->default(0)->after('progress_data');
            // Timestamp set when student enters the study page; null when session is flushed
            $table->timestamp('study_session_started_at')->nullable()->after('calculated_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn(['calculated_time', 'study_session_started_at']);
        });
    }
};
