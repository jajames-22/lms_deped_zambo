<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('assessment_questions', function (Blueprint $table) {
        $table->string('media_name')->nullable()->after('media_url'); 
    });
}

public function down()
{
    Schema::table('assessment_questions', function (Blueprint $table) {
        $table->dropColumn('media_name');
    });
}
};
