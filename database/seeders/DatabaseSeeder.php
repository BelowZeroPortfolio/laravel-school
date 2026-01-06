<?php

namespace Database\Seeders;

use App\Models\School;
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
        // Create default school
        $school = School::create([
            'name' => 'Default School',
            'code' => 'SCH-001',
            'address' => '123 Main Street',
            'phone' => '(123) 456-7890',
            'email' => 'info@defaultschool.edu',
            'is_active' => true,
        ]);

        // Super Admin (no school - can manage all schools)
        User::factory()->create([
            'school_id' => null,
            'username' => 'superadmin',
            'password' => Hash::make('password'),
            'full_name' => 'Super Administrator',
            'email' => 'superadmin@example.com',
            'role' => 'super_admin',
        ]);

        // School Admin
        User::factory()->create([
            'school_id' => $school->id,
            'username' => 'admin',
            'password' => Hash::make('password'),
            'full_name' => 'School Administrator',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        // Principal
        User::factory()->create([
            'school_id' => $school->id,
            'username' => 'principal',
            'password' => Hash::make('password'),
            'full_name' => 'School Principal',
            'email' => 'principal@example.com',
            'role' => 'principal',
        ]);

        // Teacher
        User::factory()->create([
            'school_id' => $school->id,
            'username' => 'teacher',
            'password' => Hash::make('password'),
            'full_name' => 'Sample Teacher',
            'email' => 'teacher@example.com',
            'role' => 'teacher',
        ]);
    }
}
