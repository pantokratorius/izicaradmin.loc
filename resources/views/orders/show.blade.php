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
                        <th><input type="checkbox" id="select-all"></th>
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
                        <td><input type="checkbox" class="item-checkbox" value="{{ $item->id }}"></td>
                        <td ondblclick='openItemModal({{ $order->id }}, @json($item))'>{{ $item->part_number }}</td>
                        <td ondblclick='openItemModal({{ $order->id }}, @json($item))'>{{ $item->part_make }}</td>
                        <td ondblclick='openItemModal({{ $order->id }}, @json($item))'>{{ $item->part_name }}</td>
                        <td ondblclick='openItemModal({{ $order->id }}, @json($item))'>{{ number_format($item->purchase_price, 2, ',', ' ') }}</td>
                        <td 
                            @if($item->sell_price > 0 )
                                style="background-color: #dcefff"
                            @endif
                        ondblclick='openItemModal({{ $order->id }}, @json($item))'>{{ $item->sell_price ? number_format($item->sell_price, 2, ',', ' ') : number_format($item->amount, 2, ',', ' ') }}</td>
                        <td ondblclick='openItemModal({{ $order->id }}, @json($item))'>{{ $item->quantity }}</td>
                        <td ondblclick='openItemModal({{ $order->id }}, @json($item))'>{{ number_format($item->summ, 2, ',', ' ') }}</td>
                        <td ondblclick='openItemModal({{ $order->id }}, @json($item))'>{{ $item->supplier }}</td>
                        <td> <select class="status_select"  data-id="{{ $item->id }}" style="padding: 3px 0">
                                    @foreach ($status as $key => $st)
                                        <option value="{{$key}}" {{ $item->status == $key ? 'selected' : '' }}>{{$st}}</option>
                                    @endforeach
                                </select></td>
                        <td 
                        @if($item->sell_price > 0 )
                            style="background-color: #dcefff"
                        @elseif($item->margin)
                        style="background-color: #dfffdc"
                            @endif
                            ondblclick='openItemModal({{ $order->id }}, @json($item))'>{{  $item->sell_price > 0 ? round( ($item->sell_price / $item->purchase_price - 1) * 100, 2) : ($item->margin ?? $globalMargin) }}
                        </td>
                        <td class="text-end" ondblclick='openItemModal({{ $order->id }}, @json($item))'>
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
            <th colspan="4"></th>
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
    <div style="margin: 30px 0 50px; display: flex; justify-content: space-between; align-items: flex-start">
        <div style="display: flex; flex-direction: column; justify-content: space-between; align-items: flex-start; height: 100px">
            <a href="#" onclick="window.history.back()" class="btn btn-secondary">← Назад</a>
            <br>
            <a href="{{route('orders.edit', $order->id)}}"  class="btn btn-secondary">Перейти в заказ</a>
            <br>
            <div>
                <button class="btn btn-danger" onclick="deleteSelectedItems()">🗑️ Удалить выбранные</button>
                <button id="btn-copy-new" class="btn btn-primary" onclick="openCopyModal()">Скопировать в новый заказ</button>
                <button id="btn-copy-existing" class="btn btn-secondary">Скопировать в существующий заказ</button>
                <button class="btn btn-success" onclick="addToStocks()">📦 Добавить на склад</button>
            </div>
        </div>

        <select onchange="openPrint(this, {{ $order->id }})" class="print-select">
                    <option value="">🖨️ Печать...</option>
                    <option value="{{ route('orders.print', $order->id) }}">Заказ 1</option>
                    <option value="{{ route('orders.print2', $order->id) }}">Заказ 2</option>
                </select>
    </div>
</div>

