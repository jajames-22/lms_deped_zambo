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

    }

    public function down()
    {
        Schema::dropIfExists('quiz_options');
        Schema::dropIfExists('quizzes');
        Schema::dropIfExists('lessons');
        Schema::dropIfExists('materials');
    }
};