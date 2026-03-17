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
        Schema::table('assessments', function (Blueprint $table) {
            $table->boolean('show_results')->default(false)->after('status');
        });
    }
    
    public function down()
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->dropColumn('show_results');
        });
    }
};
