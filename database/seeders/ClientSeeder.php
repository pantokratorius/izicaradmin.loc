<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        Client::factory()->count(50) ->withVehicles(rand(1, 5))->create(); // создаст 50 клиентов
    }
}
