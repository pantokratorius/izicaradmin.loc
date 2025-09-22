@extends('layouts.app')

@section('title', 'Редактировать товар')

@section('content')
<h1>Редактировать товар</h1>

<form method="POST" action="{{ route('stocks.update', $stock) }}">
    @csrf
    @method('PUT')
    @include('stocks.partials.form')
    <button type="submit" class="btn">Обновить</button>
</form>
@endsection
