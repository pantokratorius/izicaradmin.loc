@extends('layouts.app')

@section('title', 'Склад')

@section('content')
<h1>Склад</h1>

<a href="{{ route('stocks.create') }}" class="btn">➕ Добавить товар</a>

<table border="1" cellspacing="0" cellpadding="8" style="margin-top:15px;width:100%;">
    <thead>
        <tr>
            <th>ID</th>
            <th>Название</th>
            <th>Бренд</th>
            <th>Артикул</th>
            <th>Количество</th>
            <th>Цена продажи</th>
            <th>Склад</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        @foreach($stocks as $stock)
        <tr>
            <td>{{ $stock->id }}</td>
            <td>{{ $stock->name }}</td>
            <td>{{ $stock->part_make }}</td>
            <td>{{ $stock->part_number }}</td>
            <td>{{ $stock->quantity }}</td>
            <td>{{ $stock->sell_price }}</td>
            <td>{{ $stock->warehouse }}</td>
            <td>
                <a href="{{ route('stocks.edit', $stock) }}">✏️</a>
                <form action="{{ route('stocks.destroy', $stock) }}" method="POST" style="display:inline;">
                    @csrf @method('DELETE')
                    <button type="submit" onclick="return confirm('Удалить?')">🗑</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

{{ $stocks->links() }}
@endsection
