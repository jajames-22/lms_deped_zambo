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
            // Adding 'views' to track engagement. unsignedBigInteger is safe for large numbers.
            $table->unsignedBigInteger('views')->default(0)->after('thumbnail');
            
            // Adding the privacy toggle. Defaulting to false (private) is usually safer.
            $table->boolean('is_public')->default(false)->after('views');
            
            // Note: If you prefer the string option, comment out the line above and use this instead:
            // $table->string('visibility')->default('private')->after('views');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->dropColumn(['views', 'is_public']);
            
            // If you used 'visibility', drop that instead:
            // $table->dropColumn(['views', 'visibility']);
        });
    }
};