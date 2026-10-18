<?php

namespace Database\Seeders;

use App\Enums\Role;
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
        User::firstOrCreate(
            ['email' => 'aderaldo@mycare.com'],
            [
                'name' => 'Aderaldo Neto',
                'password' => Hash::make('123456789'),
                'role' => Role::Admin->value,
            ]
        );
    }
}
