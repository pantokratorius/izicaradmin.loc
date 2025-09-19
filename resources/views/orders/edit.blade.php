@extends('layouts.app')

@section('title', 'Редактировать заказ')

@section('content')
<h1>Редактировать заказ</h1>

<form id="orderForm" action="{{ route('orders.update', $order->id) }}" method="POST">
    @csrf
    @method('PUT')

    <input type="hidden" name="client_id" value="{{ $order->client_id }}">

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

    <!-- Автомобиль -->
    <div class="form-group">
        <label for="vehicle_id">Автомобиль (если есть)</label>
        <select id="vehicle_id" name="vehicle_id">
            <option value="">-- Не указан --</option>
            @foreach($vehicles as $vehicle)
                <option value="{{ $vehicle->id }}" 
                        {{ $order->vehicle_id == $vehicle->id ? 'selected' : '' }}>
                    {{ $vehicle->brand->name ?? '' }} {{ $vehicle->model->name ?? '' }} ({{ $vehicle->vin }})
                </option>
            @endforeach
        </select>
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
            <option value="3" {{ old('status', $order->status) === 3 ? 'selected' : '' }}>Завершен</option>
            <option value="4" {{ old('status', $order->status) === 4 ? 'selected' : '' }}>Отменен</option>
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
</style>
@endsection
