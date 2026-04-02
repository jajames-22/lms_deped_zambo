<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Quiz Answers Table
        Schema::create('quiz_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            // THE FIX: This creates the strict reference to lesson_contents table
            $table->foreignId('lesson_content_id')->constrained('lesson_contents')->cascadeOnDelete();
            
            $table->unsignedBigInteger('quiz_option_id')->nullable();
            $table->text('text_answer')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->timestamps();
        });

        // 2. Exam Answers Table
        Schema::create('exam_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            // THE FIX: This creates the strict reference to exams table
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            
            $table->unsignedBigInteger('exam_option_id')->nullable(); 
            $table->text('text_answer')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->timestamps();
        });

        // 3. Add Progress Tracking to Enrollments
        Schema::table('enrollments', function (Blueprint $table) {
            // Check if column exists so it doesn't crash on rollback
            if (!Schema::hasColumn('enrollments', 'progress_data')) {
                $table->json('progress_data')->nullable()->after('status');
            }
        });
    }

    public function down()
    {
        Schema::dropIfExists('quiz_answers');
        Schema::dropIfExists('exam_answers');
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn('progress_data');
        });
    }
};