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
        Schema::table('assessment_accesses', function (Blueprint $table) {
            $table->integer('pauses_left')->default(3)->after('status');
        });
    }

    public function down()
    {
        Schema::table('assessment_accesses', function (Blueprint $table) {
            $table->dropColumn('pauses_left');
        });
    }
};
