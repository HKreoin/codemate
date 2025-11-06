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
        $users = [
            [
                'name' => 'Иван Иванов',
                'email' => 'ivan@example.com',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Мария Петрова',
                'email' => 'maria@example.com',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Алексей Сидоров',
                'email' => 'alex@example.com',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Елена Козлова',
                'email' => 'elena@example.com',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Дмитрий Волков',
                'email' => 'dmitry@example.com',
                'password' => Hash::make('password'),
            ],
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }
    }
}
