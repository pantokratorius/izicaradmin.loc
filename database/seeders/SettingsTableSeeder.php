<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('settings')->insert([
            array (
  'id' => 1,
  'margin' => '20.00',
  'created_at' => '2025-09-16 09:06:28',
  'updated_at' => '2025-09-16 09:06:28',
)
        ]);
    }
}
