@extends('layouts.app')

@section('title', 'Клиенты - Контакты')

@php
    $status = [
       1 => 'Новый',
            'В работе',
            'Пришел',
            'Выдан',
            'Отменен'
    ];
@endphp

@section('content')
<div class="page-header">
  <div>Заказы</div>
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
          <th>№ заказа</th>
        <th>Сумма</th>
        <th>Предоплата</th>
        <th>Статус</th>
        <th>Дата создания</th>
        <th>Клиент</th>
        <th>Автомобиль</th>
        <th>Менеджер</th>
        <th>Пробег</th>
        <th style="width: 50px"></th>
        </tr>
      </thead>
      <tbody>
        @forelse($orders as $order)
          <tr class="order-row" data-id="{{ $order->id }}">
            <td>{{ $loop->iteration }}</td>
            <td onclick="toggleItems({{ $order->id }})">{{ $order->order_number }}</td>
            <td onclick="toggleItems({{ $order->id }})">{{ number_format($order->amount, 2, ',', ' ') }}</td>
            <td onclick="toggleItems({{ $order->id }})">{{ $order->prepayment }}</td>
            <td>
                 <select class="status_select"  data-id="{{ $order->id }}" style="padding: 3px 0">
                                    @foreach ($status as $key => $st)
                                        <option value="{{$key}}" {{ $order->status == $key ? 'selected' : '' }}>{{$st}}</option>
                                    @endforeach
                                </select>
            </td>
            <td onclick="toggleItems({{ $order->id }})">{{ $order->created_at  ?  $order->created_at->format('d.m.Y') : '' }}</td>
            <td onclick="toggleItems({{ $order->id }})">{{ $order->client?->first_name }} {{ $order->client?->middle_name }} {{ $order->client?->last_name }}</td>
            <td onclick="toggleItems({{ $order->id }})">{{ $order->vehicle?->brand?->name ??  $order->vehicle?->brand_name .' '. $order->vehicle?->model?->name  ?? $order->vehicle?->model_name }}</td>
            <td onclick="toggleItems({{ $order->id }})">{{ $order->manager ? $order->manager->name : '-' }}</td>
            <td onclick="toggleItems({{ $order->id }})">{{ $order->mileage ?? '-' }}</td>
            <td style="display: flex; align-items: center; justify-content: center">
                      <a href="{{ route('orders.copy', $order->id) }}" 
                        class="btn btn-secondary"
                        onclick="return confirm('Скопировать этот заказ?')">
                        📄
                    </a>
                <button  onclick="openOrderModal({{ $order->id }})"
                            style="btn btn-sm btn-warning; margin: 0 5px; cursor: pointer">
                        ✏
                    </button>
            <form
                  action="{{ route('orders.destroy', $order->id) }}"
                  method="POST" style="">
                    @csrf
                    @method('DELETE')
                    <button onclick="if(!confirm('Удалить заказ?')) return false" style="btn btn-sm btn-danger; cursor: pointer">🗑</button>
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

<x-pagination :paginator="$orders" />

<script>

document.addEventListener('change', function (e) {
    if (e.target.classList.contains('status_select')) {
        const select = e.target;
        const orderId = select.dataset.id;
        const newStatus = select.value;

        fetch(`/orders/${orderId}/status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({ status: newStatus })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                select.style.backgroundColor = '#d4edda';
                setTimeout(() => select.style.backgroundColor = '', 800);
            } else {
                alert('Ошибка при обновлении статуса');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Ошибка сети');
        });
    }
});

function toggleItems(orderId) { 
  window.location.href = "{{ route('orders.show', ':id') }}".replace(':id', orderId);
}

function openOrderModal(orderId) {
  window.location.href = "{{ route('orders.edit', ':id') }}".replace(':id', orderId);
}

    // document.addEventListener('DOMContentLoaded', function() {
    //     const rows = document.querySelectorAll('.order-row');

    //     rows.forEach(row => {
    //         row.addEventListener('click', function() {
    //             const orderId = this.dataset.id;
    //             // Open order edit page
    //             window.location.href = `/orders/${orderId}/edit`;
    //         });
    //     });
    // });
</script>
<style>
  .btn { padding: 5px 10px; background: #14213d; color: #fff; border-radius: 4px; text-decoration: none; margin-right: 0px; display: inline-block; }
</style>


@endsection
