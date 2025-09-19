@extends('layouts.app')

@section('title', 'Редактировать транспортное средство')

@section('content')
<h1>Редактировать транспортное средство</h1>

<form action="{{ route('vehicles.update', $vehicle->id) }}" method="POST">
    @csrf
    @method('PUT')

    <!-- Марка -->
    <div class="form-group">
        <label for="car_brand_id">Марка</label>
        <select id="car_brand_id" name="car_brand_id" 
                data-current="{{ $vehicle->car_brand_id }}" 
                data-url="/cars/models/{id}">
            <option value="">Выберите марку</option>
            @foreach($brands as $brand)
                <option value="{{ $brand->id }}" {{ $vehicle->car_brand_id == $brand->id ? 'selected' : '' }}>
                    {{ $brand->name }}
                </option>
            @endforeach
        </select>
    </div>

    <!-- Модель -->
    <div class="form-group">
        <label for="car_model_id">Модель</label>
        <select id="car_model_id" name="car_model_id" data-current="{{ $vehicle->car_model_id }}" data-url="/cars/generations/{id}">
            <option value="">Выберите модель</option>
        </select>
    </div>

    <!-- Поколение -->
    <div class="form-group">
        <label for="car_generation_id">Поколение</label>
        <select id="car_generation_id" name="car_generation_id" data-current="{{ $vehicle->car_generation_id }}" data-url="/cars/series/{id}">
            <option value="">Выберите поколение</option>
        </select>
    </div>

    <!-- Серия -->
    <div class="form-group">
        <label for="car_serie_id">Серия</label>
        <select id="car_serie_id" name="car_serie_id" data-current="{{ $vehicle->car_serie_id }}" data-url="/cars/modifications/{id}">
            <option value="">Выберите серию</option>
        </select>
    </div>

    <!-- Модификация -->
    <div class="form-group">
        <label for="car_modification_id">Модификация</label>
        <select id="car_modification_id" name="car_modification_id" data-current="{{ $vehicle->car_modification_id }}">
            <option value="">Выберите модификацию</option>
        </select>
    </div>

    @php
        $accordionOpen = $vehicle->brand_name || $vehicle->model_name || $vehicle->generation_name || $vehicle->serie_name || $vehicle->modification_name;
    @endphp

    <div class="accordion">
    <button type="button" class="accordion-toggle">Показать/Скрыть для внесения вручную</button>
    <div class="accordion-content" style="display: {{ $accordionOpen ? 'block' : 'none' }}; margin-top:10px;">
        <div class="form-group">
            <label for="brand_name">Марка</label>
            <input type="text" id="brand_name" name="brand_name" value="{{ $vehicle->brand_name }}" style="background: #dbdbdb"/>
        </div>
        <div class="form-group">
            <label for="model_name">Модель</label>
            <input type="text" id="model_name" name="model_name" value="{{ $vehicle->model_name }}" style="background: #dbdbdb"/>
        </div>
        <div class="form-group">
            <label for="generation_name">Поколение</label>
            <input type="text" id="generation_name" name="generation_name" value="{{ $vehicle->generation_name }}" style="background: #dbdbdb"/>
        </div>
        <div class="form-group">
            <label for="serie_name">Серия</label>
            <input type="text" id="serie_name" name="serie_name" value="{{ $vehicle->serie_name }}" style="background: #dbdbdb"/>
        </div>
        <div class="form-group">
            <label for="modification_name">Модификация</label>
            <input type="text" id="modification_name" name="modification_name" value="{{ $vehicle->modification_name }}" style="background: #dbdbdb"/>
        </div>
    </div>
