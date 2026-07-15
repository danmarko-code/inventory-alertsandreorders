<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUsersSeeder extends Seeder
{
    public function run(): void
    {
        $admins = [
            ['name' => 'Admin 1', 'email' => 'admin1@erp.local', 'avatar_color' => '#1a2f7a'],
            ['name' => 'Admin 2', 'email' => 'admin2@erp.local', 'avatar_color' => '#0ea86c'],
            ['name' => 'Admin 3', 'email' => 'admin3@erp.local', 'avatar_color' => '#e0403a'],
        ];

        foreach ($admins as $a) {
            DB::table('users')->updateOrInsert(
                ['email' => $a['email']],
                [
                    'name' => $a['name'],
                    'role' => 'admin',
                    'avatar_color' => $a['avatar_color'],
                    'password' => Hash::make('password'), // change before production
                    'email_verified_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
