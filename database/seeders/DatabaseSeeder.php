<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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
                'last_name' => 'User',
                'username' => 'testuser',
                'email' => 'test@example.com',
                'school_id' => $school->id,
            ]);
        }

        $this->call([
            AdminSeeder::class,
            QuadrantAndDistrictSeeder::class,
        ]);
    }
}
