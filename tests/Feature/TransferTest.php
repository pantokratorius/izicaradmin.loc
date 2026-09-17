<?php

use App\Models\TransferAttachment;
use App\Models\TransferEntry;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('allows an administrator to open the transfer feed', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->get(route('transfer.index'))
        ->assertOk()
        ->assertSee('Передача');
});

it('forbids a non-administrator from using the transfer feed', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)->get(route('transfer.index'))->assertForbidden();
    $this->actingAs($user)->post(route('transfer.store'), ['body' => 'secret'])->assertForbidden();
});

it('stores text and private attachments', function () {
    Storage::fake('local');
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)->post(route('transfer.store'), [
        'body' => 'Copy this on the computer',
        'files' => [UploadedFile::fake()->create('report.xlsx', 120)],
    ])->assertRedirect(route('transfer.index'));

    $entry = TransferEntry::with('attachments')->firstOrFail();
    expect($entry->body)->toBe('Copy this on the computer')
        ->and($entry->user_id)->toBe($admin->id)
        ->and($entry->attachments)->toHaveCount(1)
        ->and($entry->attachments->first()->original_name)->toBe('report.xlsx');
    Storage::disk('local')->assertExists($entry->attachments->first()->path);
});

it('requires text or a file', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->post(route('transfer.store'))
        ->assertSessionHasErrors(['body', 'files']);
});

it('searches entry text and attachment names', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $textEntry = TransferEntry::create(['user_id' => $admin->id, 'body' => 'important phone note']);
    $fileEntry = TransferEntry::create(['user_id' => $admin->id, 'body' => null]);
    $fileEntry->attachments()->create([
        'path' => 'transfers/file',
        'original_name' => 'budget.xlsx',
        'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'size' => 100,
    ]);

    $this->actingAs($admin)->get(route('transfer.index', ['q' => 'phone']))
        ->assertOk()->assertSee($textEntry->body)->assertDontSee('budget.xlsx');
    $this->actingAs($admin)->get(route('transfer.index', ['q' => 'budget']))
        ->assertOk()->assertSee('budget.xlsx')->assertDontSee($textEntry->body);
});

it('downloads and deletes private attachments', function () {
    Storage::fake('local');
    $admin = User::factory()->create(['is_admin' => true]);
    $entry = TransferEntry::create(['user_id' => $admin->id, 'body' => null]);
    Storage::disk('local')->put('transfers/'.$entry->id.'/document.pdf', 'contents');
    $attachment = $entry->attachments()->create([
        'path' => 'transfers/'.$entry->id.'/document.pdf',
        'original_name' => 'document.pdf',
        'mime_type' => 'application/pdf',
        'size' => 8,
    ]);

    $this->actingAs($admin)->get(route('transfer.download', $attachment))->assertDownload('document.pdf');
    $this->actingAs($admin)->delete(route('transfer.destroy', $entry))->assertRedirect(route('transfer.index'));

    expect(TransferEntry::find($entry->id))->toBeNull()
        ->and(TransferAttachment::find($attachment->id))->toBeNull();
    Storage::disk('local')->assertMissing($attachment->path);
});
