<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;

class ClientSeeder extends Seeder
{
    public function run()
    {
        $clients = include database_path('seeders/data/clients.php');

        foreach ($clients as $client) {
            Client::updateOrCreate(['id' => $client['id']], $client);
        }
    }
}

