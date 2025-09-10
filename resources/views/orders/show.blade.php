@extends('layouts.app')

@section('title', 'Детали заказа №' . $order->id)

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Заказ №{{ $order->id }}</h1>

    {{-- Информация о клиенте --}}
    <div class="card mb-4">
        <div class="card-header">Клиент</div>
        <div class="card-body">
            <p><strong>Имя:</strong> {{ $order->client->first_name ?? '—' }} {{ $order->client->middle_name ?? '' }} {{ $order->client->last_name ?? '' }}</p>
            <p><strong>Email:</strong> {{ $order->client->email ?? '—' }}</p>
            <p><strong>Телефон:</strong> {{ $order->client->phone ?? '—' }}</p>
        </div>
    </div>

    {{-- Информация о заказе --}}
    <div class="card mb-4">
        <div class="card-header">Информация о заказе</div>
        <div class="card-body">
            <p><strong>Статус:</strong> {{ $order->status ?? '—' }}</p>
            <p><strong>Дата создания:</strong> {{ $order->created_at->format('d.m.Y H:i') }}</p>
        </div>
    </div>

    {{-- Позиции заказа --}}
    <div class="card" style="margin-bottom: 10px">
      <div class="card-header d-flex justify-content-between align-items-center">
    <span>Позиции</span>
    <button class="btn" style="background: #d7d7d7" onclick="openItemModal({{ $order->id }})">
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
                        <th>Поставщик</th>
                        <th>Предоплата</th>
                        <th>Количество</th>
                        <th>Статус</th>
                        <th>Наценка %</th>
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
                        <td onclick='openItemModal({{ $order->id }}, @json($item))'>{{ number_format($item->amount, 2, ',', ' ') }}</td>
                        <td onclick='openItemModal({{ $order->id }}, @json($item))'>{{ $item->supplier }}</td>
                        <td onclick='openItemModal({{ $order->id }}, @json($item))'>{{ $item->prepayment }}</td>
                        <td onclick='openItemModal({{ $order->id }}, @json($item))'>{{ $item->quantity }}</td>
                        <td onclick='openItemModal({{ $order->id }}, @json($item))'>{{ $item->status }}</td>
                        <td onclick='openItemModal({{ $order->id }}, @json($item))'>{{ $item->margin }}</td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-danger" onclick="deleteItem({{ $item->id }})">🗑</button>
                        </td>
                    </tr>
                    @empty

                        <tr>
                            <td colspan="10" class="text-center text-muted">Нет позиций в заказе</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Кнопка назад --}}
    <div style="margin-top: 10px">
        <a href="#" onclick="window.history.back()" class="btn btn-secondary">← Назад к заказу</a>
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


            <label>Поставщик</label>
            <input type="text" name="supplier" id="supplier">

            <label>Предоплата</label>
            <input type="number" step="0.01" name="prepayment" id="prepayment">

            <label>Количество</label>
            <input type="number" name="quantity" id="quantity" value="1">

            <label>Статус</label>
            <input type="text" name="status" id="status">

            <label>Наценка</label>
            <input type="text" name="margin" id="margin">

            <div class="modal-actions">
                <button type="submit" class="btn btn-primary">Сохранить</button>
                <button type="button" class="btn btn-secondary" onclick="closeItemModal()">Отмена</button>
            </div>
        </form>
    </div>
</div>

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
        document.getElementById('supplier').value = item.supplier;
        document.getElementById('prepayment').value = item.prepayment;
        document.getElementById('quantity').value = item.quantity;
        document.getElementById('status').value = item.status;
        document.getElementById('margin').value = item.margin;
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
