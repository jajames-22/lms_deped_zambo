<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('material_accesses', function (Blueprint $table) {
            // Add the column as nullable (since pending invites don't have a student ID yet)
            $table->unsignedBigInteger('student_id')->nullable()->after('email');

            // (Optional but highly recommended) Link it to your users table
            $table->foreign('student_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('material_accesses', function (Blueprint $table) {
            $table->dropForeign(['student_id']); // Drop the foreign key first
            $table->dropColumn('student_id');    // Then drop the column
        });
    }
};