@extends('layouts.app')

@section('title', 'Склад')

@section('content')
<h1>Склад</h1>

<a href="{{ route('stocks.create') }}" class="btn">➕ Добавить товар</a>

<table border="0" cellspacing="0" cellpadding="8" style="margin-top:15px;width:100%;">
    <thead>
        <tr>
            <th>ID</th>
            <th>Название</th>
            <th>Бренд</th>
            <th>Артикул</th>
            <th>Количество</th>
            <th>Цена покупки</th>
            <th>Цена продажи</th>
            <th>Склад</th>
            <th>Поставщик</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    @foreach($stocks as $stock)
<tr class="editable-row" data-edit-url="{{ route('stocks.edit', $stock) }}">
    <td class="edit">{{ $stock->id }}</td>
    <td class="edit">{{ $stock->name }}</td>
    <td class="edit">{{ $stock->part_make }}</td>
    <td class="edit">{{ $stock->part_number }}</td>
    <td class="edit">{{ $stock->quantity }}</td>
    <td class="edit">{{ $stock->purchase_price }}</td>
    <td class="edit">{{ $stock->sell_price }}</td>
    <td class="edit">{{ $stock->warehouse }}</td>
    <td class="edit">{{ $stock->supplier }}</td>
    <td style="text-align: center">
        <a style="display: none" href="{{ route('stocks.edit', $stock) }}">✏️</a>
        <form action="{{ route('stocks.destroy', $stock) }}" method="POST" style="display:inline;">
            @csrf @method('DELETE')
            <button style="cursor: pointer" type="submit" onclick="return confirm('Удалить?')">🗑</button> 
        </form>
    </td>
</tr>
@endforeach
    </tbody>
</table>
<x-pagination :paginator="$stocks" />

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.editable-row').forEach(row => {
        row.addEventListener('click', () => {
            const url = row.dataset.editUrl;
            if(url) window.location.href = url;
        });
    });
});
</script>

@endsection
