<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */

    public function up()
    {
        if (!Schema::hasColumn('schools', 'school_id')) {
            Schema::table('schools', function (Blueprint $table) {
                $table->string('school_id')->after('id');
            });
        }
    }

    public function down()
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn('school_id');
        });
    }
};