<!-- New Order Modal -->
<div id="copySelectedModal" class="modal-overlay" style="display:none;">
    <div class="modal2">
        <h3>Копировать выбранные строки</h3>
        
        <!-- Select client -->
        <div style="margin-bottom:10px;">
            <label>Клиент</label>
            <div class="custom-select" id="client-wrapper">
                <input type="text" placeholder="Выберите Клиента..." class="select-search">
                <input type="hidden" name="client_id">
                <ul class="select-options" style="display:none;"></ul>
            </div>
        </div>

        <!-- Select vehicle -->
        <div style="margin-bottom:10px;">
            <label>Авто</label>
            <div class="custom-select" id="vehicle-wrapper">
                <input type="text" placeholder="Выберите Авто..." class="select-search" disabled>
                <input type="hidden" name="vehicle_id">
                <ul class="select-options" style="display:none;"></ul>
            </div>
        </div>

        <div style="margin-top: 15px; display: flex; justify-content: flex-end;">
            <button id="cancelCopy" class="btn btn-secondary" style="margin-right: 10px;">Отмена</button>
            <button id="confirmCopy" class="btn btn-primary">Копировать</button>
        </div>
    </div>
</div>

<style>
/* Simple modal styles */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.6);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.modal2 {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    width: 400px;
}

.custom-select {
    position: relative;
    width: 100%;
}

.custom-select input.select-search {
    width: 100%;
    padding: 8px;
    box-sizing: border-box;
}

.custom-select ul.select-options {
    position: absolute;
    width: 100%;
    background: white;
    max-height: 200px;
    border: 1px solid #ddd;
    overflow-y: auto;
    z-index: 1000;
    list-style: none;
}

.custom-select ul.select-options li {
    padding: 8px;
    cursor: pointer;
}

.custom-select ul.select-options li:hover {
    background: #f0f0f0;
}

.btn {
    padding: 8px 14px;
    cursor: pointer;
}

.btn-primary {
    background: #2c7be5;
    color: #fff;
}

.btn-secondary {
    background: #6c757d;
    color: #fff;
}
</style>

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

// helper to parse numbers like "1 234,56" => 1234.56
function parseFormattedNumber(str) {
    if (str == null) return 0;
    // remove non-breaking space too
    str = String(str).replace(/\u00A0/g, ' ').trim();
    // remove spaces (thousand separators), replace comma with dot
    str = str.replace(/\s+/g, '').replace(',', '.');
    // remove any non-digit except dot and minus
    str = str.replace(/[^0-9.\-]/g, '');
    const n = parseFloat(str);
    return isNaN(n) ? 0 : n;
}

function addToStocks() {
    const selectedRows = getSelectedIds(); // your existing function
    if (!selectedRows.length) {
        alert("Пожалуйста, выберите хотя бы одну строку для добавления на склад.");
        return;
    }

    const percent = parseFloat(document.querySelector('#percent_value')?.textContent) || 0;

    selectedRows.forEach(id => {
        const checkbox = document.querySelector(`.item-checkbox[value="${id}"]`);
        if (!checkbox) return;
        const row = checkbox.closest('tr');
        if (!row) return;

        // get all tds (including non-edit ones) so indices match your template
        const tds = row.querySelectorAll('td');

        const part_number = tds[1]?.textContent?.trim() ?? "";
        const part_make   = tds[2]?.textContent?.trim() ?? "";
        const part_name   = tds[3]?.textContent?.trim() ?? "";
        const purchase_raw = tds[4]?.textContent ?? "";
        const sell_raw     = tds[5]?.textContent ?? "";
        const quantity_raw = tds[6]?.textContent ?? "1";
        const supplier     = tds[8]?.textContent?.trim() ?? "";
        const warehouse    = ""; // your row doesn't show warehouse column — leave empty or set if you have it

        const purchase_price = parseFormattedNumber(purchase_raw);
        let sell_price = parseFormattedNumber(sell_raw);

        // if sell_price === 0 compute from purchase and percent
        if (!sell_price && purchase_price) {
            sell_price = +(purchase_price * (1 + percent / 100)).toFixed(2);
        }

        const quantity = parseInt(quantity_raw.toString().replace(/\s+/g, ''), 10) || 1;

        const stockData = {
            name: part_name,
            part_make,
            part_number,
            quantity,
            purchase_price,
            sell_price: sell_price.toFixed ? sell_price.toFixed(2) : String(sell_price),
            warehouse,
            supplier
        };

        // send to backend
        fetch("{{ route('store_ajax') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": '{{ csrf_token() }}',
            },
            body: JSON.stringify(stockData),
        })
        .then(r => {
            if (!r.ok) throw new Error("HTTP " + r.status);
            return r.json();
        })
        .then(response => {
            rowFlash(row, "#d4edda");
            const qty = response?.data?.quantity ?? quantity;
            if (response.message?.includes("increased")) {
                showToast(`➕ Кол-во увеличено (теперь ${qty} шт.)`);
            } else {
                showToast(`✅ Добавлено на склад (теперь ${qty} шт.)`);
            }
        })
        .catch(err => {
            console.error("Ошибка при добавлении на склад:", err);
            rowFlash(row, "#ffe6e6");
            showToast("❌ Ошибка при добавлении на склад", "error");
        });
    });
}


