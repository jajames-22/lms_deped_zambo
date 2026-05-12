<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SchoolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schools = [
            ['school_id' => '303734', 'name' => 'Zamboanga City National High School (Main)'],
            ['school_id' => '303735', 'name' => 'Ayala National High School'],
            ['school_id' => '303736', 'name' => 'Culianan National High School'],
            ['school_id' => '303737', 'name' => 'Southcom National High School'],
            ['school_id' => '303738', 'name' => 'Talon-Talon National High School'],
            ['school_id' => '303739', 'name' => 'Don Pablo Lorenzo Memorial High School'],
            ['school_id' => '303740', 'name' => 'Vitali National High School'],
            ['school_id' => '303741', 'name' => 'Curuan National High School'],
            // Add more schools as needed...
        ];

        // Use insertOrIgnore to prevent duplicates if you run the seeder multiple times
        DB::table('schools')->insertOrIgnore(array_map(function ($school) {
            return [
                'school_id' => $school['school_id'],
                'name' => $school['name'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }, $schools));
    }
}