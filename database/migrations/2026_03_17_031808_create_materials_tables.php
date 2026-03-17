<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Materials Table
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('instructor_id'); 
            $table->string('status')->default('draft');
            $table->string('thumbnail')->nullable();
            $table->json('draft_json')->nullable(); 
            $table->timestamps();

            $table->foreign('instructor_id')->references('id')->on('users')->onDelete('cascade');
        });

        // 2. Lessons Table
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('materials_id'); 
            $table->string('title');
            $table->integer('time_limit')->default(0);
            $table->timestamps();

            $table->foreign('materials_id')->references('id')->on('materials')->onDelete('cascade');
        });

        // 3. Quizzes Table
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lesson_id');
            $table->string('type')->default('instruction'); 
            $table->text('question_text')->nullable();
            $table->string('media_url')->nullable();
            $table->boolean('is_case_sensitive')->default(false);
            $table->timestamps();

            $table->foreign('lesson_id')->references('id')->on('lessons')->onDelete('cascade');
        });

        // 4. Quiz Options Table
        Schema::create('quiz_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quiz_id');
            $table->text('option_text')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->timestamps();

            $table->foreign('quiz_id')->references('id')->on('quizzes')->onDelete('cascade');
        });

        // 5. Enrollments Table (Upgraded from Material Access)
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('materials_id');
            $table->unsignedBigInteger('user_id'); // Strictly linking to the User/Student
            $table->string('status')->default('enrolled'); // e.g., enrolled, completed, dropped
            $table->timestamps();

            // Enforce relational integrity
            $table->foreign('materials_id')->references('id')->on('materials')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Prevent a user from being enrolled in the exact same material twice
            $table->unique(['materials_id', 'user_id']); 
        });
    }

    public function down()
    {
        Schema::dropIfExists('enrollments');
        Schema::dropIfExists('quiz_options');
        Schema::dropIfExists('quizzes');
        Schema::dropIfExists('lessons');
        Schema::dropIfExists('materials');
    }
};