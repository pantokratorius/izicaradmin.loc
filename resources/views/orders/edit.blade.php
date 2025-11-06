@extends('layouts.app')

@section('title', 'Редактировать заказ')

@section('content')
<h1>Редактировать заказ</h1>

<form id="orderForm" action="{{ route('orders.update', $order->id) }}" method="POST">
    @csrf
    @method('PUT')

    <input type="hidden" name="client_id" value="{{ $order->client_id }}">


    <!-- Клиент -->
    <div class="form-group">
        <label>Клиент</label>
        <div class="custom-select" id="client-wrapper">
            <input type="text" placeholder="Выберите клиента..." class="select-search" 
                   value="{{ $order->client->first_name ?? '' }} {{ $order->client->middle_name ?? '' }} {{ $order->client->last_name ?? '' }} {{ $order->client->phone ?? '' }}">
            <ul class="select-options" style="display:none;"></ul>
        </div>
    </div>

    <!-- Автомобиль -->
    <div class="form-group">
        <label>Автомобиль</label>
        <div class="custom-select" id="vehicle-wrapper">
            <input type="text" placeholder="Выберите автомобиль..." class="select-search" 
                   value="{{ $order->vehicle->brand->name ?? $order->vehicle->brand_name ?? ''}} {{ $order->vehicle->model->name ?? $order->vehicle->model_name ?? '' }} {{ $order->vehicle->vin ?? ''}}"
                   {{ $order->vehicle_id ? '' : 'disabled' }}>
            <ul class="select-options" style="display:none;"></ul>
        </div>
    </div>

    <!-- № заказа -->
    <div class="form-group">
        <label for="order_number">№ заказа</label>
        <input type="text" id="order_number" name="order_number" 
               value="{{ old('order_number', $order->order_number) }}">
    </div>

    <!-- Дата создания -->
    <div class="form-group">
        <label for="created_at">Дата создания</label>
        <input type="date" id="created_at" name="created_at" 
               value="{{ old('created_at', $order->created_at->format('Y-m-d')) }}">
    </div>

    <!-- Пробег -->
    <div class="form-group">
        <label for="mileage">Пробег</label>
        <input type="text" id="mileage" name="mileage" 
               value="{{ old('mileage', $order->mileage) }}">
    </div>

    <!-- Предоплата -->
    <div class="form-group">
        <label for="prepayment">Предоплата</label>
        <input type="text" id="prepayment" name="prepayment" 
               value="{{ old('prepayment', $order->prepayment) }}">
    </div>



    <!-- Ответственный менеджер -->
    <div class="form-group">
        <label for="manager_id">Ответственный менеджер</label>
        <select id="manager_id" name="manager_id">
            <option value="">-- Не указан --</option>
            @foreach($managers as $manager)
                <option value="{{ $manager->id }}" 
                        {{ $order->manager_id == $manager->id ? 'selected' : '' }}>
                    {{ $manager->name }}
                </option>
            @endforeach
        </select>
    </div>


    <!-- Статус заказа -->
    <div class="form-group">
        <label for="status">Статус заказа</label>
        <select id="status" name="status">
            <option value="1" {{ old('status', $order->status) === 1 ? 'selected' : '' }}>Новый</option>
            <option value="2" {{ old('status', $order->status) === 2 ? 'selected' : '' }}>В работе</option>
            <option value="3" {{ old('status', $order->status) === 3 ? 'selected' : '' }}>Пришел</option>
            <option value="4" {{ old('status', $order->status) === 4 ? 'selected' : '' }}>Выдан</option>
            <option value="5" {{ old('status', $order->status) === 5 ? 'selected' : '' }}>Отменен</option>
        </select>
    </div>
    
    <!-- Наценка -->
    <div class="form-group">
        <label for="margin">Наценка %</label>
        <input type="text" id="margin" name="margin" 
               value="{{ old('margin', $order->margin) }}">
    </div>

    <!-- Комментарий -->
    <div class="form-group">
        <label for="comment">Комментарий</label>
        <input type="text" id="comment" name="comment" 
               value="{{ old('comment', $order->comment) }}">
    </div>

    <button type="submit" class="btn">Сохранить</button>
</form>

<style>
.form-group {
    margin-bottom: 15px;
}
label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}
input, select {
    width: 100%;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
}
button.btn {
    padding: 10px 20px;
    background: #2d89ef;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}
button.btn:hover {
    background: #1b5fbd;
}

/* Custom select styles */
.custom-select {
    position: relative;
}
.select-search {
    width: 100%;
    padding: 8px;
    box-sizing: border-box;
}
.select-options {
    position: absolute;
    width: 100%;
    background: white;
    max-height: 200px;
    border: 1px solid #ddd;
    overflow-y: auto;
    z-index: 1000;
    list-style: none;
}
.select-options li {
    padding: 8px;
    cursor: pointer;
}
.select-options li:hover {
    background: #f0f0f0;
}
</style>

<!-- Custom select logic -->
<script>

function createCustomSelect(wrapperId, optionsData, hiddenInputName) {
    const wrapper = document.getElementById(wrapperId);
    if(!wrapper) return; // stop if wrapper not found

    const input = wrapper.querySelector('.select-search');
    const ul = wrapper.querySelector('.select-options');

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
                if(hiddenInput) hiddenInput.value = opt.value; // safety check
                ul.style.display = 'none';
                if(typeof opt.onSelect === 'function') await opt.onSelect(opt.value);
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

const clientSelect = createCustomSelect('client-wrapper', [], 'client_id');
const vehicleSelect = createCustomSelect('vehicle-wrapper', [], 'vehicle_id');

// Load clients on page load
fetch('/clients/list')
    .then(response => response.json())
    .then(data => {
        const options = data.map(client => ({
            text: `${client.first_name ?? ''} ${client.middle_name ?? ''} ${client.last_name ?? ''} (${client.phone ?? ''})`,
            value: client.id,
            onSelect: loadVehiclesForClient
        }));
        clientSelect.renderOptions(options);
    });

// Load vehicles for selected client
function loadVehiclesForClient(clientId) {
    const input = document.querySelector('#vehicle-wrapper .select-search');
    input.disabled = false;

    fetch(`/vehicles/by-client/${clientId}`)
        .then(response => response.json())
        .then(data => {
            const options = data.map(vehicle => ({
                text: vehicle.text,
                value: vehicle.id
            }));
            vehicleSelect.renderOptions(options);
        });
}
</script>
@endsection
