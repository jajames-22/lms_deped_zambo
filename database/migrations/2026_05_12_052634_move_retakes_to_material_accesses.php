<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // 1. Add the column to material_accesses
        Schema::table('material_accesses', function (Blueprint $table) {
            $table->integer('retakes')->default(0)->after('status');
        });

        // 2. (Optional but recommended) Transfer existing data 
        // This links the user_id in enrollments to the student_id in material_accesses
        DB::statement('
            UPDATE material_accesses ma 
            JOIN enrollments e ON ma.material_id = e.material_id AND ma.student_id = e.user_id 
            SET ma.retakes = e.retakes
        ');

        // 3. Drop the column from enrollments
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn('retakes');
        });
    }

    public function down()
    {
        // 1. Restore the column to enrollments
        Schema::table('enrollments', function (Blueprint $table) {
            $table->integer('retakes')->default(0)->after('progress_data');
        });

        // 2. Transfer the data back
        DB::statement('
            UPDATE enrollments e 
            JOIN material_accesses ma ON e.material_id = ma.material_id AND e.user_id = ma.student_id 
            SET e.retakes = ma.retakes
        ');

        // 3. Drop the column from material_accesses
        Schema::table('material_accesses', function (Blueprint $table) {
            $table->dropColumn('retakes');
        });
    }
};