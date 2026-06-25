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
            $table->timestamp('dropped_at')->nullable()->after('completed_at');
            $table->string('dropped_by_type')->nullable()->after('dropped_at');
        });

        Schema::table('material_accesses', function (Blueprint $table) {
            $table->timestamp('dropped_at')->nullable()->after('retakes');
            $table->string('dropped_by_type')->nullable()->after('dropped_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn(['dropped_at', 'dropped_by_type']);
        });

        Schema::table('material_accesses', function (Blueprint $table) {
            $table->dropColumn(['dropped_at', 'dropped_by_type']);
        });
    }
};
