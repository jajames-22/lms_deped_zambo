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
        // Safely drop the stuck table if it already exists from a previous failed run
        Schema::dropIfExists('enrollments');

        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys linking to the materials and users tables
            $table->foreignId('material_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            // Status of the enrollment (e.g., enrolled, completed, dropped)
            $table->string('status')->default('enrolled');
            
            $table->timestamps();

            // Prevent a user from being enrolled in the exact same material twice
            $table->unique(['material_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};