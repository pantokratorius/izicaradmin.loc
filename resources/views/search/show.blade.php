@extends('layouts.app')

@section('title', 'Корзина')

@section('content')
<h1>Корзина</h1>

<a href="{{ route('search.index') }}" class="btn">➕ Добавить товар</a>

<table border="0" cellspacing="0" cellpadding="8" style="margin-top:15px;width:100%;">
    <thead>
        <tr>
            <th><input type="checkbox" id="select-all"></th>
            <th>ID</th>
            <th>Название</th>
            <th>Бренд</th>
            <th>Артикул</th>
            <th>Цена покупки</th>
            <th>Цена продажи</th>
            <th>Количество</th>
            <th>Сумма</th>
            <th>Наценка %</th>
            <th>Склад</th>
            <th>Поставщик</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @if($searches->count()) 
    @forelse($searches as $search)
<tr class="editable-row" data-edit-url="{{ route('search.edit', $search) }}" id="item-row-{{ $search->id }}">
    <td><input type="checkbox" class="item-checkbox" value="{{ $search->id }}"></td>
    <td class="edit">{{ $search->id }}</td>
    <td class="edit">{{ $search->name }}</td>
    <td class="edit">{{ $search->part_make }}</td>
    <td class="edit">{{ $search->part_number }}</td>
    <td class="edit">{{ number_format($search->purchase_price, 2, ',', ' ')  }}</td>
    <td class="edit" 
    @if($search->sell_price > 0 )
    style="background-color: #dcefff"
    @endif
    >{{ $search->sell_price ? number_format($search->sell_price, 2, ',', ' ') : number_format($search->amount, 2, ',', ' ')  }}</td>
    <td class="edit">{{ $search->quantity }}</td>
    <td class="edit" style="white-space: nowrap ">{{ number_format($search->quantity * ($search->sell_price ?? $search->amount), 2, ',', ' ')  }}</td>
    <td class="edit"
      @if($search->sell_price > 0 )
            style="background-color: #dcefff"
        @endif
    >{{  $search->sell_price > 0 ? round( ($search->sell_price / $search->purchase_price - 1) * 100, 2) : $globalMargin }}</td>
    <td class="edit">{{ $search->warehouse }}</td>
    <td class="edit">{{ $search->supplier }}</td>
    <td style="text-align: center">
        <a style="display: none" href="{{ route('search.edit', $search) }}">✏️</a>
        <form action="{{ route('search.destroy', $search->id) }}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" onclick="return confirm('Удалить?')">🗑</button>
            </form>
    </td>
</tr>
        
        @empty
          <tr>
            <td colspan="7" style="text-align: center;">Нет данных</td>
          </tr>
        @endforelse
        <tr>
            <th colspan="8"></th>
            <th>{{ number_format($search->summ , 2, ',', ' ') }}</th>
            <th colspan="4"></th>
        </tr>
        @endif
    </tbody>
</table>
<p>
@isset($search)
    <select onchange="openPrint(this, {{ $search->id }})" class="print-select">
        <option value="">🖨️ Печать...</option>
        <option value="{{ route('search.print', $search->id) }}">Заказ 1</option>
        <option value="{{ route('search.print2', $search->id) }}">Заказ 2</option>
    </select>
    <div>
        <button class="btn btn-danger" onclick="deleteSelectedItems()">🗑️ Удалить выбранные</button>
        <button id="btn-copy-new" class="btn btn-primary" onclick="openCopyModal()">Скопировать в новый заказ</button>
        <button id="btn-copy-existing" class="btn btn-secondary">Скопировать в существующий заказ</button>
        <button class="btn btn-success" onclick="addToStocks()" style="background: antiquewhite">📦 Добавить на склад</button>
        
    </div>
    @endisset

                </p>
{{ $searches->links() }}


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


<script>

document.querySelectorAll('.btn-delete-row').forEach(btn => {
    btn.addEventListener('click', async function() {
        const id = this.dataset.id;
        if (!confirm('Удалить эту позицию?')) return;

        try {
            const res = await fetch(`/search/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            
            const data = await res.json();
            console.log(data);
            if (data.success) {
                const row = document.getElementById(`item-row-${id}`);
                if (row) row.remove();
                alert('Удалено!');
            } else {
                alert('Ошибка при удалении');
            }
        } catch (e) {
            console.error(e);
            alert('Ошибка сети');
        }
    });
});

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

    fetch(`/search/batch-delete`, {
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

  //------------------------------------------------------------
// 🧩 Add selected items to Stocks
//------------------------------------------------------------

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
    const selectedRows = getSelectedIds(); // get selected checkboxes
    if (!selectedRows.length) {
        alert("Пожалуйста, выберите хотя бы одну строку для добавления на склад.");
        return;
    }

    const percent = parseFloat(document.querySelector('#percent_value')?.textContent) || 0;
    const selectedStocks = [];

    selectedRows.forEach(id => {
        const row = document.querySelector(`.item-checkbox[value="${id}"]`).closest('tr');

        const item = {
            part_number: row.querySelector('.edit:nth-child(5)')?.textContent?.trim() ?? "",
            part_make: row.querySelector('.edit:nth-child(4)')?.textContent?.trim() ?? "",
            name: row.querySelector('.edit:nth-child(3)')?.textContent?.trim() ?? "",
            quantity: parseInt(row.querySelector('.edit:nth-child(6)')?.textContent ?? "1", 10),
            purchase_price: parseFormattedNumber(row.querySelector('.edit:nth-child(7)')?.textContent) || 0,
            sell_price: parseFormattedNumber(row.querySelector('.edit:nth-child(8)')?.textContent) || 0,
            warehouse: row.querySelector('.edit:nth-child(9)')?.textContent?.trim() ?? "",
            supplier: row.querySelector('.edit:nth-child(10)')?.textContent?.trim() ?? "",
        };

        const stockData = {
            part_number: item.part_number,
            part_make: item.part_make,
            name: item.name,
            quantity: item.quantity,
            purchase_price: item.purchase_price,
            sell_price: (item.sell_price || item.purchase_price * (1 + percent / 100)).toFixed(2),
            warehouse: item.warehouse,
            supplier: item.supplier,
        };

        const key = `${stockData.part_make}_${stockData.part_number}_${stockData.supplier}`;
        const existing = selectedStocks.find(s => `${s.part_make}_${s.part_number}_${s.supplier}` === key);

        if (existing) {
            existing.quantity += stockData.quantity;
        } else {
            selectedStocks.push(stockData);
        }

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
                const qty = response?.data?.quantity ?? 1;
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

function rowFlash(row, color) {
    const original = row.style.backgroundColor;
    row.style.backgroundColor = color;
    setTimeout(() => row.style.backgroundColor = original, 1000);
}

function showToast(message) {
    alert(message); // or your custom toast
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

    fetch('/orders/copy-to-new2', {
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

    const response = await fetch('/orders/copy-to-existing2/' + orderNumber, {
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

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.editable-row').forEach(row => {
        row.addEventListener('dblclick', () => {
            const url = row.dataset.editUrl;
            if(url) window.location.href = url;
        });
    });
});
</script>

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

@endsection
