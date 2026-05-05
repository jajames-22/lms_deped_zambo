<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            // Add the nullable text column to store the teacher's reason
            $table->text('revert_reason')->nullable()->after('status');
            
            // NOTE: If your 'status' column was explicitly defined as an ENUM() 
            // like $table->enum('status', ['draft', 'pending', 'published']), 
            // you will need to alter it to include 'revert_requested' by changing it to a string:
            // $table->string('status')->default('draft')->change(); 
            // (Make sure to run `composer require doctrine/dbal` if you use ->change())
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->dropColumn('revert_reason');
        });
    }
};