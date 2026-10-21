<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        
        // Hash password 1 kali di awal untuk menghemat alokasi CPU
        $password = Hash::make('password123');

        // 1. Akun Admin
        User::updateOrCreate(
            ['email' => 'admin@cbt.com'],
            [
                'name' => 'Admin CBT',
                'password' => $password,
                'role' => UserRole::ADMIN->value,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        // 2. Bulk Insert 100 Akun Siswa (siswa1@cbt.com s/d siswa100@cbt.com)
        $students = [];
        for ($i = 1; $i <= 100; $i++) {
            $students[] = [
                'name' => "Siswa {$i}",
                'email' => "siswa{$i}@cbt.com",
                'password' => $password,
                'role' => UserRole::STUDENT->value,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        User::insertOrIgnore($students);
    }
}