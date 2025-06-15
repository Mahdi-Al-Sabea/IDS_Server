<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{

    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'a@1',
            'password' => Hash::make('123456'),
            'role' => 'Admin',
            'profile_picture' => 'storage/ProfileImages/default.png',
        ]);

        User::create([
            'name' => 'Employee',
            'email' => 'e@1',
            'password' => Hash::make('123456'),
            'role' => 'Employee',
            'profile_picture' => 'storage/ProfileImages/default.png',
        ]);

        User::create([
            'name' => 'Guest',
            'email' => 'g@1',
            'password' => Hash::make('123456'),
            'role' => 'Guest',
            'profile_picture' => 'storage/ProfileImages/default.png',
        ]);
    }
}
