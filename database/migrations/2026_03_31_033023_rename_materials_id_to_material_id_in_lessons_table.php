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
        Schema::table('lessons', function (Blueprint $table) {
            // 1. Drop the old foreign key constraint
            // Laravel's default naming convention is: {table}_{column}_foreign
            $table->dropForeign(['materials_id']);

            // 2. Rename the column
            $table->renameColumn('materials_id', 'material_id');

            // 3. Add the new foreign key constraint with the correct column name
            $table->foreign('material_id')
                  ->references('id')
                  ->on('materials')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            // 1. Drop the new foreign key constraint
            $table->dropForeign(['material_id']);

            // 2. Revert the column name back to the plural form
            $table->renameColumn('material_id', 'materials_id');

            // 3. Re-add the old foreign key constraint
            $table->foreign('materials_id')
                  ->references('id')
                  ->on('materials')
                  ->onDelete('cascade');
        });
    }
};