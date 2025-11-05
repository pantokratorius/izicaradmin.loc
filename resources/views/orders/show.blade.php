@extends('layouts.app')

@section('title', 'Детали заказа №' . $order->id)

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
<div class="container py-4">
    <h1 class="mb-4">Заказ № {{ $order->order_number }}</h1>
    
    {{-- Информация о клиенте --}}
    <div class="card mb-4">
        <div class="card-header">Клиент</div>
        <div class="card-body" style="margin-left: 10px">
            <p><strong>Имя:</strong> {{ $order->client->first_name ?? $order->vehicle->client->first_name ??'—' }} {{ $order->client?->middle_name ?? $order->vehicle->client->middle_name ?? '' }} {{ $order->client?->last_name ?? $order->vehicle->client->last_name ?? '' }}</p>
            <p><strong>Email:</strong> {{ $order->client->email ?? $order->vehicle->client->email ?? '—' }}</p>
            <p><strong>Телефон:</strong> {{ $order->client->phone ?? $order->vehicle->client->phone ?? '—' }}</p>
        </div>
    </div>
<br>
    {{-- Информация о заказе --}}
    <div class="card mb-4">
        <div class="card-header">Информация о заказе</div>
        <div class="card-body" style="margin-left: 10px">
            <p><strong>Статус:</strong> {{ $status[$order->status] ?? '—' }}</p>
            <p><strong>Дата создания:</strong> {{ $order->created_at->format('d.m.Y H:i') }}</p>
        </div>
    </div>
<br>
    {{-- Позиции заказа --}}
    <div class="card" style="margin-bottom: 10px">
      <div class="card-header d-flex justify-content-between align-items-center">
    <span>Позиции</span>
    <button class="btn" style="background: #d7d7d7" onclick="location='{{route('orderitems.create',$order->id)}}'">
        ➕ Добавить позицию
    </button>
</div>
    </div>
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Номер детали</th>
                        <th>Производитель</th>
                        <th>Название</th>
                        <th>Закупка</th>
                        <th>Продажа</th>
                        <th>Количество</th>
                        <th>Сумма</th>
                        <th>Поставщик</th>
                        <th>Статус</th>
                        <th>Наценка %</th>
                        <th>Комментарий</th>
                        <th class="text-end"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($order->items as $item)
                    <tr id="item-row-{{ $item->id }}">
                        <td onclick='openItemModal({{ $order->id }}, @json($item))'>{{ $item->part_number }}</td>
                        <td onclick='openItemModal({{ $order->id }}, @json($item))'>{{ $item->part_make }}</td>
                        <td onclick='openItemModal({{ $order->id }}, @json($item))'>{{ $item->part_name }}</td>
                        <td onclick='openItemModal({{ $order->id }}, @json($item))'>{{ number_format($item->purchase_price, 2, ',', ' ') }}</td>
                        <td 
                            @if($item->sell_price > 0 )
                                style="background-color: #dcefff"
                            @endif
                        onclick='openItemModal({{ $order->id }}, @json($item))'>{{ $item->sell_price ? number_format($item->sell_price, 2, ',', ' ') : number_format($item->amount, 2, ',', ' ') }}</td>
                        <td onclick='openItemModal({{ $order->id }}, @json($item))'>{{ $item->quantity }}</td>
                        <td onclick='openItemModal({{ $order->id }}, @json($item))'>{{ number_format($item->summ, 2, ',', ' ') }}</td>
                        <td onclick='openItemModal({{ $order->id }}, @json($item))'>{{ $item->supplier }}</td>
                        <td> <select class="status_select"  data-id="{{ $item->id }}" style="padding: 3px 0">
                                    @foreach ($status as $key => $st)
                                        <option value="{{$key}}" {{ $item->status == $key ? 'selected' : '' }}>{{$st}}</option>
                                    @endforeach
                                </select></td>
                        <td 
                        @if($item->margin)
                        style="background-color: #dfffdc"
                        @endif
                        onclick='openItemModal({{ $order->id }}, @json($item))'>{{ $item->margin ?? $globalMargin }}</td>
                        <td class="text-end" onclick='openItemModal({{ $order->id }}, @json($item))'>
                            {{ $item->comment }}
                        </td>
                        <td ><button class="btn btn-sm btn-danger" onclick="deleteItem({{ $item->id }})">🗑</button></td>
                    </tr>
                    @empty

                        <tr>
                            <td colspan="10" class="text-center text-muted">Нет позиций в заказе</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
        <tr>
            <th colspan="3"></th>
            <th>{{ number_format($totalPurchasePrice, 2, ',', ' ') }}</th>
            <th>{{ number_format($totalSellPrice, 2, ',', ' ') }}</th>
            <th></th>
            <th>{{ number_format($totalPurchasePriceSumm, 2, ',', ' ') }}</th>
            <th colspan="5"></th>
        </tr>
    </tfoot>
            </table>
        </div>
    </div>

    {{-- Кнопка назад --}}
    <div style="margin: 30px 0 50px; display: flex; justify-content: space-between; ">
        <a href="#" onclick="window.history.back()" class="btn btn-secondary">← Назад к заказу</a>

        <select onchange="openPrint(this, {{ $order->id }})" class="print-select">
                    <option value="">🖨️ Печать...</option>
                    <option value="{{ route('orders.print', $order->id) }}">Заказ 1</option>
                    <option value="{{ route('orders.print2', $order->id) }}">Заказ 2</option>
                </select>
    </div>
