<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin user
        User::create([
            'name' => 'System Administrator',
            'email' => 'admin@school.local',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'student_number' => null,
            'is_active' => true,
            'is_first_login' => false,
            'email_verified_at' => now(),
        ]);

        // Teacher users
        User::create([
            'name' => 'Taro Yamada',
            'email' => 'yamada@school.local',
            'password' => Hash::make('password'),
            'role' => 'teacher',
            'student_number' => null,
            'is_active' => true,
            'is_first_login' => false,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Hanako Tanaka',
            'email' => 'tanaka@school.local',
            'password' => Hash::make('password'),
            'role' => 'teacher',
            'student_number' => null,
            'is_active' => true,
            'is_first_login' => false,
            'email_verified_at' => now(),
        ]);

        // Student Admin
        User::create([
            'name' => 'Jiro Suzuki',
            'email' => 'suzuki@school.local',
            'password' => Hash::make('password'),
            'role' => 'student_admin',
            'student_number' => 'S2024001',
            'is_active' => true,
            'is_first_login' => false,
            'email_verified_at' => now(),
        ]);

        // Regular Students
        for ($i = 2; $i <= 10; $i++) {
            User::create([
                'name' => "Student {$i}",
                'email' => "student{$i}@school.local",
                'password' => Hash::make('password'),
                'role' => 'student',
                'student_number' => 'S' . date('Y') . str_pad($i, 3, '0', STR_PAD_LEFT),
                'is_active' => true,
                'is_first_login' => true,
                'email_verified_at' => now(),
            ]);
        }
    }
}
