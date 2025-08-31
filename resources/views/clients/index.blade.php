@extends('layouts.app')

@section('title', 'Клиенты - Контакты')

@section('content')
<div class="page-header">
  <div>Клиенты > Контакты</div>
  <div style="display: flex; gap: 10px; align-items: center;">
    <form method="GET" action="{{ route('clients.index') }}">
      <input type="text" name="search" class="search-box"
             placeholder="Поиск по наименованию и номеру телефона"
             value="{{ request('search') }}">
    </form>
    <a href="{{ route('clients.create') }}" class="btn btn-add">+ Добавить</a>
  </div>
</div>

@if(session('success'))
  <div style="background: #d4edda; color: #155724; padding: 10px 15px; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 15px;">
    {{ session('success') }}
  </div>
@endif

@if(session('error'))
  <div style="background: #f8d7da; color: #721c24; padding: 10px 15px; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 15px;">
    {{ session('error') }}
  </div>
@endif

<table>
  <thead>
    <tr>
      <th>№</th>
      <th>Полное имя</th>
      <th>Сделки</th>
      <th>Количество</th>
      <th>Способ связи</th>
      <th>Основной телефон</th>
      <th>Дата рождения</th>
      <th>Действия</th>
    </tr>
  </thead>
  <tbody>
    @forelse($clients as $client)
      <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ $client->full_name }}</td>
        <td>{{ $client->deals_count ?? '-' }}</td>
        <td>{{ $client->orders_count ?? '-' }}</td>
        <td>{{ $client->contact_method ?? 'Нет' }}</td>
        <td>{{ $client->phone }}</td>
        <td>{{ $client->birth_date ?? '' }}</td>
        <td>
          <a href="{{ route('clients.edit', $client->id) }}" class="btn btn-edit">✏ Редактировать</a>
          <form action="{{ route('clients.destroy', $client->id) }}" method="POST" style="display:inline;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-delete" onclick="return confirm('Удалить клиента?')">🗑 Удалить</button>
          </form>
        </td>
      </tr>
    @empty
      <tr>
        <td colspan="8" style="text-align: center;">Нет данных</td>
      </tr>
    @endforelse
  </tbody>
</table>
@endsection