</div>

    <!-- VIN -->
    <div class="form-group">
        <label for="vin">VIN</label>
        <input type="text" id="vin" name="vin" value="{{ $vehicle->vin }}">
    </div>

    <!-- Тип транспортного средства -->
    <div class="form-group">
        <label for="vehicle_type">Тип транспортного средства</label>
        <input type="text" id="vehicle_type" name="vehicle_type" value="{{ $vehicle->vehicle_type }}">
    </div>

    <!-- Кузов -->
    <div class="form-group">
        <label for="body_type">Кузов</label>
        <input type="text" id="body_type" name="body_type" value="{{ $vehicle->body_type }}">
    </div>

    <!-- Гос номер -->
    <div class="form-group">
        <label for="plate_number">Гос номер</label>
        <input type="text" id="plate_number" name="plate_number" value="{{ $vehicle->plate_number }}">
    </div>

    <!-- СТС -->
    <div class="form-group">
        <label for="sts">СТС</label>
        <input type="text" id="sts" name="sts" value="{{ $vehicle->sts }}">
    </div>

    <!-- ПТС -->
    <div class="form-group">
        <label for="pts">ПТС</label>
        <input type="text" id="pts" name="pts" value="{{ $vehicle->pts }}">
    </div>

    <!-- Год -->
    <div class="form-group">
        <label for="year">Год</label>
        <input type="number" id="year" name="year" value="{{ $vehicle->year }}">
    </div>

    <!-- Тип двигателя -->
    <div class="form-group">
        <label for="engine_type">Тип двигателя</label>
        <input type="text" id="engine_type" name="engine_type" value="{{ $vehicle->engine_type }}">
    </div>

    <button type="submit">Сохранить</button>
</form>

<style>
/* Pure CSS */
.form-group { margin-bottom: 15px; }
label { display: block; margin-bottom: 5px; font-weight: bold; }
select, input { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
button { padding: 10px 20px; background: #2d89ef; color: white; border: none; border-radius: 4px; cursor: pointer; }
button:hover { background: #1b5fbd; }
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

<script>
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

    
document.addEventListener("DOMContentLoaded", function () {

    const brand = document.getElementById("car_brand_id");
    const model = document.getElementById("car_model_id");
    const generation = document.getElementById("car_generation_id");
    const serie = document.getElementById("car_serie_id");
    const modification = document.getElementById("car_modification_id");

    function fetchOptions(url, selectEl, currentValue, placeholder = "Выберите") {
        selectEl.innerHTML = `<option value="">Загрузка…</option>`;
        fetch(url)
            .then(res => res.json())
            .then(data => {
                selectEl.innerHTML = `<option value="">${placeholder}</option>`;
                data.forEach(item => {
                    const opt = document.createElement("option");
                    opt.value = item.id;
                    opt.textContent = item.name;
                    if (currentValue && currentValue == item.id) opt.selected = true;
                    selectEl.appendChild(opt);
                });
            })
            .catch(() => {
                selectEl.innerHTML = `<option value="">Ошибка загрузки</option>`;
            });
    }

    // Prefill selects on edit page
    if (brand.value) {
        fetchOptions(`/cars/models/${brand.value}`, model, model.dataset.current, "Выберите модель");
    }
    if (model.dataset.current) {
        fetchOptions(`/cars/generations/${model.dataset.current}`, generation, generation.dataset.current, "Выберите поколение");
    }
    if (generation.dataset.current) {
        fetchOptions(`/cars/series/${generation.dataset.current}`, serie, serie.dataset.current, "Выберите серию");
    }
    if (serie.dataset.current) {
        fetchOptions(`/cars/modifications/${serie.dataset.current}`, modification, modification.dataset.current, "Выберите модификацию");
    }

    // Dynamic updates
    brand.addEventListener("change", function () {
        fetchOptions(`/cars/models/${this.value}`, model, null, "Выберите модель");
        generation.innerHTML = "<option value=''>Выберите поколение</option>";
        serie.innerHTML = "<option value=''>Выберите серию</option>";
        modification.innerHTML = "<option value=''>Выберите модификацию</option>";
    });

    model.addEventListener("change", function () {
        fetchOptions(`/cars/generations/${this.value}`, generation, null, "Выберите поколение");
        serie.innerHTML = "<option value=''>Выберите серию</option>";
        modification.innerHTML = "<option value=''>Выберите модификацию</option>";
    });

    generation.addEventListener("change", function () {
        fetchOptions(`/cars/series/${this.value}`, serie, null, "Выберите серию");
        modification.innerHTML = "<option value=''>Выберите модификацию</option>";
    });

    serie.addEventListener("change", function () {
        fetchOptions(`/cars/modifications/${this.value}`, modification, null, "Выберите модификацию");
    });

});
</script>

@endsection
