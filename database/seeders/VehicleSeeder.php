<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vehicle;

class VehicleSeeder extends Seeder
{
    public function run()
    {
        $clients = include database_path('seeders/data/clients.php');

        foreach ($clients as $client) {
            Vehicle::updateOrCreate(['id' => $client['id']], $client);
        }
    }
}
