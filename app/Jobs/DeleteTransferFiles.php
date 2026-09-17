<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class DeleteTransferFiles implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * @param  array<int, string>  $paths
     */
    public function __construct(public array $paths)
    {
    }

    public function handle(): void
    {
        foreach ($this->paths as $path) {
            if (Storage::disk('local')->exists($path) && ! Storage::disk('local')->delete($path)) {
                throw new RuntimeException("Unable to delete transfer file: {$path}");
            }
        }
    }

    public function backoff(): array
    {
        return [10, 60, 300];
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Transfer file cleanup failed after all retries.', [
            'paths' => $this->paths,
            'exception' => $exception,
        ]);
    }
}
