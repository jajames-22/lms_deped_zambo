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
        Schema::create('broadcast_reads', function (Blueprint $table) {
            $table->id();
            
            // Links to the user who read the broadcast
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            // Links to the specific broadcast
            $table->foreignId('broadcast_id')->constrained('broadcasts')->cascadeOnDelete();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('broadcast_reads');
    }
};