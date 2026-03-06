<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            // 1. Linking the District
            $table->foreignId('district_id')
                  ->nullable() 
                  ->after('id') // Optional: keeps the column order neat
                  ->constrained('districts')
                  ->cascadeOnDelete(); 

            // 2. School Logo (Stores the file path)
            $table->string('logo')->nullable()->after('name');

            // 3. Complete Address
            $table->text('address')->nullable()->after('logo');

            // 4. Academic Level (Restricted to your specific list)
            $table->enum('level', [
                'elementary', 
                'highschool', 
                'seniorHighschool', 
                'integrated'
            ])->default('elementary')->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropForeign(['district_id']);
            $table->dropColumn(['district_id', 'logo', 'address', 'level']);
        });
    }
};