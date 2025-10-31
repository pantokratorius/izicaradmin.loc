<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Корзина</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 14px; margin: 40px; }
        h3 { text-align: center; margin: 15px 0; }
        .logo { text-align: center; margin-bottom: 10px; }
        .logo img { max-height: 80px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; text-align: center}
        th { background: #f0f0f0; }
        .right { text-align: right; }
        .no-border td { border: none; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

<div class="logo">
    <img src="{{ asset('logo.png') }}" alt="Логотип">
</div>

<br>



<table>
    <thead>
        <tr>
            <th>№</th>
            <th>Артикул</th>
            <th>Наименование</th>
            <th>Производитель</th>
            <th>Кол-во</th>
            <th>Цена</th>
            <th>Сумма</th>
        </tr>
    </thead>
    <tbody>
        @foreach($search as $i => $item)
        <tr>
            <td>{{ $item->id}}</td>
            <td>{{ $item->part_number }}</td>
            <td>{{ $item->name }}</td>
            <td>{{ $item->part_make }}</td>
            <td>{{ $item->quantity }}</td>
            <td>{{ number_format($item->purchase_price, 2, ',', ' ') }}</td>
            <td>{{ number_format($item->sell_price, 2, ',', ' ') }}</td>
        </tr>
        @endforeach
        <tr>
            <td colspan="5" class="right"><strong>Итого:</strong></td>
            <td></td>
            <td></td>
        </tr>
    </tbody>
</table>

<br><br>

<table class="no-border">
    <tr>
        <td>Предоплата:  р.</td>
        <td></td>
    </tr>
    <tr>
        <td>Остаток:  р.</td>
        <td>Тел: </td>
    </tr>
</table>

<br><br><br>
<div class="right">Заказчик _____________________</div>

<div class="no-print" style="margin-top:20px; text-align:left;">
    <button onclick="window.print()">🖨️ Печать</button>
</div>

{{-- <script>
    document.addEventListener('DOMContentLoaded', function(){
        window.print()
    })

    window.onafterprint = function(){
        window.close()
    }

    window.onfocus=function(){ window.close();}
</script> --}}

</body>
</html>
