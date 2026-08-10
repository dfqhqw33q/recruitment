<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Super Admin', 'email' => 'admin@recruit.test', 'role' => 'Super Admin', 'phone' => '09171234567'],
            ['name' => 'HR Admin', 'email' => 'hr@recruit.test', 'role' => 'HR Administrator', 'phone' => '09172222222'],
            ['name' => 'Recruitment Officer', 'email' => 'officer@recruit.test', 'role' => 'Recruitment Officer', 'phone' => '09173333333'],
            ['name' => 'IT Department Head', 'email' => 'ithead@recruit.test', 'role' => 'Department Head', 'phone' => '09174444444'],
            ['name' => 'Finance Department Head', 'email' => 'finhead@recruit.test', 'role' => 'Department Head', 'phone' => '09175555555'],
        ];

        foreach ($users as $user) {
            $u = User::updateOrCreate([
                'email' => $user['email'],
            ], [
                'name' => $user['name'],
                'password' => Hash::make('password123'),
                'phone' => $user['phone'],
                'email_verified_at' => now(),
                'status' => 'active',
            ]);
            $u->assignRole($user['role']);
        }
    }
}
