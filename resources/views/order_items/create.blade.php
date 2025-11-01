@extends('layouts.app')

@section('title', 'Добавить позицию')
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="order-id" content="{{ $order->id }}">

@section('content')
<h1>Добавить позицию</h1>
<h4>Заказ № {{ $order->order_number }}</h4>
<a href="#" onclick="window.history.back()">← Назад к заказу</a>
<br><br><br>
<form method="POST" action="{{ route('orderitems.store') }}">
    @csrf
    @include('order_items.partials.form3')
    @include('order_items.partials.form')
    <button type="submit" class="btn">Сохранить</button>
</form>
@endsection
