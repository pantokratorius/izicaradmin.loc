@extends('layouts.app')

@section('title', 'Клиенты - Контакты')

@section('content')
<div class="page-header">
  <div>Клиенты</div>
  <div style="display: flex; gap: 10px; align-items: center;">
    <form method="GET" action="{{ route('vehicles.index') }}">
      <input type="text" name="search" class="search-box"
             placeholder="Поиск по наименованию и номеру телефона"
             value="{{ request('search') }}">
    </form>
    <a href="{{ route('vehicles.create') }}" class="btn btn-add">+ Добавить</a>
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
          <th>Клиент</th>
          <th>Тип</th>
          <th>Бренд</th>
        <th>Модель</th>
        <th>Поколение</th>
        <th>Серия</th>
        <th>Кузов</th>
        <th>Модификация</th>
          <th>Гос номер</th>
        <th>СТС</th>
        <th>ПТС</th>
        <th>Год</th>
        <th>Тип двигателя</th>
        </tr>
      </thead>
      <tbody>
        @forelse($vehicles as $vehicle)
          <tr class="vehicle-row" data-id="{{ $vehicle->id }}">
            <td>{{ $loop->iteration }}</td>
            <td>{{ $vehicle->client->first_name ?? '-' }} {{ $vehicle->client->middle_name ?? '' }} {{ $vehicle->client->last_name ?? '' }}</td>
            <td>{{ $vehicle->vehicle_type }}</td>
            <td>{{ $vehicle->brand->name ?? $vehicle->brand_name ?? '-' }}</td>
            <td>{{ $vehicle->model->name ?? $vehicle->model_name ?? '-' }}</td>
            <td>{{ $vehicle->generation->name ?? $vehicle->generation_name ?? '-' }}</td>
            <td>{{ $vehicle->serie->name ?? $vehicle->serie_name ?? '-' }}</td>
            <td>{{ $vehicle->body ?? '-' }}</td>
            <td>{{ $vehicle->modification->name ??  $vehicle->modification_name ?? '-' }}</td>
            <td>{{ $vehicle->registration_number ?? '-' }}</td>
            <td>{{ $vehicle->sts ?? '-' }}</td>
            <td>{{ $vehicle->pts ?? '-' }}</td>
            <td>{{ $vehicle->year_of_manufacture }}</td>
            <td>{{ $vehicle->engine_type }}</td>
        </tr>
        @empty
          <tr>
            <td colspan="8" style="text-align: center;">Нет данных</td>
          </tr>
        @endforelse
      </tbody>
    </table>

<x-pagination :paginator="$vehicles" />

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const rows = document.querySelectorAll('.vehicle-row');

        rows.forEach(row => {
            row.addEventListener('click', function() {
                const vehicleId = this.dataset.id;
                // Open vehicle edit page
                window.location.href = `/vehicles/${vehicleId}/edit`;
            });
        });
    });
</script>


@endsection
