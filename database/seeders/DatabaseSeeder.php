<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $school = \App\Models\School::firstOrCreate([
            'school_id' => 'SCH-001',
            'name' => 'Default Test School'
        ]);

        if (User::where('email', 'test@example.com')->doesntExist()) {
            User::factory()->create([
                'first_name' => 'Test',
                'last_name' => 'Student',
                'lrn' => '123456789000',
                'email' => 'test@example.com',
                'password' => Hash::make('Test1234'),
                'school_id' => $school->id,
                'role' => 'student',
                'status' => 'verified',
                'grade_level' => 'Grade 10',
            ]);
        }

        $this->call([
            AdminSeeder::class,
            QuadrantAndDistrictSeeder::class,
        ]);
    }
}
