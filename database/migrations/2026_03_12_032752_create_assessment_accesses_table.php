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
        Schema::create('assessment_accesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained()->onDelete('cascade');
            $table->string('lrn', 12); // Philippine LRNs are exactly 12 digits
            $table->enum('status', ['offline', 'lobby', 'taking_exam', 'finished'])->default('offline');
            $table->timestamps();

            // This prevents the same LRN from being added to the same exam twice
            $table->unique(['assessment_id', 'lrn']); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_accesses');
    }
};
