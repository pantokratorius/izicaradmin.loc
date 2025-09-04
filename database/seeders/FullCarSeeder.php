<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class FullCarSeeder extends Seeder
{
    public function run()
    {
        $this->seedTable('car_brands', 'car_brands.csv', ['id', 'name']);
        $this->seedTable('car_models', 'car_models.csv', ['id', 'car_brand_id', 'name']);
        $this->seedTable('car_generations', 'car_generations.csv', ['id', 'car_model_id', 'name', 'year_begin', 'year_end']);
        $this->seedTable('car_series', 'car_series.csv', ['id', 'car_model_id', 'car_generation_id', 'name']);
        $this->seedTable('car_modifications', 'car_modifications.csv', ['id', 'car_model_id', 'car_serie_id', 'name', 'start_production_year', 'end_production_year']);
    }

    private function seedTable($table, $csvFile, $columns)
    {
        $file = database_path("seeders/csv/$csvFile");

        if (!file_exists($file)) {
            $this->command->error("File not found: $file");
            return;
        }

        $data = array_map('str_getcsv', file($file));
        $header = array_map('trim', array_shift($data));

        foreach ($data as $row) {
            $rowData = array_combine($header, $row);

            // Clean NULLs and empty strings
            $rowData = array_map(fn($v) => ($v === 'NULL' || $v === '') ? null : trim($v), $rowData);

            // Build insert data
            $insert = [];
            foreach ($columns as $col) {
                $insert[$col] = $rowData[$col] ?? null;
            }

            // Add timestamps if table has them
            if (in_array('created_at', $header)) {
                $insert['created_at'] = $rowData['created_at'] ?? Carbon::now();
                $insert['updated_at'] = $rowData['updated_at'] ?? Carbon::now();
            }

            DB::table($table)->updateOrInsert(['id' => $insert['id']], $insert);
        }

        $this->command->info("$table seeded successfully!");
    }
}



// php artisan db:seed --class=FullCarSeeder