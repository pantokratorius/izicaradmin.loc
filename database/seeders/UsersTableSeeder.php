<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->insert([
            array (
  'id' => 1,
  'name' => 'Admin',
  'email' => 'admin@mail.ru',
  'email_verified_at' => NULL,
  'password' => '$2y$12$f.xnPVnwFHyVU79JTyFdUO9txzZeXc/KtuOmVZDsxtgmLsELIiMdu',
  'remember_token' => NULL,
  'created_at' => '2025-09-16 09:04:08',
  'updated_at' => '2025-09-16 09:04:08',
  'is_admin' => 1,
)
        ]);
    }
}
