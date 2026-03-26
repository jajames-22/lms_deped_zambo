<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // 1. Drop the old column
            $table->dropColumn('user_id');

            // 2. Add the new columns (nullable because a user won't have both)
            $table->string('lrn')->nullable()->unique()->after('email');
            $table->string('employee_id')->nullable()->unique()->after('lrn');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Reverse the actions if we need to rollback
            $table->dropColumn(['lrn', 'employee_id']);
            $table->string('user_id')->unique()->nullable()->after('email');
        });
    }
};