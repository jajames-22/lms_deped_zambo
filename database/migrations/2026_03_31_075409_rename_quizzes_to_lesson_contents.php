<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Rename the table
        Schema::rename('quizzes', 'lesson_contents');
    }

    public function down()
    {
        // Revert the name back if you ever rollback the database
        Schema::rename('lesson_contents', 'quizzes');
    }
};