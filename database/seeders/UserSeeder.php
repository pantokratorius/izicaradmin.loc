<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@mail.ru'], // если уже есть такой email, обновит
            [
                'name' => 'Admin',
                'password' => Hash::make('qwerty12345'),
                'is_admin' => 1,
            ]
        );
    }
}
