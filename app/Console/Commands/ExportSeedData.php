<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ExportSeedData extends Command
{
    protected $signature = 'db:export-seeds {tables*}';
    protected $description = 'Export tables data to seed files';

    public function handle()
    {
        $tables = $this->argument('tables');

        foreach ($tables as $table) {
            $modelClass = $this->guessModel($table);

            if (!class_exists($modelClass)) {
                $this->error("Model for table {$table} not found: {$modelClass}");
                continue;
            }

            $data = $modelClass::all()->toArray();

            if (!File::exists(database_path('seeders/data'))) {
                File::makeDirectory(database_path('seeders/data'), 0755, true);
            }

            $filePath = database_path("seeders/data/{$table}.php");
            File::put($filePath, '<?php return ' . var_export($data, true) . ';');

            $this->info("Exported {$table} to {$filePath}");
        }

        $this->info('All done!');
    }

    protected function guessModel($table)
    {
        // naive guess: table name singular and StudlyCase
        $modelName = str_replace(' ', '', ucwords(str_replace('_', ' ', rtrim($table, 's'))));
        return "App\\Models\\{$modelName}";
    }
}