//------------------------------------------------------------
// 🌈 Helpers
//------------------------------------------------------------
function rowFlash(row, color) {
    const original = row.style.backgroundColor;
    row.style.backgroundColor = color;
    setTimeout(() => row.style.backgroundColor = original, 1000);
}

function showToast(message) {
    alert(message); // or replace with your custom toast
}



document.getElementById('select-all').addEventListener('change', function(e) {
    document.querySelectorAll('.item-checkbox').forEach(checkbox => {
        checkbox.checked = e.target.checked;
    });
});

function deleteSelectedItems() {
    const selected = Array.from(document.querySelectorAll('.item-checkbox:checked'))
                          .map(cb => cb.value);

    if (selected.length === 0) {
        alert('Выберите хотя бы одну позицию для удаления');
        return;
    }

    if (!confirm(`Удалить выбранные позиции (${selected.length})?`)) return;

    fetch(`/orderitems/batch-delete`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        body: JSON.stringify({ ids: selected })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            selected.forEach(id => document.getElementById(`item-row-${id}`).remove());
        } else {
            alert('Ошибка при удалении');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Ошибка сети');
    });
}



//-----------------------------//-----------------------------
let lastChecked = null;
const checkboxes = document.querySelectorAll('.item-checkbox');

checkboxes.forEach(checkbox => {
    checkbox.addEventListener('click', function(e) {
        if (!lastChecked) {
            lastChecked = this;
            return;
        }

        if (e.shiftKey) {
            let inBetween = false;
            checkboxes.forEach(cb => {
                if (cb === this || cb === lastChecked) {
                    inBetween = !inBetween;
                }
                if (inBetween) {
                    cb.checked = lastChecked.checked;
                }
            });
        }

        lastChecked = this;
    });
});

function getSelectedIds() {
    return Array.from(checkboxes)
        .filter(cb => cb.checked)
        .map(cb => cb.value);
}

// Copy to new order

function openCopyModal() {
    const selectedRows = getSelectedIds(); // Ваша функция получения выделенных строк

    if (!selectedRows.length) {
        alert("Пожалуйста, выберите хотя бы одну строку для копирования.");
        return;
    }

    // Reset form fields
    document.querySelector('input[name="client_id"]').value = '';
    document.querySelector('#client-wrapper .select-search').value = '';

    const vehicleInput = document.querySelector('#vehicle-wrapper .select-search');
    vehicleInput.value = '';
    vehicleInput.disabled = true; // disable until client is selected
    document.querySelector('input[name="vehicle_id"]').value = '';

    // Clear vehicle dropdown options if previously loaded
    const vehicleOptions = document.querySelector('#vehicle-wrapper .select-options');
    vehicleOptions.innerHTML = '';

    // Open modal
    document.getElementById('copySelectedModal').style.display = 'flex';
}

// Function to close modal
function closeCopyModal() {
    document.getElementById('copySelectedModal').style.display = 'none';
}

