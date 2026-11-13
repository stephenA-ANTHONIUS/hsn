<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'tpenn@gmail.com'],
            [
                'name' => 'Kepala Toko',
                'password' => Hash::make('tpenn123'),
                'role' => 'kepala_toko',
            ]
        );
    }
}