</div>

<!-- Modal -->
<div id="itemModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeItemModal()">&times;</span>
        <h2 id="itemModalTitle">Добавить позицию</h2>

        <form id="itemForm">
            @csrf
            <input type="hidden" name="order_id" id="order_id">
            <input type="hidden" name="item_id" id="item_id">

            <label>Номер детали</label>
            <input type="text" name="part_number" id="part_number">

            <label>Производитель</label>
            <input type="text" name="part_make" id="part_make">

            <label>Название</label>
            <input type="text" name="part_name" id="part_name">

            <label>Закупка</label>
            <input type="number" step="0.01" name="purchase_price" id="purchase_price">
            
            <label>Продажа</label>
            <input type="number" step="0.01" name="sell_price" id="sell_price">


            <label>Поставщик</label>
            <input type="text" name="supplier" id="supplier">

            <label>Количество</label>
            <input type="number" name="quantity" id="quantity" value="1">

            <label>Статус</label>
            <input type="text" name="status" id="status">

            <label>Наценка %</label>
            <input type="text" name="margin" id="margin">

            <label>Комментарий</label>
            <input type="text" name="comment" id="comment">

            <div class="modal-actions">
                <button type="submit" class="btn btn-primary">Сохранить</button>
                <button type="button" class="btn btn-secondary" onclick="closeItemModal()">Отмена</button>
            </div>
        </form>
    </div>
</div>
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
<style>
/* Модалка */
.modal {
    display: none; 
    position: fixed;
    z-index: 9999;
    left: 0; top: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.5);
}

/* Контент */
.modal-content {
    background: #fff;
    margin: 5% auto;
    padding: 20px;
    border-radius: 8px;
    width: 600px;
    max-width: 95%;
    box-shadow: 0 5px 20px rgba(0,0,0,0.3);
}

/* Закрыть */
.modal .close {
    float: right;
    font-size: 24px;
    cursor: pointer;
    padding: 20px
}

/* Формы */
#itemForm label {
    display: block;
    margin-top: 10px;
    font-weight: bold;
}
#itemForm input {
    width: 100%;
    padding: 6px;
    margin-top: 4px;
    box-sizing: border-box;
}

.modal-actions {
    margin-top: 15px;
    text-align: right;
}
</style>



<script>

    function openPrint(select, orderId) {
    if (select.value) {
        window.open(select.value, '_blank');
        select.selectedIndex = 0; // сбрасываем обратно на первый вариант
    }
}

document.addEventListener('change', function (e) {
    if (e.target.classList.contains('status_select')) {
        const select = e.target;
        const orderId = select.dataset.id;
        const newStatus = select.value;

        fetch(`/orderitem/${orderId}/status` , {
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


function openItemModal(orderId, item = null) {
    document.getElementById('itemForm').reset();
    document.getElementById('order_id').value = orderId;

    if (item) {
        document.getElementById('itemModalTitle').innerText = 'Редактировать позицию';
        document.getElementById('item_id').value = item.id;
        document.getElementById('part_number').value = item.part_number;
        document.getElementById('part_make').value = item.part_make;
        document.getElementById('part_name').value = item.part_name;
        document.getElementById('purchase_price').value = item.purchase_price;
        document.getElementById('sell_price').value = item.sell_price;
        document.getElementById('supplier').value = item.supplier;
        document.getElementById('quantity').value = item.quantity;
        document.getElementById('status').value = item.status;
        document.getElementById('margin').value = item.margin;
        document.getElementById('comment').value = item.comment;
    } else {
        document.getElementById('itemModalTitle').innerText = 'Добавить позицию';
        document.getElementById('item_id').value = '';
    }

    document.getElementById('itemModal').style.display = 'block';
}

function closeItemModal() {
    document.getElementById('itemModal').style.display = 'none';
}

// сохранение
document.getElementById('itemForm').addEventListener('submit', function(e) {
    e.preventDefault();

    let formData = new FormData(this);
    let itemId = document.getElementById('item_id').value;
    let url = itemId ? `/orderitems/${itemId}` : `/orderitems`;
   if (itemId) {
        formData.append('_method', 'PUT'); // 👈 имитируем PUT
    }
    fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Ошибка сохранения');
        }
    });
});

// удаление
function deleteItem(id) {
    if (!confirm('Удалить позицию?')) return;
    fetch(`/orderitems/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById('item-row-' + id).remove();
        }
    });
}
</script>





@endsection
