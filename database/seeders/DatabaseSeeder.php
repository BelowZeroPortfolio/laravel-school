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
        // Admin user
        User::factory()->create([
            'username' => 'admin',
            'password' => Hash::make('password'),
            'full_name' => 'System Administrator',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        // Principal user
        User::factory()->create([
            'username' => 'principal',
            'password' => Hash::make('password'),
            'full_name' => 'School Principal',
            'email' => 'principal@example.com',
            'role' => 'principal',
        ]);

        // Teacher user
        User::factory()->create([
            'username' => 'teacher',
            'password' => Hash::make('password'),
            'full_name' => 'Sample Teacher',
            'email' => 'teacher@example.com',
            'role' => 'teacher',
        ]);
    }
}
