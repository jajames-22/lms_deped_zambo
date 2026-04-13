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
        Schema::table('materials', function (Blueprint $table) {
            // Add the new columns after the 'status' column for clean organization
            $table->text('admin_remarks')->nullable()->after('status');
            $table->json('evaluation_json')->nullable()->after('admin_remarks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            // Drop the columns if we ever need to rollback
            $table->dropColumn(['admin_remarks', 'evaluation_json']);
        });
    }
};