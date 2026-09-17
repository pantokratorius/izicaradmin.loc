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
  .delete-all-button {
  border: 0;
  border-radius: 8px;
  padding: 10px 15px;
  background: #dc2626;
  color: #fff;
  font-weight: 700;
  cursor: pointer;
}

.delete-all-button:hover {
  background: #b91c1c;
}
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

    @if($entries->total() > 0)
        <form method="POST" action="{{ route('transfer.destroyAll') }}" onsubmit="return confirm('Удалить все записи и все файлы? Это действие нельзя отменить.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="delete-all-button">Очистить всё</button>
        </form>
    @endif
</div>

  <form class="transfer-card transfer-compose" method="POST" action="{{ route('transfer.store') }}" enctype="multipart/form-data">
    @csrf
    @if($errors->any())
      <div role="alert" style="margin-bottom:12px; padding:10px 12px; border:1px solid #f3b7bd; border-radius:8px; background:#fff1f2; color:#9f1239;">
        <strong>Не удалось сохранить запись:</strong>
        <ul style="margin:6px 0 0; padding-left:20px;">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif
    <textarea name="body" placeholder="Вставьте текст, ссылку или заметку…">{{ old('body') }}</textarea>
    <div class="transfer-actions">
      <input class="file-picker" type="file" name="files[]" multiple>
      <button class="send-button" type="submit">Отправить</button>
    </div>
    <p class="upload-note">
      До 10 файлов, не более {{ $maximumFileSizeMb }} МБ каждый.
      @if($maximumRequestSizeMb > 0)
        Общий лимит одного запроса на сервере — {{ $maximumRequestSizeMb }} МБ.
      @endif
      Можно загружать документы, архивы, изображения и другие обычные файлы.
    </p>
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
<style>
  .sidebar {
    display: none;
  }
</style>
<script>
document.addEventListener('click', async function (e) {
    const button = e.target.closest('[data-copy]');
    if (!button) return;

    const body = document.getElementById(`entry-body-${button.dataset.copy}`);
    if (!body) return;

    const text = body.innerText || body.textContent || '';

    try {
        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(text);
        } else {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.left = '-9999px';
            textarea.style.top = '0';
            document.body.appendChild(textarea);
            textarea.focus();
            textarea.select();

            const ok = document.execCommand('copy');
            document.body.removeChild(textarea);

            if (!ok) {
                throw new Error('Copy command failed');
            }
        }

        const oldText = button.textContent;
        button.textContent = 'Скопировано';
        setTimeout(() => {
            button.textContent = oldText;
        }, 1400);
    } catch (error) {
        console.error('Copy failed:', error);

        const range = document.createRange();
        range.selectNodeContents(body);
        const selection = window.getSelection();
        selection.removeAllRanges();
        selection.addRange(range);

        alert('Не удалось скопировать автоматически. Текст выделен — нажмите Ctrl+C.');
    }
});
</script>
@endsection
