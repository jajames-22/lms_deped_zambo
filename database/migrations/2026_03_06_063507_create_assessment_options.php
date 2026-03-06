<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migration for assessment_options
        Schema::create('assessment_options', function (Blueprint $table) {
            $table->id();
            // Use unsignedBigInteger to ensure type compatibility for the foreign key
            $table->unsignedBigInteger('question_id');
            $table->text('option_text');
            $table->boolean('is_correct')->default(false); //
            $table->timestamps();

            // Define the relationship with cascade delete
            $table->foreign('question_id')
                ->references('id')
                ->on('assessment_questions')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_options');
    }
};
