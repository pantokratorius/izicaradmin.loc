<?php

namespace App\Http\Controllers;

use App\Models\TransferAttachment;
use App\Models\TransferEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

        return view('transfer.index', compact('entries', 'search'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'body' => ['nullable', 'required_without:files', 'string', 'max:100000'],
            'files' => ['nullable', 'required_without:body', 'array', 'max:10'],
            'files.*' => ['file', 'max:51200'],
        ]);

        DB::transaction(function () use ($request, $validated) {
            $entry = TransferEntry::create([
                'user_id' => $request->user()->id,
                'body' => $validated['body'] ?? null,
            ]);

            foreach ($request->file('files', []) as $file) {
                $path = $file->store('transfers/'.$entry->id, 'local');
                $entry->attachments()->create([
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
            }
        });

        return to_route('transfer.index')->with('success', 'Запись сохранена');
    }

    public function download(TransferAttachment $attachment): StreamedResponse
    {
        abort_unless(Storage::disk('local')->exists($attachment->path), 404);

        return Storage::disk('local')->download($attachment->path, $attachment->original_name);
    }

    public function destroy(TransferEntry $entry): RedirectResponse
    {
        DB::transaction(function () use ($entry) {
            foreach ($entry->attachments as $attachment) {
                Storage::disk('local')->delete($attachment->path);
            }
            Storage::disk('local')->deleteDirectory('transfers/'.$entry->id);
            $entry->delete();
        });

        return to_route('transfer.index')->with('success', 'Запись удалена');
    }
}
