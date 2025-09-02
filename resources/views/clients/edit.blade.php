@extends('layouts.app')

@section('title', 'Редактировать клиента')

@section('content')
<style>
    .tabs { display: flex; border-bottom: 2px solid #ccc; margin-bottom: 15px; }
    .tab { padding: 10px 20px; cursor: pointer; border: 1px solid #ccc; border-bottom: none; background: #f7f7f7; margin-right: 5px; }
    .tab.active { background: #fff; font-weight: bold; border-top: 2px solid #14213d; }
    .tab-content { border: 1px solid #ccc; padding: 10px; background: #fff; display: none; }
    .tab-content.active { display: block; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; max-height: 300px; overflow-y: auto; display: block; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: left; white-space: nowrap; }
    th { background: #eee; position: sticky; top: 0; }
    .btn { padding: 5px 10px; background: #14213d; color: #fff; border-radius: 4px; text-decoration: none; margin-bottom: 10px; display: inline-block; }
    .btn:hover { background: #0f0f2d; }
    .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); }
    .modal-content { background-color: #fff; margin: 5% auto; padding: 20px; border-radius: 6px; width: 500px; position: relative; max-height: 90vh; overflow-y: auto; }
    .close { position: absolute; top: 10px; right: 15px; font-size: 20px; cursor: pointer; }
</style>

<div class="page-header">
    <div>Клиенты > Редактировать</div>
</div>

<form method="POST" action="{{ route('clients.update', $client->id) }}" style="background:#fff;padding:20px;border-radius:6px;margin-bottom:20px;">
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
    <form id="delete-client-{{ $client->id }}" 
        action="{{ route('clients.destroy', $client->id) }}" 
        method="POST" style="display: none;">
      @csrf
      @method('DELETE')
      <button onclick="if(!confirm('Удалить клиента?')) return false " class="delete_client" data-id="{{$client->id}}" style="background:#77312f;color:#fff;padding:10px 20px;border:none;border-radius:4px;cursor:pointer;">Удалить</button>
  </form>
</form>

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

        @if(session('success'))
  <div class="successMessage" style="background: #d4edda; color: #155724; padding: 10px 15px; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 15px;">
    {{ session('success') }}
  </div>
@endif

@if(session('error'))
  <div class="errorMessage" style="background: #f8d7da; color: #721c24; padding: 10px 15px; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 15px;">
    {{ session('error') }}
  </div>
@endif
            <table>
                <thead>
                    <tr>
                        <th>VIN</th>
                        <th>Тип</th>
                        <th>Бренд</th>
                        <th>Модель</th>
                        <th>Поколение</th>
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
                            <td onclick="openVehicleModal({{ $vehicle }})">{{ $vehicle->vin }}</td>
                            <td>{{ $vehicle->vehicle_type }}</td>
                            <td>{{ $vehicle->brand }}</td>
                            <td>{{ $vehicle->model }}</td>
                            <td>{{ $vehicle->generation ?? '-' }}</td>
                            <td>{{ $vehicle->body ?? '-' }}</td>
                            <td>{{ $vehicle->modification ?? '-' }}</td>
                            <td>{{ $vehicle->registration_number ?? '-' }}</td>
                            <td>{{ $vehicle->sts ?? '-' }}</td>
                            <td>{{ $vehicle->pts ?? '-' }}</td>
                            <td>{{ $vehicle->year_of_manufacture }}</td>
                            <td>{{ $vehicle->engine_type }}</td>
                            <td><form 
                  action="{{ route('vehicles.destroy', $vehicle->id) }}" 
                  method="POST" style="">
                    @csrf
                    @method('DELETE')
                    <button onclick="if(!confirm('Удалить автомобиль?')) return false" style="background:#77312f;color:#fff;padding:10px 20px;border:none;border-radius:4px;cursor:pointer;">Удалить</button>
                </form></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <!-- Orders tab -->

    
    <div id="orders" class="tab-content">
        <a href="javascript:void(0)" class="btn" onclick="openOrderModal()">Добавить заказ</a>
        @if($client->orders->isEmpty())
            <p>У клиента нет заказов.</p>
        @else
        @if(session('success'))
        <div class="successMessage" style="background: #d4edda; color: #155724; padding: 10px 15px; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 15px;">
          {{ session('success') }}
        </div>
      @endif

      @if(session('error'))
        <div class="errorMessage" style="background: #f8d7da; color: #721c24; padding: 10px 15px; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 15px;">
          {{ session('error') }}
        </div>
      @endif  
            <table>
                <thead>
                    <tr>
                        <th>Номер заказа</th>
                        <th>Сумма</th>
                        <th>Дата создания</th>
                        <th>Автомобиль</th>
                        <th>Менеджер</th>
                        <th>Пробег</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($client->orders ?? [] as $order)
                        <tr style="cursor:pointer;" >
                            <td onclick="openOrderModal({{ $order }})">{{ $order->order_number }}</td>
                            <td>{{ $order->amount }}</td>
                            <td>{{ $order->created_at  ?  $order->created_at->format('d.m.Y') : '' }}</td>
                            <td>{{ $order->vehicle ? $order->vehicle->brand.' '.$order->vehicle->model : '-' }}</td>
                            <td>{{ $order->manager ? $order->manager->name : '-' }}</td>
                            <td>{{ $order->mileage ?? '-' }}</td>
                            <td><form  
                  action="{{ route('orders.destroy', $order->id) }}" 
                  method="POST" style="">
                    @csrf
                    @method('DELETE')
                    <button onclick="if(!confirm('Удалить заказ?')) return false" style="background:#77312f;color:#fff;padding:10px 20px;border:none;border-radius:4px;cursor:pointer;">Удалить</button>
                </form></td>
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
                      'vin'=>'VIN','vehicle_type'=>'Тип транспортного средства','brand'=>'Бренд','model'=>'Модель',
                      'generation'=>'Поколение','body'=>'Кузов','modification'=>'Модификация',
                      'registration_number'=>'Гос номер','sts'=>'СТС','pts'=>'ПТС',
                      'year_of_manufacture'=>'Год','engine_type'=>'Тип двигателя'
                  ];
              @endphp
              @foreach($fields as $name => $label)
                  <div style="margin-bottom:10px;">
                      <label>{{ $label }}</label>
                      <input type="text" name="{{ $name }}" id="vehicle_{{ $name }}" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;">
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
                <label>Номер заказа</label>
                <input type="text" name="order_number" id="order_order_number" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;">
            </div>
            <div style="margin-bottom:10px;">
                <label>Сумма заказа</label>
                <input type="text" name="amount" id="order_amount" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;">
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
                <label>Автомобиль (если есть)</label>
                <select name="vehicle_id" id="order_vehicle_id" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;">
                    <option value="">-- Не указан --</option>
                    @foreach($client->vehicles ?? [] as $vehicle)
                        <option value="{{ $vehicle->id }}">{{ $vehicle->brand }} {{ $vehicle->model }} ({{ $vehicle->vin }})</option>
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
            <button type="submit" class="btn">Сохранить</button>
        </form>
    </div>
</div>

<script>
const tabs = document.querySelectorAll('.tab');
const contents = document.querySelectorAll('.tab-content');
function activateTab(tabName) {
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
    tab.addEventListener('click', () => activateTab(tab.dataset.tab));
});

function openModal(id) { document.getElementById(id).style.display = 'block'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; }
window.onclick = function(event) {
    if(event.target.classList.contains('modal')) event.target.style.display = "none";
}

// Vehicle modal open for edit or add
function openVehicleModal(vehicle = null) { 
 
  
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
        for(let key in vehicle) {
            if(document.getElementById('vehicle_'+key)) document.getElementById('vehicle_'+key).value = vehicle[key] ?? '';
        }
    } else {
        document.getElementById('vehicleModalTitle').innerText = 'Добавить автомобиль';
        form.action = '{{ route("vehicles.store") }}';
        form.method = 'POST';
        form.querySelectorAll('input').forEach(i => { if(i.type !== 'hidden') i.value = ''; });
    }
    openModal('vehicleModal');
}

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
        form.querySelectorAll('input').forEach(i => { if(i.type !== 'hidden') i.value = ''; });
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

</script>
@endsection
