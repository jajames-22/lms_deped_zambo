<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificate_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('background_image')->nullable(); // stored path in /storage
            $table->json('elements')->nullable();           // positions & sizes of all draggable fields
            $table->boolean('is_active')->default(false);  // only one can be active at a time
            $table->boolean('is_default')->default(false); // the original built-in template
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate_templates');
    }
};
