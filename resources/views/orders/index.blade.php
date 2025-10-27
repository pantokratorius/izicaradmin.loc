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
        <th></th>
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
              <button  onclick="openOrderModal({{ $order->id }})"
                          style="btn btn-sm btn-warning;  cursor: pointer">
                      ✏
                  </button>
                      <a href="{{ route('orders.copy', $order->id) }}" 
                        class="btn btn-secondary" style="margin: 0 5px;"
                        onclick="return confirm('Скопировать этот заказ?')">
                        📄
                    </a>
            <form
                  action="{{ route('orders.destroy', $order->id) }}"
                  method="POST" style="">
                    @csrf
                    @method('DELETE')
                    <button onclick="if(!confirm('Удалить заказ?')) return false" style="btn btn-sm btn-danger; cursor: pointer">🗑</button>
                </form>
            </td>
            <td>
              <select onchange="openPrint(this, {{ $order->id }})" class="print-select">
                    <option value="">🖨️ Печать...</option>
                    <option value="{{ route('orders.print', $order->id) }}">Заказ 1</option>
                    <option value="{{ route('orders.print2', $order->id) }}">Заказ 2</option>
                </select>
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
<style>
.print-select {
    display: inline-block;
    width: auto;
    padding: 6px 12px;
    border-radius: 4px;
    background-color: #6c757d; /* как .btn-secondary */
    color: #fff;
    border: 1px solid #6c757d;
    cursor: pointer;
    font-weight: 500;
    appearance: none; /* убираем стандартную стрелку */
    background-image: url("data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%23fff' viewBox='0 0 16 16'%3E%3Cpath d='M1.5 5.5l6 6 6-6'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    background-size: 1rem;
    padding-right: 2rem;
}
.print-select:hover {
    background-color: #5a6268;
    border-color: #545b62;
}
</style>
<script>
function openPrint(select, orderId) {
    if (select.value) {
        window.open(select.value, '_blank');
        select.selectedIndex = 0; // сбрасываем обратно на первый вариант
    }
}
</script>
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
