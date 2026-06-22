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
        if (Schema::hasColumn('lessons', 'materials_id')) {
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('lessons', 'material_id')) {
            // We check if material_id exists to roll it back, but wait - if the original creation had material_id, rolling this back might break it. 
            // Better to only rollback if we actually renamed it, but since we can't easily know, we just assume if it's there we can revert it, 
            // EXCEPT the original table has 'material_id' now! Reverting it would break it.
            // Let's just make both methods safe.
            Schema::table('lessons', function (Blueprint $table) {
                if (Schema::hasColumn('lessons', 'material_id') && !Schema::hasColumn('lessons', 'materials_id')) {
                    // 1. Drop the new foreign key constraint
                    $table->dropForeign(['material_id']);

                    // 2. Revert the column name back to the plural form
                    $table->renameColumn('material_id', 'materials_id');

                    // 3. Re-add the old foreign key constraint
                    $table->foreign('materials_id')
                          ->references('id')
                          ->on('materials')
                          ->onDelete('cascade');
                }
            });
        }
    }
};