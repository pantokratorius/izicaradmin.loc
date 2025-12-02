@extends('layouts.app')

@section('title', 'Добавить позицию')
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="order-id" content="{{ isset($draft) ? $draftOrder->id  : $order->id }}">

@section('content')
<h1>Добавить позицию</h1>
<h4>Заказ № {{ isset($draft) ? $draftOrder->order_number : $order->order_number}}</h4>
@isset($draft)
    <a href="{{ route('draft-orders.show', $draftOrder) }}">← Назад к заказу</a>
@else
    <a href="{{ route('orders.show', $order->id) }}">← Назад к заказу</a>
@endif
<br><br><br>
<form method="POST" action="{{ route('orderitems.store') }}">
    @csrf
    @include('order_items.partials.form3')
    @include('order_items.partials.form')
    <button type="submit" class="btn">Сохранить</button>
</form>
@endsection
