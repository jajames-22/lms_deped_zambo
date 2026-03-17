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
        Schema::table('assessment_sessions', function (Blueprint $table) {
            $table->boolean('is_completed')->default(false)->after('time_remaining');
        });
    }

    public function down()
    {
        Schema::table('assessment_sessions', function (Blueprint $table) {
            $table->dropColumn('is_completed');
        });
    }
};
