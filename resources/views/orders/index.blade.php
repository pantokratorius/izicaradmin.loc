@extends('layouts.app')

@section('title', 'Клиенты - Контакты')

@section('content')
<div class="page-header">
  <div>Клиенты</div>
  <div style="display: flex; gap: 10px; align-items: center;">
    <form method="GET" action="{{ route('orders.index') }}">
      <input type="text" name="search" class="search-box"
             placeholder="Поиск по наименованию и номеру телефона"
             value="{{ request('search') }}">
    </form>
    <a href="{{ route('orders.create') }}" class="btn btn-add">+ Добавить</a>
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
          <th>Номер заказа</th>
        <th>Сумма</th>
        <th>Предоплата</th>
        <th>Статус</th>
        <th>Дата создания</th>
        <th>Автомобиль</th>
        <th>Менеджер</th>
        <th>Пробег</th>
        </tr>
      </thead>
      <tbody>
        @forelse($orders as $order)
          <tr class="order-row" data-id="{{ $order->id }}">
            <td>{{ $loop->iteration }}</td>
            <td onclick="toggleItems({{ $order->id }})">{{ $order->order_number }}</td>
            <td onclick="toggleItems({{ $order->id }})">{{ number_format($order->amount, 2, ',', ' ') }}</td>
            <td onclick="toggleItems({{ $order->id }})">{{ $order->prepayment }}</td>
            <td onclick="toggleItems({{ $order->id }})">{{ $order->status }}</td>
            <td onclick="toggleItems({{ $order->id }})">{{ $order->created_at  ?  $order->created_at->format('d.m.Y') : '' }}</td>
            <td onclick="toggleItems({{ $order->id }})">{{ $order->vehicle ? $order->vehicle->brand->name.' '.$order->vehicle->model->name : '-' }}</td>
            <td onclick="toggleItems({{ $order->id }})">{{ $order->manager ? $order->manager->name : '-' }}</td>
            <td onclick="toggleItems({{ $order->id }})">{{ $order->mileage ?? '-' }}</td>
        </tr>
        @empty
          <tr>
            <td colspan="8" style="text-align: center;">Нет данных</td>
          </tr>
        @endforelse
      </tbody>
    </table>

<x-pagination :paginator="$orders" />

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const rows = document.querySelectorAll('.order-row');

        rows.forEach(row => {
            row.addEventListener('click', function() {
                const orderId = this.dataset.id;
                // Open order edit page
                window.location.href = `/orders/${orderId}/edit`;
            });
        });
    });
</script>


@endsection
