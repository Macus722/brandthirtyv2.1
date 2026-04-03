<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::updateOrCreate(
            ['name' => 'superadmin'],
            [
                'email' => 'superadmin@brandthirty.com', // Added email to meet potential unique constraints
                'password' => \Illuminate\Support\Facades\Hash::make('123'),
                'role' => 'admin',
                'is_super_admin' => true,
            ]
        );
    }
}
