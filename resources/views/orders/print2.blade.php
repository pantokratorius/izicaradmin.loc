<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Заказ №{{ $order->id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 13px; margin: 30px; line-height: 1.5; }
        h3 { text-align: center; margin: 15px 0; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .header-left { font-size: 13px; line-height: 1.4; }
        .header-right img { max-height: 80px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #000; padding: 5px;  text-align: center }
        th { background: #f0f0f0; }
        .right { text-align: right; }
        .no-border td { border: none; }
        .conditions { font-size: 10px; margin-top: 20px; line-height: 12px}
        .signatures { margin-top: 20px; display: flex; justify-content: space-between; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>

<div class="header">
    <div class="header-left">
        <strong>ИСПОЛНИТЕЛЬ:</strong><br>
        ИП БОНДАРЬ ВИТАЛИЙ АЛЕКСАНДРОВИЧ<br>
        ИНН: 231516956734 / ОГРН: 314231502700094<br>
        Тел.: +7(960)480-62-02
    </div>
    <div class="header-right">
        <img src="{{ asset('logo.png') }}" alt="Логотип">
    </div>
</div>

<h3>ДИСТАНЦИОННЫЙ ЗАКАЗ ТОВАРА №{{ $order->order_number }} от {{ $order->created_at->format('d.m.Y') }}</h3>

<p>
    Марка автомобиля: {{ $order->vehicle->brand->name ?? '-' }},  
    VIN: {{ $order->vehicle->vin  ?? '-'}}
</p>

<table>
    <thead>
        <tr>
            <th>№</th>
            <th>Производитель</th>
            <th>Наименование</th>
            <th>Цена</th>
            <th>Кол-во</th>
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
            <td>{{ $item->part_make }}</td>
            <td>{{ $item->part_name }}</td>
            <td>{{ number_format($item->amount, 2, ',', ' ') }} р</td>
            <td>{{ $item->quantity }}</td>
            <td>{{ number_format($item->summ, 2, ',', ' ') }} р</td>
        </tr>
        @endforeach
        <tr>
            <td colspan="5" class="right"><strong>Итого:</strong></td>
            <td><strong>{{ number_format($total, 2, ',', ' ') }} р</strong></td>
        </tr>
    </tbody>
</table>

<p>
    Ожидаемая дата поставки: {{ $order->expected_date?->format('d.m.Y') ?? '—' }}<br>
    Предоплата: {{ number_format($order->prepayment, 2, ',', ' ') }} р.<br>
    Остаток: {{ number_format($total - $order->prepayment, 2, ',', ' ') }} р.
</p>

<div class="conditions">
    <p style="text-align: center; text-decoration: underline"><strong>Условия поставки и выдачи товара:</strong></p>
    <ul style="padding: 0">
        <li>Заказ товара заключается на основании ознакомления заказчика с предложенным исполнителем описанием товара посредством каталогов, проспектов, буклетов, фотоснимков, средств связи или иными исключающими возможность непосредственного ознакомления потребителя с товаром.</li>
        <li>Исполнителем предоставлена Заказчику полная информация об основных потребительских свойствах товара, о месте изготовления товара, о полном фирменном наименовании изготовителя, о цене и об условиях приобретения товара, о его доставке и гарантийном сроке в полном объеме.</li>
        <li>Заказчик вправе отказаться от товара в любое время до его передачи, а после передачи товара — в течение 7 (семи) дней.</li>
        <li>Возврат товара надлежащего качества возможен в случае, если сохранены его товарный вид, потребительские свойства, а также документ, подтверждающий факт и условия покупки указанного товара. Отсутствие у потребителя документа, подтверждающего факт и условия покупки товара, не лишает его возможности ссылаться на другие доказательства приобретения товара у данного продавца.</li>
        <li>Исполнитель обязан в случае изменения цены и срока поставки информировать не позднее 1 (одного) рабочего дня Заказчика об изменении условий с целью получения согласия на новые условия выполнения Заказа в целом, либо в части.</li>
        <li>Отказ Поставщика по одному или нескольким наименованиям или количеству товара данного заказа не будет являться основанием для отказа Заказчика от остальных позиций заказа.</li>
        <li>Общим сроком исполнения заказа является наибольший срок поставки одного из наименований Товара, составляющих Заказ.</li>
        <li>Исполнитель имеет право не принимать претензий по несоответствию товара автомобилю и не принимать возврат товара в случае переоборудования автомобиля вне специализированных станций технического обслуживания или установки нестандартного оборудования или агрегатов.</li>
        <li>Выдача товара производится при наличии листа Заказа.</li>
        <li>Заказчик обязуется забрать товар в течение 7 (семи) календарных дней с момента получения уведомления от исполнителя о поступлении товара на склад.</li>
        <li>В случае невостребования Товара Заказчиком в течение 7 (семи) календарных дней с момента уведомления Заказчика о поступлении Товара на склад Исполнителя, Исполнитель имеет право аннулировать заказ в одностороннем порядке, предоплата внесенная за товар будет возвращена Заказчику за вычетом расходов, понесенных исполнителем.</li>
        <li>Лист Заказа составлен в 2-х экземплярах, один экземпляр остается у Исполнителя, другой у Заказчика.</li>
        <li>Стороны несут ответственность за исполнение обязательств в соответствии с законодательством РФ.</li>
    </ul>
</div>

<div class="signatures">
    <div>
        <strong>Исполнитель</strong><br>
        ИП Бондарь Виталий Александрович<br>
        Адрес: 353915, г. Новороссийск, ул. Дзержинского 165<br>
        Тел.: +7-960-480-62-02<br><br>
        Подпись _______________
    </div>
    <div>
        <strong>Заказчик</strong><br>
        ФИО: {{ $order->client->last_name ?? $order->vehicle?->client?->last_name }} {{ $order->client->middle_name ?? $order->vehicle?->client?->middle_name }} {{ $order->client->first_name ?? $order->vehicle?->client?->first_name}}<br>
        Тел: {{ $order->vehicle->client->phone ?? '-' }}<br>
        Контактные данные верны, с условиями<br> поставки и выдачи товара согласен.<br>
        Подпись _______________
    </div>
</div>

<p style="margin-top:10px; font-size:10px;">
Претензий к внешнему виду полученного товара не имею, производитель и наименования товара проверены и соответствуют указанным в заказе. Информация о порядке и сроках возврата товара надлежащего качества была предоставлена. Заказ выполнен полностью и в срок.
</p>
<p style="margin-bottom: 0; margin-top: 5px">/______________/______________/ ____.____. {{date('Y')}} г</p>
<p style="margin-top: 0">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Подпись  
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  ФИО</p>

<div class="no-print" style="text-align:left; margin-top:20px;">
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
