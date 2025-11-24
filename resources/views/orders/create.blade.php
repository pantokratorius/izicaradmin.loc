@extends('layouts.app')

@section('title', 'Создать заказ')

@section('content')
<h1>Создать заказ</h1>

<form id="orderForm" action="{{ route('orders.store') }}" method="POST">
    @csrf

    <!-- Клиент -->
    <div class="form-group">
        <label for="client_search">Клиент</label>
        <input type="hidden" name="client_id" id="client_id">
        <input type="text" id="client_search" placeholder="Введите имя или телефон клиента">
        <ul id="client_results" class="dropdown"></ul>
    </div>

    <!-- Автомобиль -->
    <div class="form-group">
        <label for="vehicle_search">Автомобиль</label>
        <input type="hidden" name="vehicle_id" id="vehicle_id">
        <input type="text" id="vehicle_search" placeholder="Введите VIN, марку или модель">
        <ul id="vehicle_results" class="dropdown"></ul>
    </div>

    <!-- № заказа -->
    <div class="form-group">
        <label for="order_number">№ заказа</label>
        <input type="text" id="order_number" name="order_number" value="{{ old('order_number', $orders_count) }}">
    </div>

    <!-- Дата -->
    <div class="form-group">
        <label for="created_at">Дата создания</label>
        <input type="date" id="created_at" name="created_at" value="{{ old('created_at', now()->format('Y-m-d')) }}">
    </div>

    <!-- Пробег -->
    <div class="form-group">
        <label for="mileage">Пробег</label>
        <input type="text" id="mileage" name="mileage" value="{{ old('mileage') }}">
    </div>

    <!-- Предоплата -->
    <div class="form-group">
        <label for="prepayment">Предоплата</label>
        <input type="text" id="prepayment" name="prepayment" value="{{ old('prepayment') }}">
    </div>

        <!-- Ответственный менеджер -->
    <div class="form-group">
        <label for="manager_id">Ответственный менеджер</label>
        <select id="manager_id" name="manager_id">
            <option value="">-- Не указан --</option>
            @foreach($managers as $manager)
                <option value="{{ $manager->id }}">
                    {{ $manager->name }}
                </option>
            @endforeach
        </select>
    </div>

    <!-- Статус -->
    <div class="form-group">
        <label for="status">Статус заказа</label>
        <select id="status" name="status">
            <option value="1">Новый</option>
            <option value="2">В работе</option>
            <option value="3">Пришел</option>
            <option value="4">Выдан</option>
            <option value="5">Отменен</option>
        </select>
    </div>

    <!-- Наценка -->
    <div class="form-group">
        <label for="margin">Наценка %</label>
        <input type="text" id="margin" name="margin" value="{{ old('margin') }}">
    </div>

    <!-- Комментарий -->
    <div class="form-group">
        <label for="comment">Комментарий</label>
        <input type="text" id="comment" name="comment" value="{{ old('comment') }}">
    </div>

    <button type="submit" class="btn">Создать</button>
</form>

<style>
.form-group { margin-bottom: 15px; }
label { display: block; margin-bottom: 5px; font-weight: bold; }
input, select { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
button.btn { padding: 10px 20px; background: #2d89ef; color: white; border: none; border-radius: 4px; cursor: pointer; }
button.btn:hover { background: #1b5fbd; }
.dropdown { list-style: none; padding: 0; margin: 0; border: 1px solid #ccc; max-height: 150px; overflow-y: auto; position: absolute; background: white; width: 100%; z-index: 1000; display: none; }
.dropdown li { padding: 6px; cursor: pointer; }
.dropdown li:hover { background: #eee; }
</style>

<script>
// Generic search dropdown
function setupSearch(inputId, hiddenId, resultsId, url) {
    const input = document.getElementById(inputId);
    const hidden = document.getElementById(hiddenId);
    const results = document.getElementById(resultsId);

    input.addEventListener("input", async () => {
        const query = input.value.trim();
        if (query.length < 2) {
            results.style.display = "none";
            return;
        }

        const res = await fetch(url + "?q=" + encodeURIComponent(query));
        const data = await res.json();

        results.innerHTML = "";
        if (data.length > 0) {
            data.forEach(item => {
                const li = document.createElement("li");
                li.textContent = item.text;
                li.addEventListener("click", () => {
                    input.value = item.text;
                    hidden.value = item.id;
                    results.style.display = "none";

                    // Trigger event for vehicle population if client selected
                    if(inputId === 'client_search') {
                        populateVehicles(item.id);
                    }
                });
                results.appendChild(li);
            });
            results.style.display = "block";
        } else {
            results.style.display = "none";
        }
    });

    document.addEventListener("click", e => {
        if (!results.contains(e.target) && e.target !== input) {
            results.style.display = "none";
        }
    });
}

// Populate vehicles automatically when client selected
async function populateVehicles(clientId) {
    const vehicleInput = document.getElementById('vehicle_search');
    const vehicleHidden = document.getElementById('vehicle_id');
    const vehicleResults = document.getElementById('vehicle_results');

    vehicleInput.value = '';
    vehicleHidden.value = '';
    vehicleResults.innerHTML = '';
    vehicleResults.style.display = 'none';

    if(!clientId) return;

    try {
        const res = await fetch(`/clients/${clientId}/vehicles`);
        const vehicles = await res.json();

        if(vehicles.length === 1) {
            // Auto-fill if only one vehicle
            const vehicle = vehicles[0];
            vehicleInput.value = `${vehicle.brand_name || '-'} ${vehicle.model_name || '-'} (${vehicle.plate_number || ''})`;
            vehicleHidden.value = vehicle.id;
        } else if(vehicles.length > 1) {
            // Populate dropdown for multiple vehicles
            vehicles.forEach(vehicle => {
                const li = document.createElement('li');
                li.textContent = `${vehicle.brand_name || '-'} ${vehicle.model_name || '-'} (${vehicle.plate_number || ''})`;
                li.addEventListener('click', () => {
                    vehicleInput.value = li.textContent;
                    vehicleHidden.value = vehicle.id;
                    vehicleResults.style.display = 'none';
                });
                vehicleResults.appendChild(li);
            });
            vehicleResults.style.display = 'block';
        }
    } catch(err) {
        console.error(err);
    }
}

document.addEventListener("DOMContentLoaded", () => {

    const preselectedVehicle = @json($vehicle);
    const preselectedClient  = @json($client);



    setupSearch("client_search", "client_id", "client_results", "{{ route('clients.search') }}");
    setupSearch("vehicle_search", "vehicle_id", "vehicle_results", "{{ route('vehicles.search') }}");

    if (preselectedClient) {
        // Fill client hidden input
        document.getElementById('client_id').value = preselectedClient.id;

        // Fill client visible input
        document.getElementById('client_search').value =
            `${preselectedClient.first_name} ${preselectedClient.middle_name ?? ''} ${preselectedClient.last_name ?? ''} (${preselectedClient.phone ?? ''})`;

        // Load vehicles for this client
        populateVehicles(preselectedClient.id);
    }

    if (preselectedVehicle) {
        // Fill vehicle hidden input
        document.getElementById('vehicle_id').value = preselectedVehicle.id;

        // Fill vehicle visible input
        document.getElementById('vehicle_search').value =
            `${preselectedVehicle.brand_name || ''} ${preselectedVehicle.model_name || ''} (${preselectedVehicle.plate_number || ''})`;
    }

});
</script>


@endsection
