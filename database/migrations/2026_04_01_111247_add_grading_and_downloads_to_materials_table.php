<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('materials', function (Blueprint $table) {
            // Grading Configuration
            $table->integer('exam_weight')->default(60)->after('status');
            $table->integer('passing_percentage')->default(80)->after('exam_weight');
            
            // Analytics
            $table->unsignedBigInteger('downloads')->default(0)->after('views');
        });
    }

    public function down()
    {
        Schema::table('materials', function (Blueprint $table) {
            // This allows you to safely rollback if you make a mistake
            $table->dropColumn(['exam_weight', 'passing_percentage', 'downloads']);
        });
    }
};