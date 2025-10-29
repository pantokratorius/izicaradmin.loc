@extends('layouts.app')

@section('title', 'Поиск товара')
<meta name="csrf-token" content="{{ csrf_token() }}">
@section('content')
<h1>Добавить товар</h1>

<form method="POST" action="{{ route('stocks.store') }}">
    @csrf
    @include('stocks.partials.form3')
    @include('stocks.partials.form')
    <button type="submit" class="btn">Сохранить</button>
</form>
@endsection
