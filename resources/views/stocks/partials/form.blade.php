{{-- <div class="form-group">
    <label>Артикул</label>
    <input type="text" id="searchInput"  style="width: 200px; margin-right: 10px" name="part_number" value="{{ old('part_number', $stock->part_number ?? '') }}">
    <button type="submit" id="searchButton" class="btn">Поиск</button>
</div> --}}
<br><br>

<div class="form-group">
    <label>Бренд</label>
    <input type="text" name="part_make" value="{{ old('part_make', $stock->part_make ?? '') }}">
</div>

<div class="form-group">
    <label>Название</label>
    <input type="text" name="name" value="{{ old('name', $stock->name ?? '') }}">
</div>

<div class="form-group">
    <label>Количество</label>
    <input type="number" name="quantity" value="{{ old('quantity', $stock->quantity ?? 0) }}">
</div>

<div class="form-group">
    <label>Цена закупки</label>
    <input type="number" step="0.01" name="purchase_price" value="{{ old('purchase_price', $stock->purchase_price ?? '') }}">
</div>

<div class="form-group">
    <label>Цена продажи</label>
    <input type="number" step="0.01" name="sell_price" value="{{ old('sell_price', $stock->sell_price ?? '') }}">
</div>

<div class="form-group">
    <label>Склад</label>
    <input type="text" name="warehouse" value="{{ old('warehouse', $stock->warehouse ?? '') }}">
</div>

@php
    $suppliers = ["ABS","Москворечье", "Берг", "Фаворит", "Форум-Авто", 
                        "Профит Лига", "Микадо", "Росско", "STparts", "Авторусь", 
                        "Автоспутник", "Авто-Евро", "Авто Союз", "Ats-Auto", "АвтоТрейд"];
@endphp


<div class="form-group">
    <label>Поставщик</label>
    <select name="supplier">
        <option value="">Выберите поставщика</option>
        @foreach($suppliers as $v)
            <option value="{{$v}}" @if($v == $stock?->supplier) selected @endif>{{$v}}</option>
        @endforeach
    </select>
</div>



<style>
.form-group { margin-bottom: 15px; }
label { display: block; margin-bottom: 5px; font-weight: bold; }
select, input { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
button { padding: 10px 20px; background: #2d89ef; color: white; border: none; border-radius: 4px; cursor: pointer; }
button:hover { background: #1b5fbd; }
.accordion-toggle { background: #2d89ef; color: white; padding: 8px 12px; border: none; border-radius: 4px; cursor: pointer; margin-bottom: 10px; }
.accordion-toggle:hover { background: #1b5fbd; }
.accordion-content .form-group { margin-bottom: 10px; }
</style>