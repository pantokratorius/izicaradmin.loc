<?php

use App\Jobs\DeleteTransferFiles;
use App\Models\TransferAttachment;
use App\Models\TransferEntry;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Queue;

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

it('removes stored files when the database transaction fails', function () {
    Storage::fake('local');
    $admin = User::factory()->create(['is_admin' => true]);
    TransferAttachment::creating(fn () => throw new RuntimeException('Database insert failed'));
    $this->withoutExceptionHandling();

    try {
        $this->actingAs($admin)->post(route('transfer.store'), [
            'files' => [UploadedFile::fake()->create('orphan.pdf', 10)],
        ]);
    } catch (RuntimeException $exception) {
        expect($exception->getMessage())->toBe('Database insert failed');
    } finally {
        TransferAttachment::flushEventListeners();
    }

    expect(TransferEntry::count())->toBe(0);
    expect(Storage::disk('local')->allFiles('transfers'))->toBeEmpty();
});

it('requires text or a file', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->post(route('transfer.store'))
        ->assertSessionHasErrors(['body', 'files']);
});

it('displays transfer validation errors', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->followingRedirects()
        ->post(route('transfer.store'))
        ->assertOk()
        ->assertSee('Не удалось сохранить запись:');
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
    Queue::fake();
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
    Storage::disk('local')->assertExists($attachment->path);
    Queue::assertPushed(DeleteTransferFiles::class, fn ($job) => $job->paths === [$attachment->path]);
});

it('keeps files when deleting the database entry fails', function () {
    Storage::fake('local');
    Queue::fake();
    $admin = User::factory()->create(['is_admin' => true]);
    $entry = TransferEntry::create(['user_id' => $admin->id, 'body' => null]);
    $path = 'transfers/'.$entry->id.'/document.pdf';
    Storage::disk('local')->put($path, 'contents');
    $entry->attachments()->create([
        'path' => $path,
        'original_name' => 'document.pdf',
        'mime_type' => 'application/pdf',
        'size' => 8,
    ]);
    TransferEntry::deleting(fn () => throw new RuntimeException('Database delete failed'));
    $this->withoutExceptionHandling();

    try {
        $this->actingAs($admin)->delete(route('transfer.destroy', $entry));
    } catch (RuntimeException $exception) {
        expect($exception->getMessage())->toBe('Database delete failed');
    } finally {
        TransferEntry::flushEventListeners();
    }

    expect(TransferEntry::find($entry->id))->not->toBeNull();
    Storage::disk('local')->assertExists($path);
    Queue::assertNothingPushed();
});

it('deletes files in the cleanup job', function () {
    Storage::fake('local');
    Storage::disk('local')->put('transfers/1/document.pdf', 'contents');

    (new DeleteTransferFiles(['transfers/1/document.pdf']))->handle();

    Storage::disk('local')->assertMissing('transfers/1/document.pdf');
});
