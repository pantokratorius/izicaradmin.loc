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
            <th></th>
        </tr>
    </thead>
    <tbody>
        @foreach($stocks as $stock)
        <tr>
            <td class="edit">{{ $stock->id }}</td>
            <td class="edit">{{ $stock->name }}</td>
            <td class="edit">{{ $stock->part_make }}</td>
            <td class="edit">{{ $stock->part_number }}</td>
            <td class="edit">{{ $stock->quantity }}</td>
            <td class="edit">{{ $stock->sell_price }}</td>
            <td class="edit">{{ $stock->warehouse }}</td>
            <td style="text-align: center">
                <a style="display: none" href="{{ route('stocks.edit', $stock) }}">✏️</a>
                <form action="{{ route('stocks.destroy', $stock) }}" method="POST" style="display:inline;">
                    @csrf @method('DELETE')
                    <button style="cursor: pointer" type="submit" onclick="return confirm('Удалить?')">🗑</button>
                </form>
            </td>
        </tr>
         @if($loop->last)
        <script>
            [...document.querySelectorAll('.edit')].forEach(i => {
                i.addEventListener('click', function(){
                    location='{{ route('stocks.edit', $stock) }}'
                })
            });
        </script>
        @endif
        @endforeach
    </tbody>
</table>

{{ $stocks->links() }}



@endsection
