@extends('layouts.app')

@section('title', 'Склад')

@section('content')
<h1>Склад</h1>

<a href="{{ route('search.index') }}" class="btn">➕ Добавить товар</a>

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
    @foreach($searches as $search)
<tr class="editable-row" data-edit-url="{{ route('stocks.edit', $search) }}">
    <td class="edit">{{ $search->id }}</td>
    <td class="edit">{{ $search->name }}</td>
    <td class="edit">{{ $search->part_make }}</td>
    <td class="edit">{{ $search->part_number }}</td>
    <td class="edit">{{ $search->quantity }}</td>
    <td class="edit">{{ $search->purchase_price }}</td>
    <td class="edit">{{ $search->sell_price }}</td>
    <td class="edit">{{ $search->warehouse }}</td>
    <td class="edit">{{ $search->supplier }}</td>
    <td style="text-align: center">
        <a style="display: none" href="{{ route('stocks.edit', $search) }}">✏️</a>
        <form action="{{ route('stocks.destroy', $search) }}" method="POST" style="display:inline;">
            @csrf @method('DELETE')
            <button style="cursor: pointer" type="submit" onclick="return confirm('Удалить?')">🗑</button> 
        </form>
    </td>
</tr>
@endforeach
    </tbody>
</table>
<p>
<select onchange="openPrint(this, {{ $search->id }})" class="print-select">
    <option value="">🖨️ Печать...</option>
    <option value="{{ route('search.print', $search->id) }}">Заказ 1</option>
    <option value="{{ route('search.print2', $search->id) }}">Заказ 2</option>
</select>

                </p>
{{ $searches->links() }}

<script>
function openPrint(select, orderId) {
    if (select.value) {
        window.open(select.value, '_blank');
        select.selectedIndex = 0; // сбрасываем обратно на первый вариант
    }
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.editable-row').forEach(row => {
        row.addEventListener('click', () => {
            const url = row.dataset.editUrl;
            if(url) window.location.href = url;
        });
    });
});
</script>

<style>
.print-select {
    display: inline-block;
    width: auto;
    padding: 6px 12px;
    border-radius: 4px;
    background-color: #6c757d; /* как .btn-secondary */
    color: #fff;
    border: 1px solid #6c757d;
    cursor: pointer;
    font-weight: 500;
    appearance: none; /* убираем стандартную стрелку */
    background-image: url("data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%23fff' viewBox='0 0 16 16'%3E%3Cpath d='M1.5 5.5l6 6 6-6'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    background-size: 1rem;
    padding-right: 2rem;
}
.print-select:hover {
    background-color: #5a6268;
    border-color: #545b62;
}
</style>

@endsection
