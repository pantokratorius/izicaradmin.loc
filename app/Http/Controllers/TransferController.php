<?php

namespace App\Http\Controllers;

use App\Jobs\DeleteTransferFiles;
use App\Models\TransferAttachment;
use App\Models\TransferEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class TransferController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));
        $entries = TransferEntry::query()
            ->with('attachments')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('body', 'like', "%{$search}%")
                        ->orWhereHas('attachments', fn ($query) => $query->where('original_name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $maximumFileSizeMb = (int) floor($this->maximumFileSizeKilobytes() / 1024);
        $maximumRequestSizeMb = $this->iniSizeInMegabytes(ini_get('post_max_size'));

        return view('transfer.index', compact('entries', 'search', 'maximumFileSizeMb', 'maximumRequestSizeMb'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'body' => ['nullable', 'required_without:files', 'string', 'max:100000'],
            'files' => ['nullable', 'required_without:body', 'array', 'max:10'],
            'files.*' => ['file', 'max:'.$this->maximumFileSizeKilobytes()],
        ]);

        $storedPaths = [];

        try {
            DB::transaction(function () use ($request, $validated, &$storedPaths) {
                $entry = TransferEntry::create([
                    'user_id' => $request->user()->id,
                    'body' => $validated['body'] ?? null,
                ]);

                foreach ($request->file('files', []) as $file) {
                    $path = $file->store('transfers/'.$entry->id, 'local');
                    throw_if($path === false, new RuntimeException('Unable to store transfer file.'));
                    $storedPaths[] = $path;
                    $entry->attachments()->create([
                        'path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                    ]);
                }
            });
        } catch (Throwable $exception) {
            foreach ($storedPaths as $path) {
                if (! Storage::disk('local')->delete($path)) {
                    Log::error('Unable to clean up a transfer file after a failed transaction.', [
                        'path' => $path,
                        'exception' => $exception,
                    ]);
                }
            }

            throw $exception;
        }

        return to_route('transfer.index')->with('success', 'Запись сохранена');
    }

    public function download(TransferAttachment $attachment): StreamedResponse
    {
        abort_unless(Storage::disk('local')->exists($attachment->path), 404);

        return Storage::disk('local')->download($attachment->path, $attachment->original_name);
    }

    public function destroy(TransferEntry $entry): RedirectResponse
    {
        $paths = $entry->attachments()->pluck('path')->all();

        DB::transaction(function () use ($entry) {
            $entry->delete();
        });

        DeleteTransferFiles::dispatch($paths);

        return to_route('transfer.index')->with('success', 'Запись удалена');
    }

    private function maximumFileSizeKilobytes(): int
    {
        $applicationLimit = 50 * 1024;
        $serverLimit = $this->iniSizeInKilobytes(ini_get('upload_max_filesize'));

        return $serverLimit > 0 ? min($applicationLimit, $serverLimit) : $applicationLimit;
    }

    private function iniSizeInMegabytes(string|false $value): int
    {
        return (int) floor($this->iniSizeInKilobytes($value) / 1024);
    }

    private function iniSizeInKilobytes(string|false $value): int
    {
        if ($value === false || trim($value) === '') {
            return 0;
        }

        $value = trim($value);
        $number = (float) $value;

        return match (strtolower(substr($value, -1))) {
            'g' => (int) ($number * 1024 * 1024),
            'm' => (int) ($number * 1024),
            'k' => (int) $number,
            default => (int) ceil($number / 1024),
        };
    }

    public function destroyAll(): RedirectResponse
    {
        $paths = TransferAttachment::query()->pluck('path')->all();

        DB::transaction(function () {
            TransferEntry::query()->delete();
        });

        DeleteTransferFiles::dispatch($paths);

        return to_route('transfer.index')->with('success', 'Все записи удалены');
    }
}
