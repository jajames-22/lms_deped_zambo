<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            // 1. Drop the foreign keys that depend on the indexes
            $table->dropForeign(['materials_id']);
            $table->dropForeign(['user_id']);

            // 2. NOW we can safely drop the old unique constraint
            $table->dropUnique(['materials_id', 'user_id']);

            // 3. Make user_id nullable
            $table->unsignedBigInteger('user_id')->nullable()->change();

            // 4. Add the email column
            $table->string('email')->after('user_id')->nullable();

            // 5. Create the new unique constraint for email invites
            $table->unique(['materials_id', 'email']);

            // 6. Put the foreign keys back exactly as they were
            $table->foreign('materials_id')->references('id')->on('materials')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            // Drop foreign keys before reverting
            $table->dropForeign(['materials_id']);
            $table->dropForeign(['user_id']);

            // Drop the new constraint and column
            $table->dropUnique(['materials_id', 'email']);
            $table->dropColumn('email');

            // Revert user_id to strictly required
            $table->unsignedBigInteger('user_id')->nullable(false)->change();

            // Put the old unique constraint back
            $table->unique(['materials_id', 'user_id']);

            // Put the foreign keys back
            $table->foreign('materials_id')->references('id')->on('materials')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};