@extends('layouts.app')

@section('title', 'Редактировать клиента')

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
<style>
    .tabs { display: flex; border-bottom: 2px solid #ccc; margin-bottom: 15px; }
    .tab { padding: 10px 20px; cursor: pointer; border: 1px solid #ccc; border-bottom: none; background: #f7f7f7; margin-right: 5px; }
    .tab.active { background: #fff; font-weight: bold; border-top: 2px solid #14213d; }
    .tab-content { border: 1px solid #ccc; padding: 10px; background: #fff; display: none; }
    .tab-content.active { display: block; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px;
        /* max-height: 300px;  */
        overflow-y: auto; display: block; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: left; white-space: nowrap; }
    th { background: #eee; position: sticky; top: 0; }
    .btn { padding: 5px 10px; background: #14213d; color: #fff; border-radius: 4px; text-decoration: none; margin-bottom: 10px; display: inline-block; }
    .btn:hover { background: #0f0f2d; }
    .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); }
    .modal-content { background-color: #fff; margin: 5% auto; padding: 40px; border-radius: 6px; width: 500px; position: relative; max-height: 90vh; overflow-y: auto; }
    .close { position: absolute; top: 10px; right: 15px; font-size: 20px; cursor: pointer; padding: 20px }
</style>

<div class="page-header">
    <div>Клиенты > Редактировать</div>
</div>

<button id="toggleClientForm" class="btn" style="margin-bottom:10px;">Показать форму</button>

<div id="clientFormContainer" style= "display: none">
<form method="POST" action="{{ route('clients.update', $client->id) }}" style="background:#fff;padding:20px 20px 20px 20px; border-radius:6px;margin-bottom:70px;">
    @csrf
    @method('PUT')
    <!-- Client fields -->
    <div style="margin-bottom:15px;">
        <label>Имя</label><br>
        <input type="text" name="first_name" value="{{ old('first_name', $client->first_name) }}" required style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;">
        @error('first_name') <small style="color:red">{{ $message }}</small> @enderror
    </div>
    <div style="margin-bottom:15px;">
        <label>Фамилия</label><br>
        <input type="text" name="last_name" value="{{ old('last_name', $client->last_name) }}" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;">
        @error('last_name') <small style="color:red">{{ $message }}</small> @enderror
    </div>
    <div style="margin-bottom:15px;">
        <label>Отчество</label><br>
        <input type="text" name="middle_name" value="{{ old('middle_name', $client->middle_name) }}" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;">
        @error('middle_name') <small style="color:red">{{ $message }}</small> @enderror
    </div>
    <div style="margin-bottom:15px;">
        <label>Телефон</label><br>
        <input type="text" name="phone" value="{{ old('phone', $client->phone) }}" required style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;">
        @error('phone') <small style="color:red">{{ $message }}</small> @enderror
    </div>
    <div style="margin-bottom:15px;">
        <label>Email</label><br>
        <input type="email" name="email" value="{{ old('email', $client->email) }}" required style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;">
        @error('email') <small style="color:red">{{ $message }}</small> @enderror
    </div>
    <div style="margin-bottom:15px;">
        <label>Сегмент</label><br>
        @php $segments = ['Розница', 'Сотрудники', 'СТО']; @endphp
        @foreach($segments as $seg)
            <label style="margin-right:15px;">
                <input type="radio" name="segment" value="{{ $seg }}" {{ old('segment', $client->segment) === $seg ? 'checked' : '' }}>
                {{ $seg }}
            </label>
        @endforeach
        @error('segment') <br><small style="color:red">{{ $message }}</small> @enderror
    </div>
    <div style="margin-bottom:15px;">
        <label>Скидка (%)</label><br>
        <input type="number" step="0.01" name="discount" value="{{ old('discount', $client->discount) }}" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;">
        @error('discount') <small style="color:red">{{ $message }}</small> @enderror
    </div>
    <button type="submit" style="background:#14213d;color:#fff;padding:10px 20px;border:none;border-radius:4px;cursor:pointer;">Обновить</button>
</form>
    <form id="delete-client-{{ $client->id }}" style="margin: -124px 20px 40px 153px"
        action="{{ route('clients.destroy', $client->id) }}"
        method="POST" >
      @csrf
      @method('DELETE')
      <button onclick="if(!confirm('Удалить клиента?')) return false " class="delete_client" data-id="{{$client->id}}" style="btn btn-sm btn-danger; background: #990202; cursor: pointer;color:#fff;padding:10px 20px;border:none;border-radius:4px; ">Удалить</button>
  </form>
</div>
<!-- Tabs -->
<div>
    <div class="tabs">
        <div class="tab active" data-tab="vehicles">Автомобили</div>
        <div class="tab" data-tab="orders">Заказы</div>
    </div>



    <!-- Vehicles tab -->
    <div id="vehicles" class="tab-content active">
        <a href="javascript:void(0)" class="btn" onclick="openVehicleModal()">Добавить автомобиль</a>
        @if($client->vehicles->isEmpty())
            <p>У клиента нет автомобилей.</p>
        @else



            <table>
                <thead>
                    <tr>
                        <th>VIN</th>
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
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($client->vehicles ?? [] as $vehicle)
                        <tr style="cursor:pointer;" >
                            <td onclick="openVehiclesOrders({{ $vehicle }})">{{ $vehicle->vin }}</td>
                            <td  onclick="openVehiclesOrders({{ $vehicle }})">{{ $vehicle->vehicle_type }}</td>
                            <td  onclick="openVehiclesOrders({{ $vehicle }})">{{ $vehicle->brand->name ?? $vehicle->brand_name }}</td>
                            <td  onclick="openVehiclesOrders({{ $vehicle }})">{{ $vehicle->model->name ?? $vehicle->model_name ?? '-'}}</td>
                            <td  onclick="openVehiclesOrders({{ $vehicle }})">{{ $vehicle->generation->name ?? $vehicle->generation_name ?? '-' }}</td>
                            <td  onclick="openVehiclesOrders({{ $vehicle }})">{{ $vehicle->serie->name ?? $vehicle->serie_name ?? '-' }}</td>
                            <td  onclick="openVehiclesOrders({{ $vehicle }})">{{ $vehicle->body ?? '-' }}</td>
                            <td  onclick="openVehiclesOrders({{ $vehicle }})">{{ $vehicle->modification->name ?? '-' }}</td>
                            <td  onclick="openVehiclesOrders({{ $vehicle }})">{{ $vehicle->registration_number ?? '-' }}</td>
                            <td  onclick="openVehiclesOrders({{ $vehicle }})">{{ $vehicle->sts ?? '-' }}</td>
                            <td  onclick="openVehiclesOrders({{ $vehicle }})">{{ $vehicle->pts ?? '-' }}</td>
                            <td  onclick="openVehiclesOrders({{ $vehicle }})">{{ $vehicle->year_of_manufacture }}</td>
                            <td  onclick="openVehiclesOrders({{ $vehicle }})">{{ $vehicle->engine_type }}</td>
                            <td style="display: flex; flex-direction: row-reverse"><form
                  action="{{ route('vehicles.destroy', $vehicle->id) }}"
                  method="POST" style="">
                    @csrf
                    @method('DELETE')
                    <button onclick="if(!confirm('Удалить автомобиль?')) return false" style="btn btn-sm btn-danger; cursor: pointer">🗑</button>
                </form>
            <button  onclick="openVehicleModal({{ $vehicle }})"

                            style="btn btn-sm btn-warning; cursor: pointer; margin-right: 5px">
                        ✏
                    </button>
            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <!-- Orders tab -->


    <div id="orders" class="tab-content">
        <a href="javascript:void(0)" class="btn" onclick="openOrderModal()">Добавить заказ</a>
        <div style="margin-bottom: 10px;">
    <button id="resetOrdersBtn" type="button" onclick="resetOrdersFilter()" style="display:none;">
        Показать все заказы
    </button>
</div>  
        @if($allOrders->isEmpty())
            <p>У клиента нет заказов.</p>
        @else

            <table>
                <thead>
                    <tr>
                        <th>№ заказа</th>
                        <th>Закупка</th>
                        <th>Продажа</th>
                        <th>Предоплата</th>
                        <th>Остаток</th>
                        <th>Статус</th>
                        <th>Дата создания</th>
                        <th>Автомобиль</th>
                        <th>Менеджер</th>
                        <th>Пробег</th>
                        <th>Наценка %</th>
                        <th>Прибыль</th>
                        <th>Коммент</th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    
                    @foreach($allOrders ?? [] as $order)
                        <tr style="cursor:pointer; " id="toggle-btn-{{ $order->id }}" data-vehicle-id="{{ $order->vehicle_id }}">
                            <td onclick="toggleItems({{ $order->id }})">{{ $order->order_number }}</td>
                            <td onclick="toggleItems({{ $order->id }})">{{ number_format($order->purchase_sum, 2, ',', ' ') }}</td>
                            <td onclick="toggleItems({{ $order->id }})">{{ number_format($order->amount, 2, ',', ' ')}}</td>
                            <td onclick="toggleItems({{ $order->id }})">{{ number_format($order->prepayment, 2, ',', ' ') ?? '-' }}</td>
                            <td onclick="toggleItems({{ $order->id }})">{{ number_format($order->amount - $order->prepayment, 2, ',', ' ') ?? '-' }}</td>
                            <td>
                                <select class="status_select"  data-id="{{ $order->id }}" style="padding: 3px 0">
                                    @foreach ($status as $key => $st)
                                        <option value="{{$key}}" {{ $order->status == $key ? 'selected' : '' }}>{{$st}}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td onclick="toggleItems({{ $order->id }})">{{ $order->created_at  ?  $order->created_at->format('d.m.Y') : '' }}</td>
                            <td onclick="toggleItems({{ $order->id }})">{{ $order->vehicle ? ($order->vehicle->brand->name ?? $order->vehicle->brand_name).' '.($order->vehicle->model->name ?? $order->vehicle->model_name) : '-' }}</td>
                            <td onclick="toggleItems({{ $order->id }})">{{ $order->manager ? $order->manager->name : '-' }}</td>
                            <td onclick="toggleItems({{ $order->id }})">{{ $order->mileage ?? '-' }}</td>
                            <td onclick="toggleItems({{ $order->id }})">{{ $order->margin ?? $globalMargin ?? '-' }}</td>
                            <td onclick="toggleItems({{ $order->id }})">{{ number_format($order->amount - $order->purchase_sum, 2, ',', ' ') }} 
                                ({{$order->purchase_sum > 0 ?  number_format( ($order->amount - $order->purchase_sum) / $order->purchase_sum * 100 , 2, ',', ' ') : 0}}%)</td>
                                <td onclick="toggleItems({{ $order->id }})"></td>
                            <td >
                                <div style="display: flex; align-items: flex-start">
                      
                  @if(!$allOrders->isEmpty())
                  <a href="{{ route('orders.copy', $order->id) }}" 
                        class="btn btn-secondary"
                        onclick="return confirm('Скопировать этот заказ?')">
                        📄
                    </a>

                    <button  onclick="openOrderModal({{ $order }})"

                            style="btn btn-sm btn-warning; margin: 0 5px; cursor: pointer">
                        ✏
                    </button>
                  @endif
            <form
                  action="{{ route('orders.destroy', $order->id) }}"
                  method="POST" style="">
                    @csrf
                    @method('DELETE')
                    <button onclick="if(!confirm('Удалить заказ?')) return false" style="btn btn-sm btn-danger; cursor: pointer">🗑</button>
                </form>
            </div></td>
                <td>
                <select onchange="openPrint(this, {{ $order->id }})" class="print-select">
                    <option value="">🖨️ Печать...</option>
                    <option value="{{ route('orders.print', $order->id) }}">Заказ 1</option>
                    <option value="{{ route('orders.print2', $order->id) }}">Заказ 2</option>
                </select>
            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

<!-- Vehicle Modal -->
<div id="vehicleModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('vehicleModal')">&times;</span>
        <h3 id="vehicleModalTitle">Добавить автомобиль</h3>
          <form id="vehicleForm" method="POST" action="{{ route('vehicles.store') }}">
              @csrf
              <input type="hidden" name="client_id" value="{{ $client->id }}">
              @php
                  $fields = [
                      'vin'=>'VIN','vehicle_type'=>'Тип транспортного средства',
                      'body'=>'Кузов', 
                      'registration_number'=>'Гос номер','sts'=>'СТС','pts'=>'ПТС',
                      'year_of_manufacture'=>'Год','engine_type'=>'Тип двигателя'
                  ];
              @endphp

                <div style="margin-bottom:10px;">
    <label>Марка</label>
    <div class="custom-select" id="brand-wrapper">
        <input type="text" placeholder="Выберите Марку..." class="select-search">
        <ul class="select-options" style="display:none;"></ul>
    </div>
</div>

<div style="margin-bottom:10px;">
    <label>Модель</label>
    <div class="custom-select" id="model-wrapper">
        <input type="text" placeholder="Выберите Модель..." class="select-search">
        <ul class="select-options" style="display:none;"></ul>
    </div>
</div>

<div style="margin-bottom:10px;">
    <label>Поколение</label>
    <div class="custom-select" id="generation-wrapper">
        <input type="text" placeholder="Выберите Поколение..." class="select-search">
        <ul class="select-options" style="display:none;"></ul>
    </div>
</div>

<div style="margin-bottom:10px;">
    <label>Серия</label>
    <div class="custom-select" id="serie-wrapper">
        <input type="text" placeholder="Выберите Серию..." class="select-search">
        <ul class="select-options" style="display:none;"></ul>
    </div>
</div>

<div style="margin-bottom:10px;">
    <label>Модификация</label>
    <div class="custom-select" id="modification-wrapper">
        <input type="text" placeholder="Выберите модификацию..." class="select-search">
        <ul class="select-options" style="display:none;"></ul>
    </div>
</div>

    @php
        $accordionOpen = $client->vehicle?->brand_name || $client->vehicle?->model_name || $client->vehicle?->generation_name || $client->vehicle?->serie_name || $client->vehicle?->modification_name;
    @endphp

  <div class="accordion">
    <button type="button" class="accordion-toggle">Показать/Скрыть для внесения вручную</button>
    <div class="accordion-content" style="display: {{ $accordionOpen ? 'block' : 'none' }}; margin-top:10px;">
<div style="margin-bottom:10px;">
    <label>Марка</label>
    <input type="text" name="brand_name" id="vehicle_brand_name" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;background: #dbdbdb;">
</div>

<div style="margin-bottom:10px;">
    <label>Модель</label>
    <input type="text" name="model_name" id="vehicle_model_name" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;background: #dbdbdb;">
</div>

<div style="margin-bottom:10px;">
    <label>Поколение</label>
    <input type="text" name="generation_name" id="vehicle_generation_name" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;background: #dbdbdb;">
</div>

<div style="margin-bottom:10px;">
    <label>Серия</label>
    <input type="text" name="serie_name" id="vehicle_serie_name" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;background: #dbdbdb;">
</div>

<div style="margin-bottom:10px;">
    <label>Модификация</label>
    <input type="text" name="modification_name" id="vehicle_modification_name" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;background: #dbdbdb;">
</div>

    </div>
</div>

              @foreach($fields as $name => $label)
                  <div style="margin-bottom:10px;">
                      <label>{{ $label }}</label>
                      <input type="text" name="{{ $name }}" id="vehicle_{{ $name }}" 
                      style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;">
                  </div>
              @endforeach
              <button type="submit" class="btn" style="color:#fff;padding:10px 20px;border:none;border-radius:4px;cursor:pointer;">Сохранить</button>
            </form>

    </div>
</div>

<!-- Order Modal -->
<div id="orderModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('orderModal')">&times;</span>
        <h3 id="orderModalTitle">Добавить заказ</h3>
        <form id="orderForm" method="POST" action="{{ route('orders.store') }}">
            @csrf
            <input type="hidden" name="client_id" value="{{ $client->id }}">
            <div style="margin-bottom:10px;">
                <label>№ заказа</label>
                <input type="text" name="order_number" value="{{$orders_count}}" id="order_order_number" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;">
            </div>
            <div style="margin-bottom:10px;">
                <label>Дата создания</label>
                <input type="date" name="created_at" id="created_at" value="{{ date('Y-m-d') }}" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;">
            </div>
            <div style="margin-bottom:10px;">
                <label>Пробег</label>
                <input type="text" name="mileage" id="order_mileage" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;">
            </div>
            <div style="margin-bottom:10px;">
                <label>Предоплата</label>
                <input type="text" name="prepayment" id="order_prepayment" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;">
            </div>
            <div style="margin-bottom:10px;">
                <label>Автомобиль (если есть)</label>
                <select name="vehicle_id" id="order_vehicle_id" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;">
                    <option value="">-- Не указан --</option>
                    @foreach($client->vehicles ?? [] as $vehicle)
                        <option value="{{ $vehicle->id }}">{{ $vehicle->brand->name ?? '-' }} {{ $vehicle->model->name  ?? '-'}} ({{ $vehicle->vin }})</option>
                    @endforeach
                </select>
            </div>
            <div style="margin-bottom:10px;">
                <label>Ответственный менеджер</label>
                <select name="manager_id" id="order_manager_id" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;">
                    <option value="">-- Не указан --</option>
                    @foreach(\App\Models\User::all() as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="margin-bottom:10px;">
                <label>Наценка %</label>
                <input type="text" name="margin" id="order_margin" value="" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;">
            </div>
            <div style="margin-bottom:10px;">
                <label>Комментарий</label>
                <input type="text" name="comment" id="order_comment" value="" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;">
            </div>
            <button type="submit" class="btn">Сохранить</button>
        </form>
    </div>
</div>

<!-- OrderItem Modal -->
<div id="orderItemModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('orderItemModal')">&times;</span>
        <h3 id="orderItemModalTitle">Добавить позицию</h3>
        <form id="orderItemForm" method="POST" action="{{ route('orderitems.store') }}">
            @csrf
            <input type="hidden" name="order_id" id="orderItem_order_id" value="">

            <div style="margin-bottom:10px;">
                <label>Артикул</label>
                <input type="text" name="part_number" id="orderItem_part_number" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;">
            </div>
            <div style="margin-bottom:10px;">
                <label>Бренд</label>
                <input type="text" name="part_make" id="orderItem_part_make" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;">
            </div>
            <div style="margin-bottom:10px;">
                <label>Наименование</label>
                <input type="text" name="part_name" id="orderItem_part_name" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;">
            </div>
            <div style="margin-bottom:10px;">
                <label>Цена закупки</label>
                <input type="text" name="purchase_price" id="orderItem_purchase_price" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;">
            </div>
            <div style="margin-bottom:10px;">
                <label>Поставщик</label>
                <input type="text" name="supplier" id="orderItem_supplier" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;">
            </div>
            <div style="margin-bottom:10px;">
                <label>Количество</label>
                <input type="number" name="quantity" id="orderItem_quantity" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;">
            </div>
            <button type="submit" class="btn">Сохранить</button>
        </form>
    </div>
</div>

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



document.addEventListener("DOMContentLoaded", function () {
    const toggleBtn = document.querySelector(".accordion-toggle");
    const content = document.querySelector(".accordion-content");

    toggleBtn.addEventListener("click", () => {
        if (content.style.display === "none") {
            content.style.display = "block";
        } else {
            content.style.display = "none";
        }
    });
});
</script>
<script>
function openPrint(select, orderId) {
    if (select.value) {
        window.open(select.value, '_blank');
        select.selectedIndex = 0; // сбрасываем обратно на первый вариант
    }
}
</script>

<script>
window.addEventListener("pageshow", function (event) {
    if (event.persisted) {
        // If coming from browser cache, force reload
        window.location.reload();
    }
});
</script>
<script>

// // Brand options (from Blade variable)
// const brands = [
//     @foreach($brands as $brand)
//     { value: {{ $brand->id }}, text: "{{ $brand->name }}" },
//     @endforeach
// ];
function resetOrdersFilter() {
    document.querySelectorAll('#orders tbody tr').forEach(tr => {
        tr.style.display = '';
    });

    // Hide reset button
    document.getElementById('resetOrdersBtn').style.display = 'none';
}


function openVehiclesOrders(vehicle) {
    // Switch to orders tab
    activateTab('orders');

    // Hide all orders rows first
    document.querySelectorAll('#orders tbody tr').forEach(tr => {
        tr.style.display = '';
    });

    if (vehicle && vehicle.id) {
        document.querySelectorAll('#orders tbody tr').forEach(tr => {
            const rowVehicleId = tr.getAttribute('data-vehicle-id');
            tr.style.display = (rowVehicleId == vehicle.id) ? '' : 'none';
        });

        // Show reset button
        document.getElementById('resetOrdersBtn').parentNode.style.display = 'inline-block';
        document.getElementById('resetOrdersBtn').style.display = 'block';
    }
}



// Create a custom searchable select
function createCustomSelect(wrapperId, optionsData, hiddenInputName) {
    const wrapper = document.getElementById(wrapperId);
    const input = wrapper.querySelector('.select-search');
    const ul = wrapper.querySelector('.select-options');

    // Hidden input to store value
    let hiddenInput = wrapper.querySelector('input[type="hidden"]');
    if(!hiddenInput){
        hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = hiddenInputName;
        wrapper.appendChild(hiddenInput);
    }

    function renderOptions(data){
        ul.innerHTML = '';
        data.forEach(opt => {
            const li = document.createElement('li');
            li.textContent = opt.text;
            li.dataset.value = opt.value;
            li.addEventListener('click', async () => {
                input.value = opt.text;
                hiddenInput.value = opt.value;
                ul.style.display = 'none';
                if(typeof opt.onSelect === 'function') await opt.onSelect(opt.value); // trigger dependent dropdown
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
        if(!wrapper.contains(e.target)) ul.style.display = 'none';
    });

    return { renderOptions };
}

// Brand options (from Blade variable)
const brands = [
    @foreach($brands as $brand)
    { value: {{ $brand->id }}, text: "{{ $brand->name }}" },
    @endforeach
];




// Initialize all selects
const selects = [
    { wrapperId: 'brand-wrapper', hiddenName: 'car_brand_id', fetchUrl: '/cars/models/' },
    { wrapperId: 'model-wrapper', hiddenName: 'car_model_id', fetchUrl: '/cars/generations/' },
    { wrapperId: 'generation-wrapper', hiddenName: 'car_generation_id', fetchUrl: '/cars/series/' },
    { wrapperId: 'serie-wrapper', hiddenName: 'car_serie_id', fetchUrl: '/cars/modifications/' },
    { wrapperId: 'modification-wrapper', hiddenName: 'car_modification_id', fetchUrl: null }
];

const selectObjects = selects.map(s => createCustomSelect(s.wrapperId, [], s.hiddenName));

// Populate brand options
selectObjects[0].renderOptions(brands);

// Fetch dependent options
async function fetchOptionsAsync(url){
    const res = await fetch(url);
    const data = await res.json();
    return data.map(d => ({ value: d.id, text: d.name }));
}

// Setup cascading selects
async function setupCascadingSelects() {
    for(let i=0; i<selects.length; i++){
        const sel = selects[i];
        const obj = selectObjects[i];

        obj.renderOptions = (function(originalRender, index){
            return function(data){
                originalRender(data);
                data.forEach(d => d.onSelect = async function(value){
                    // Reset downstream selects
                    for(let j=index+1; j<selectObjects.length; j++){
                        selectObjects[j].renderOptions([]);
                        const input = document.querySelector(`#${selects[j].wrapperId} .select-search`);
                        const hidden = document.querySelector(`#${selects[j].wrapperId} input[type="hidden"]`);
                        input.value = '';
                        hidden.value = '';
                    }
                    // Fetch next level if exists
                    if(selects[index].fetchUrl){
                        const nextData = await fetchOptionsAsync(selects[index].fetchUrl + value);
                        selectObjects[index+1].renderOptions(nextData);
                    }
                });
            }
        })(obj.renderOptions, i);
    }
}

setupCascadingSelects();

// Fill vehicle modal selects when editing
async function fillVehicleSelects(vehicle) {
    for(let i=0; i<selects.length; i++){
        const sel = selects[i];
        const obj = selectObjects[i];
        const value = vehicle[sel.hiddenName];
        if(!value) break;

        let text = '';

        if(i === 0){
            const opt = brands.find(b => b.value == value);
            text = opt ? opt.text : '';
            obj.renderOptions(brands);
        } else {
            const prevValue = vehicle[selects[i-1].hiddenName];
            const data = await fetchOptionsAsync(selects[i-1].fetchUrl + prevValue);
            obj.renderOptions(data);
            const opt = data.find(d => d.value == value);
            text = opt ? opt.text : '';
        }

        const input = document.querySelector(`#${sel.wrapperId} .select-search`);
        const hidden = document.querySelector(`#${sel.wrapperId} input[type="hidden"]`);
        input.value = text;
        hidden.value = value;
    }
}

// Vehicle modal open for edit or add
async function openVehicleModal(vehicle = null) { 

    const form = document.getElementById('vehicleForm');

    if(vehicle) {
        document.getElementById('vehicleModalTitle').innerText = 'Редактировать автомобиль';
        form.action = '/vehicles/' + vehicle.id;
        form.method = 'POST';

        if(!form.querySelector('[name="_method"]')) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = '_method';
            input.value = 'PUT';
            form.appendChild(input);
        }

        // Fill normal inputs
        for(let key in vehicle){
            const el = document.getElementById('vehicle_'+key);
            if(el) el.value =  el.value = vehicle[key]?.name ?? (vehicle[key] ?? '');
        }

        // Fill cascading selects
        await fillVehicleSelects(vehicle);

    } else {
        // New vehicle
        document.getElementById('vehicleModalTitle').innerText = 'Добавить автомобиль';
        form.action = '{{ route("vehicles.store") }}';
        form.method = 'POST';
        form.querySelectorAll('input').forEach(i => { if(i.type !== 'hidden') i.value = ''; });
    }

    openModal('vehicleModal');
}


</script>



<script>
const tabs = document.querySelectorAll('.tab');
const contents = document.querySelectorAll('.tab-content');

function setTab(val){
    const value = val;

    fetch("{{ route('set.session') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ active_tab: value })
    })
    .then(response => response.json())
    .then(data => {
        // Update the page instantly
    })
    .catch(error => console.error(error));
}


function activateTab(tabName) {

    setTab(tabName)

    tabs.forEach(t => {
        t.classList.toggle('active', t.dataset.tab === tabName);
    });
    contents.forEach(c => {
        c.classList.toggle('active', c.id === tabName);
    });
}

// default: vehicles, but use session value if exists
let activeTab = "{{ session('active_tab', 'vehicles') }}";
activateTab(activeTab);

// add listeners
tabs.forEach(tab => {
    tab.addEventListener('click', () => {
        activateTab(tab.dataset.tab)
    });
});

function openModal(id) { document.getElementById(id).style.display = 'block'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; }
// window.onclick = function(event) {
//     if(event.target.classList.contains('modal')) event.target.style.display = "none";
// }

// Initialize cascading selects
setupCascadingSelects();

// Populate brand options AFTER handlers are ready
selectObjects[0].renderOptions(brands);


// Order modal open for edit or add
function openOrderModal(order = null) {
    const form = document.getElementById('orderForm');
    if(order) {
        document.getElementById('orderModalTitle').innerText = 'Редактировать заказ';
        form.action = '/orders/' + order.id;
        form.method = 'POST';
        if(!form.querySelector('[name="_method"]')) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = '_method';
            input.value = 'PUT';
            form.appendChild(input);
        }
        for(let key in order) {
            if(document.getElementById('order_'+key)) document.getElementById('order_'+key).value = order[key] ?? '';
        }
    } else {
        document.getElementById('orderModalTitle').innerText = 'Добавить заказ';
        form.action = '{{ route("orders.store") }}';
        form.method = 'POST';
        form.querySelectorAll('input').forEach(i => { if(i.type !== 'hidden' && !['order_number', 'created_at'].includes(i.name)){ 
            i.value = '';
        } });
    }
    openModal('orderModal');
}

  setTimeout(() => {
      const success = document.querySelectorAll('.successMessage');
      const error = document.querySelectorAll('.errorMessage');
      if (success){
         [...success].forEach(item => {
          item.style.display = 'none';
        })
      }
      if (error) {
        [...error].forEach(item => {
          item.style.display = 'none';
        })
      }
  }, 2000);

function toggleItems(orderId) { 

window.location.href = "{{ route('orders.show', ':id') }}".replace(':id', orderId);

    // let block = document.getElementById('order-items-' + orderId);
    // let btn = document.querySelector('#toggle-btn-' + orderId);

    // block.classList.toggle('open');

}

let activeOrder = "{{ session('toggle-btn-', '') }}";
if(activeOrder != '') toggleItems(activeOrder)

const toggleBtn = document.getElementById('toggleClientForm');
const clientForm = document.getElementById('clientFormContainer');

toggleBtn.addEventListener('click', () => {
    if(clientForm.style.display === 'none' || clientForm.style.maxHeight === '0px') {
        clientForm.style.display = 'block';
        toggleBtn.textContent = 'Скрыть форму';
    } else {
        clientForm.style.display = 'none';
        toggleBtn.textContent = 'Показать форму';
    }
});


</script>

<style>
/* .accordion-content {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.4s ease, padding 0.3s ease;
    padding: 0;
}
.accordion-content.open {
    padding: 10px 0;
    max-height: 500px; /* достаточно большое значение 
} */


#clientFormContainer {
    transition: max-height 0.4s ease, padding 0.3s ease;
}





.custom-select { position: relative; width: 100%; }
.select-search { width: 100%; padding: 6px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
.select-options { position: absolute; top: 100%; left: 0; right: 0; max-height: 300px; overflow-y: auto; border: 1px solid #ccc; border-radius: 4px; background: #fff; list-style: none; padding: 0; margin: 0; z-index: 1000; }
.select-options li { padding: 6px; cursor: pointer; }
.select-options li:hover { background: #f0f0f0; }

#orderForm div label, #orderItemForm div label, #vehicleForm div label {
    margin-bottom: 5px;
    display: inline-block;
    font-weight: bold;
}

</style>
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
.accordion-toggle {
    background: #2d89ef;
    color: white;
    padding: 8px 12px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    margin-bottom: 10px;
}
.accordion-toggle:hover {
    background: #1b5fbd;
}
.accordion-content .form-group { margin-bottom: 10px; }
</style>

@endsection
