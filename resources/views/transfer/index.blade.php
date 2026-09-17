@extends('layouts.app')

@section('title', 'Передача')

@section('content')
<style>
  .transfer-wrap { max-width: 900px; margin: 0 auto; }
  .transfer-title { display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom:18px; }
  .transfer-title h1 { margin:0; font-size:25px; color:#14213d; }
  .transfer-title p { margin:5px 0 0; color:#667085; font-size:14px; }
  .transfer-card { background:#fff; border:1px solid #e7ebf0; border-radius:14px; padding:18px; margin-bottom:16px; box-shadow:0 3px 12px rgba(20,33,61,.05); }
  .transfer-compose textarea { box-sizing:border-box; width:100%; min-height:120px; resize:vertical; border:1px solid #cfd6df; border-radius:10px; padding:13px; font:15px/1.5 Arial,sans-serif; }
  .transfer-compose textarea:focus, .transfer-search input:focus { outline:2px solid #93c5fd; border-color:#2563eb; }
  .transfer-actions { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-top:12px; }
  .file-picker { max-width:100%; color:#475467; }
  .file-picker::file-selector-button { border:0; border-radius:7px; padding:8px 12px; margin-right:9px; cursor:pointer; background:#eef2f7; color:#14213d; }
  .send-button { border:0; border-radius:8px; padding:10px 22px; background:#2563eb; color:white; font-weight:700; cursor:pointer; }
  .send-button:hover { background:#1d4ed8; }
  .upload-note { margin:10px 0 0; color:#7b8494; font-size:12px; }
  .transfer-search { display:flex; gap:8px; margin:18px 0; }
  .transfer-search input { box-sizing:border-box; flex:1; min-width:0; border:1px solid #cfd6df; border-radius:9px; padding:10px 12px; font-size:14px; }
  .search-button, .clear-link { border:0; border-radius:8px; padding:10px 15px; background:#14213d; color:#fff; text-decoration:none; cursor:pointer; }
  .clear-link { background:#e9edf3; color:#344054; }
  .entry-meta { display:flex; justify-content:space-between; gap:12px; color:#7b8494; font-size:12px; margin-bottom:12px; }
  .entry-body { white-space:pre-wrap; overflow-wrap:anywhere; font-size:15px; line-height:1.55; color:#202939; }
  .entry-tools { display:flex; gap:8px; align-items:center; }
  .copy-button, .delete-button { border:0; background:none; color:#2563eb; padding:0; cursor:pointer; font-size:12px; }
  .delete-button { color:#c53030; }
  .attachment-list { display:grid; gap:8px; margin-top:14px; }
  .attachment { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:10px 12px; background:#f6f8fb; border-radius:9px; }
  .attachment-info { min-width:0; }
  .attachment-name { display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; color:#1f2937; font-size:14px; }
  .attachment-size { color:#8992a1; font-size:11px; }
  .download-link { flex:none; color:#2563eb; text-decoration:none; font-size:13px; font-weight:600; }
  .empty-feed { text-align:center; color:#7b8494; padding:35px 15px; }
  .transfer-pages nav { margin-top:18px; }
  @media (max-width: 700px) {
    .sidebar { display:none; }
    .main { margin-left:0; padding:12px; }
    .transfer-card { padding:14px; border-radius:11px; }
    .transfer-title h1 { font-size:22px; }
    .transfer-actions { align-items:stretch; flex-direction:column; }
    .send-button { width:100%; }
    .transfer-search { flex-wrap:wrap; }
    .transfer-search input { flex-basis:100%; }
  }
</style>

<div class="transfer-wrap">
  <div class="transfer-title">
    <div>
      <h1>Передача</h1>
      <p>Тексты и файлы для быстрого доступа с другого устройства</p>
    </div>
  </div>

  <form class="transfer-card transfer-compose" method="POST" action="{{ route('transfer.store') }}" enctype="multipart/form-data">
    @csrf
    <textarea name="body" placeholder="Вставьте текст, ссылку или заметку…">{{ old('body') }}</textarea>
    <div class="transfer-actions">
      <input class="file-picker" type="file" name="files[]" multiple>
      <button class="send-button" type="submit">Отправить</button>
    </div>
    <p class="upload-note">До 10 файлов, не более 50 МБ каждый. Можно загружать документы, архивы, изображения и другие обычные файлы.</p>
  </form>

  <form class="transfer-search" method="GET" action="{{ route('transfer.index') }}">
    <input type="search" name="q" value="{{ $search }}" placeholder="Поиск по тексту или имени файла" aria-label="Поиск">
    <button class="search-button" type="submit">Найти</button>
    @if($search !== '')
      <a class="clear-link" href="{{ route('transfer.index') }}">Сбросить</a>
    @endif
  </form>

  @forelse($entries as $entry)
    <article class="transfer-card">
      <div class="entry-meta">
        <time datetime="{{ $entry->created_at->toIso8601String() }}">{{ $entry->created_at->format('d.m.Y H:i') }}</time>
        <div class="entry-tools">
          @if(filled($entry->body))
            <button type="button" class="copy-button" data-copy="{{ $entry->id }}">Копировать</button>
          @endif
          <form method="POST" action="{{ route('transfer.destroy', $entry) }}" onsubmit="return confirm('Удалить эту запись и все файлы?')">
            @csrf
            @method('DELETE')
            <button class="delete-button" type="submit">Удалить</button>
          </form>
        </div>
      </div>

      @if(filled($entry->body))
        <div class="entry-body" id="entry-body-{{ $entry->id }}">{{ $entry->body }}</div>
      @endif

      @if($entry->attachments->isNotEmpty())
        <div class="attachment-list">
          @foreach($entry->attachments as $attachment)
            <div class="attachment">
              <div class="attachment-info">
                <span class="attachment-name" title="{{ $attachment->original_name }}">📎 {{ $attachment->original_name }}</span>
                <span class="attachment-size">{{ Number::fileSize($attachment->size) }}</span>
              </div>
              <a class="download-link" href="{{ route('transfer.download', $attachment) }}">Скачать</a>
            </div>
          @endforeach
        </div>
      @endif
    </article>
  @empty
    <div class="transfer-card empty-feed">
      {{ $search !== '' ? 'Ничего не найдено.' : 'Пока нет записей. Отправьте первый текст или файл.' }}
    </div>
  @endforelse

  <div class="transfer-pages">{{ $entries->links() }}</div>
</div>

<script>
  document.querySelectorAll('[data-copy]').forEach((button) => {
    button.addEventListener('click', async () => {
      const body = document.getElementById(`entry-body-${button.dataset.copy}`);
      try {
        await navigator.clipboard.writeText(body.innerText);
        const label = button.textContent;
        button.textContent = 'Скопировано';
        setTimeout(() => button.textContent = label, 1400);
      } catch (error) {
        window.getSelection().selectAllChildren(body);
      }
    });
  });
</script>
@endsection
