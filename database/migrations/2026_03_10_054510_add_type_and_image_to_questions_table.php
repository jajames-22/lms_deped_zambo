<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('assessment_questions', function (Blueprint $table) {
            // Adds 'mcq', 'checkbox', 'text', or 'instruction'
            $table->string('type')->default('mcq')->after('question_text');
            // Stores the image URL
            $table->string('media_url')->nullable()->after('type');
            $table->boolean('is_case_sensitive')->default(false)->after('media_url');
        });
    }

    public function down()
    {
        Schema::table('assessment_questions', function (Blueprint $table) {
            $table->dropColumn(['type', 'media_url']);
        });
    }
};