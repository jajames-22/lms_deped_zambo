<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            // Nullable FK — when set, students viewing this module's certificate
            // will see this template instead of the globally active one.
            $table->foreignId('exclusive_template_id')
                ->nullable()
                ->constrained('certificate_templates')
                ->nullOnDelete()
                ->after('is_featured');
        });
    }

    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->dropForeign(['exclusive_template_id']);
            $table->dropColumn('exclusive_template_id');
        });
    }
};
