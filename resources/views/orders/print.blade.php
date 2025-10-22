<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Заказ №{{ $order->order_number }}</title>
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

<h3>ДИСТАНЦИОННЫЙ ЗАКАЗ ТОВАРА №{{ $order->order_number }} от {{ $order->created_at->format('d.m.Y') }}</h3>

<p>
    Марка автомобиля: {{  $order->vehicle->brand->name ?? '-'}}<br>
    VIN: {{ $order->vehicle->vin ?? '-' }}
</p>

<table>
    <thead>
        <tr>
            <th>№</th>
            <th>Артикул</th>
            <th>Наименование</th>
            <th>Кол-во</th>
            <th>Цена</th>
            <th>Сумма</th>
        </tr>
    </thead>
    <tbody>

@php
    $total = 0;
@endphp

        @foreach ($order->items as $i => $item)
       
        @php($total += $item->summ) 

        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $item->part_number }}</td>
            <td>{{ $item->part_name }}</td>
            <td>{{ $item->quantity }}</td>
            <td>{{ number_format($item->amount, 2, ',', ' ') }}</td>
            <td>{{ number_format($item->summ, 2, ',', ' ') }}</td>
        </tr>
        @endforeach
        <tr>
            <td colspan="5" class="right"><strong>Итого:</strong></td>
            <td><strong>{{ number_format($total, 2, ',', ' ') }}</strong></td>
        </tr>
    </tbody>
</table>

<br><br>

<table class="no-border">
    <tr>
        <td>Предоплата: {{ number_format($order->prepayment, 2, ',', ' ') }} р.</td>
        <td>ФИО: {{ $order->client->last_name ?? $order->vehicle?->client?->last_name }} {{ $order->client->middle_name ?? $order->vehicle?->client?->middle_name }} {{ $order->client->first_name ?? $order->vehicle?->client?->first_name}}</td>
    </tr>
    <tr>
        <td>Остаток: {{ number_format($order->amount , 2, ',', ' ') }} р.</td>
        <td>Тел: {{ $order->vehicle->client->phone ?? '-' }}</td>
    </tr>
</table>

<br><br><br>
<div class="right">Заказчик _____________________</div>

<div class="no-print" style="margin-top:20px; text-align:left;">
    <button onclick="window.print()">🖨️ Печать</button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function(){
        window.print()
    })

    window.onafterprint = function(){
        window.close()
    }

    window.onfocus=function(){ window.close();}
</script>

</body>
</html>
