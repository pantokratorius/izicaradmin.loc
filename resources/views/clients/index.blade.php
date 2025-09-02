@extends('layouts.app')

@section('title', 'Клиенты - Контакты')

@section('content')
<div class="page-header">
  <div>Клиенты</div>
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
          <th>Имя</th>
          <th>Фамилия</th>
          <th>Отчество</th>
          <th>Телефон</th>
          <th>Email</th>
          <th>Сегмент</th>
          <th>Скидка (%)</th>
        </tr>
      </thead>
      <tbody>
        @forelse($clients as $client)
          <tr class="client-row" data-id="{{ $client->id }}">
            <td>{{ $loop->iteration }}</td>
            <td>{{ $client->first_name }}</td>
            <td>{{ $client->last_name }}</td>
            <td>{{ $client->middle_name ?? '-' }}</td>
            <td>{{ $client->phone }}</td>
            <td>{{ $client->email }}</td>
            <td>{{ $client->segment ?? '-' }}</td>
            <td>{{ $client->discount }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="8" style="text-align: center;">Нет данных</td>
          </tr>
        @endforelse
      </tbody>
    </table>
 
<x-pagination :paginator="$clients" />

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const rows = document.querySelectorAll('.client-row');

        rows.forEach(row => {
            row.addEventListener('click', function() {
                const clientId = this.dataset.id;
                // Open client edit page
                window.location.href = `/clients/${clientId}/edit`;
            });
        });
    });
</script>


@endsection
