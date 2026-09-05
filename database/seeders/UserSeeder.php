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
            ['name' => 'Super Admin', 'email' => 'admin@hiraya.com', 'role' => 'Super Admin'],
            ['name' => 'HR Administrator', 'email' => 'hr@hiraya.com', 'role' => 'HR Administrator'],
            ['name' => 'Recruitment Officer', 'email' => 'recruitment@hiraya.com', 'role' => 'Recruitment Officer'],
            ['name' => 'Tour Operations Head', 'email' => 'tours.head@hiraya.com', 'role' => 'Department Head'],
            ['name' => 'Ticketing & Visa Head', 'email' => 'visa.head@hiraya.com', 'role' => 'Department Head'],
            ['name' => 'Sales & Travel Head', 'email' => 'sales.head@hiraya.com', 'role' => 'Department Head'],
            ['name' => 'Carlos Sainz', 'email' => 'carlos@gmail.com', 'role' => 'Employee'],
            ['name' => 'Samantha Tan', 'email' => 'samantha.tan@gmail.com', 'role' => 'Employee'],
            ['name' => 'Ramon Bautista', 'email' => 'ramon.bautista@gmail.com', 'role' => 'Employee'],
        ];

        foreach ($users as $user) {
            $u = User::updateOrCreate([
                'email' => $user['email'],
            ], [
                'name' => $user['name'],
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'status' => 'active',
            ]);
            $u->assignRole($user['role']);
        }
    }
}
