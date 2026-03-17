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
        Schema::table('users', function (Blueprint $table) {
            // Changes the column to a string that can hold any status, defaulting to pending
            $table->string('status')->default('pending')->change();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // You can leave this blank or revert it to your old setup if needed
        });
    }
};
