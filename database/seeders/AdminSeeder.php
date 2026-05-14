<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if an admin already exists to prevent duplicate master accounts
        if (User::where('email', 'admin@depedzamboanga.gov.ph')->doesntExist()) {
            User::create([
                'username' => 'superadmin',
                'first_name' => 'DepEd',
                'last_name' => 'Admin',
                'email' => 'adminlms@deped.gov.ph',
                'password' => Hash::make('Admin456'), 
                'role' => 'admin',
                'status' => 'verified',
                'email_verified_at' => now(),
            ]);
            
            $this->command->info('Default Admin account created successfully.');
        } else {
            $this->command->info('Admin account already exists.');
        }
    }
}