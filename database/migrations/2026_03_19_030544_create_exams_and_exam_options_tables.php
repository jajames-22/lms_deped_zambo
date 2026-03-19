<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Create the exams table (Tied directly to the Material)
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained()->cascadeOnDelete(); // TIED TO MATERIAL
            $table->string('type')->default('mcq');
            $table->text('question_text')->nullable();
            $table->string('media_url')->nullable();
            $table->boolean('is_case_sensitive')->default(false);
            $table->timestamps();
        });

        // 2. Create the exam_options table
        Schema::create('exam_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->text('option_text')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('exam_options');
        Schema::dropIfExists('exams');
    }
};