// Custom select logic reused for client and vehicle
function createCustomSelect(wrapperId, optionsData, hiddenInputName) {
    const wrapper = document.getElementById(wrapperId);
    const input = wrapper.querySelector('.select-search');
    const ul = wrapper.querySelector('.select-options');
    const hiddenInput = wrapper.querySelector(`input[name="${hiddenInputName}"]`);

    function renderOptions(data) {
        ul.innerHTML = '';
        data.forEach(opt => {
            const li = document.createElement('li');
            li.textContent = opt.text;
            li.dataset.value = opt.value;
            li.addEventListener('click', async () => {
                input.value = opt.text;
                hiddenInput.value = opt.value;
                ul.style.display = 'none';
                if (typeof opt.onSelect === 'function') await opt.onSelect(opt.value);
            });
            ul.appendChild(li);
        });
    }

    renderOptions(optionsData);

    input.addEventListener('focus', () => ul.style.display = 'block');
    input.addEventListener('input', () => {
        const filter = input.value.toLowerCase();
        Array.from(ul.children).forEach(li => {
            li.style.display = li.textContent.toLowerCase().includes(filter) ? '' : 'none';
        });
    });

    document.addEventListener('click', e => {
        if (!wrapper.contains(e.target)) ul.style.display = 'none';
    });

    return { renderOptions };
}

const clientSelect = createCustomSelect('client-wrapper', [], 'client_id');
const vehicleSelect = createCustomSelect('vehicle-wrapper', [], 'vehicle_id');

// Fetch clients on load
fetch('/clients/list')
    .then(response => response.json())
    .then(data => {
        const options = data.map(client => ({
            text: `${client.first_name ?? ''} ${client.middle_name ?? ''} ${client.last_name ?? ''} ${client.phone ?? ''}`,
            value: client.id,
            onSelect: loadVehiclesForClient
        }));
        clientSelect.renderOptions(options);
    });

// Load vehicles when client is selected
function loadVehiclesForClient(clientId) {
    const input = document.querySelector('#vehicle-wrapper .select-search');
    input.value = ''
    input.disabled = false;

    fetch(`/vehicles/by-client/${clientId}`)
        .then(response => response.json())
        .then(data => {
            const vehicleOptions = data.map(vehicle => ({
                text: `${vehicle.text}`,
                value: vehicle.id
            }));
            vehicleSelect.renderOptions(vehicleOptions);
        });
}

// Event listeners for modal buttons
document.getElementById('cancelCopy').addEventListener('click', closeCopyModal);
document.getElementById('confirmCopy').addEventListener('click', () => {
    // Collect data and send POST
    const clientId = document.querySelector('input[name="client_id"]').value;
    const vehicleId = document.querySelector('input[name="vehicle_id"]').value;

    const selectedRows = getSelectedIds(); // Your method to retrieve selected rows

    fetch('/orders/copy-to-new', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ client_id: clientId, vehicle_id: vehicleId, rows: selectedRows })
    })
    .then(response => response.json())
    .then(data => {
       if(data.redirect.length){
            alert("Строки скопированы успешно!");
            closeCopyModal();
            window.location = data.redirect
       }
        // Optionally reload or update page...
    })
    .catch(e => console.error(e));
});


// Copy to existing order
document.getElementById('btn-copy-existing').addEventListener('click', async function() {
    const ids = getSelectedIds();
    if (ids.length === 0) {
        alert('Ничего не выбрано!');
        return;
    }

    const orderNumber = prompt("Введите номер заказа:");
    if (!orderNumber) return;

    const response = await fetch('/orders/copy-to-existing/' + orderNumber, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ ids })
    });

    if (response.ok) {
        alert('Скопировано в заказ!');
        window.location.reload();
    } else {
        alert('Ошибка при копировании!');
    }
});


//-----------------------------

    function openPrint(select, orderId) {
        if (!select.value) return;

        // Get selected item IDs
        const selectedIds = getSelectedIds(); // your existing helper
        let url = select.value;

        if (selectedIds.length > 0) {
            // Append selected IDs as query string
            const params = new URLSearchParams();
            params.append('items', selectedIds.join(','));
            url += '?' + params.toString();
        }

        window.open(url, '_blank');

        // Reset dropdown
        select.selectedIndex = 0;
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
