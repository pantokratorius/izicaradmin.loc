<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class GenerateSeeders extends Command
{
    protected $signature = 'generate:seeders';
    protected $description = 'Generate seeders for all tables from database';

    public function handle()
    {
        $tables = $this->getTables();

        foreach ($tables as $table) {
            $this->info("Processing table: $table");

            $modelClass = $this->guessModel($table);

            $data = DB::table($table)->get();

            $seederClassName = Str::studly($table) . 'TableSeeder';
            $seederPath = database_path("seeders/{$seederClassName}.php");

            $seederContent = $this->generateSeederContent($table, $data, $seederClassName);
            File::put($seederPath, $seederContent);

            $this->info("Seeder created: $seederClassName");
        }

        $this->info("All seeders generated successfully!");
    }

    protected function getTables()
    {
        $database = env('DB_DATABASE');
        $driver = env('DB_CONNECTION');

        if ($driver === 'mysql') {
            $tables = DB::select("SHOW TABLES");
            $key = "Tables_in_{$database}";
            return array_map(fn($t) => $t->$key, $tables);
        }

        $this->error("Database driver $driver not supported yet.");
        return [];
    }

    protected function guessModel($table)
    {
        $modelName = Str::studly(Str::singular($table));
        return "App\\Models\\{$modelName}";
    }

    protected function generateSeederContent($table, $data, $className)
    {
        $insertData = $data->map(function($row) {
            $rowArray = (array) $row;
            $rowExport = var_export($rowArray, true);
            return $rowExport;
        })->implode(",\n");

        return <<<PHP
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class {$className} extends Seeder
{
    public function run()
    {
        DB::table('{$table}')->insert([
            {$insertData}
        ]);
    }
}

PHP;
    }
}
