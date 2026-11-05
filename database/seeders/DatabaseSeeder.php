<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Admin User
        User::firstOrCreate(
            ['email' => 'admin@anubi.local'],
            [
                'name'     => 'System Admin',
                'password' => Hash::make('password123'),
                'role'     => 'admin',
            ]
        );

        // 2. Create Staff User
        User::firstOrCreate(
            ['email' => 'staff@anubi.local'],
            [
                'name'     => 'Finance Staff',
                'password' => Hash::make('password123'),
                'role'     => 'staff',
            ]
         );
    }
}