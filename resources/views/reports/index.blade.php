@extends('layouts.app')

@section('title', 'Отчёты по продажам')

@section('content')
<h1>Отчёты по продажам по клиентам</h1>

<form method="GET" action="{{ route('reports.index') }}" style="margin-bottom:20px;">
    <label>Начальная дата:</label>
    <input type="date" name="start_date" value="{{ optional($startDate)->format('Y-m-d') }}">

    <label>Конечная дата:</label>
    <input type="date" name="end_date" value="{{ optional($endDate)->format('Y-m-d') }}">

    <label>Период:</label>
    <select name="period" id="period-select">
        <option value="range" {{ $period=='range' ? 'selected' : '' }}>За период</option>
        <option value="day" {{ $period=='day' ? 'selected' : '' }}>День</option>
        <option value="month" {{ $period=='month' ? 'selected' : '' }}>Месяц</option>
        <option value="year" {{ $period=='year' ? 'selected' : '' }}>Год</option>
    </select>

    <span id="date-controls" style="{{ $period=='range' ? 'display:none;' : '' }}">
        <!-- For day filter -->
        <label id="date-label" style="{{ $period=='month' || $period=='year' ? 'display:none;' : '' }}">Выбрать дату:</label>
        <input type="date" name="date" id="date-input" value="{{ $selectedDate->format('Y-m-d') }}" style="{{ $period=='month' || $period=='year' ? 'display:none;' : '' }}">

        <!-- For month filter -->
        <span id="month-year-selects" style="{{ $period=='month' ? '' : 'display:none;' }}">
            <label>Месяц:</label>
            <select name="month">
                @foreach([
                    1=>'Январь',2=>'Февраль',3=>'Март',4=>'Апрель',5=>'Май',6=>'Июнь',
                    7=>'Июль',8=>'Август',9=>'Сентябрь',10=>'Октябрь',11=>'Ноябрь',12=>'Декабрь'
                ] as $num => $name)
                    <option value="{{ $num }}" {{ $selectedMonth==$num ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>

            <label>Год:</label>
            <input type="number" name="year" min="2000" max="2100" value="{{ $selectedYear }}">
        </span>

        <!-- For year filter -->
        <span id="year-select" style="{{ $period=='year' ? '' : 'display:none;' }}">
            <label>Год:</label>
            <input type="number" name="year" min="2000" max="2100" value="{{ $selectedYear }}">
        </span>
    </span>

    <button type="submit">Сформировать</button>
    <a href="{{ route('reports.index') }}" style="margin-left:10px;">Сбросить</a>
</form>

@php
    $grandTotal = array_sum(array_column($results, 'total_sum'));
    $grandProfit = array_sum(array_column($results, 'profit'));
    $hasResults = !empty($results);
@endphp

<table border="1" cellpadding="5" cellspacing="0" style="margin-top:20px; width:100%; border-collapse:collapse;">
    <thead>
        <tr>
            <th>Клиент</th>
            <th>Сумма продаж</th>
            <th>Прибыль</th>
        </tr>
    </thead>
    <tbody>
        @forelse($results as $item)
        <tr>
            <td>{{ $item['client_name'] }}</td>
            <td>{{ number_format($item['total_sum'], 2) }} ₽</td>
            <td>{{ number_format($item['profit'], 2) }} ₽</td>
        </tr>
        @empty
        <tr>
            <td colspan="3">Нет данных за выбранный период</td>
        </tr>
        @endforelse
    </tbody>
    @if($hasResults)
    <tfoot>
        <tr style="font-weight:bold; background:#f2f2f2;">
            <td>Итого</td>
            <td>{{ number_format($grandTotal, 2) }} ₽</td>
            <td>{{ number_format($grandProfit, 2) }} ₽</td>
        </tr>
    </tfoot>
    @endif
</table>
@if($hasResults)
    <h2 style="margin-top:30px;">График продаж по клиентам</h2>
    <canvas id="salesChart" height="120"></canvas>
@endif
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const periodSelect = document.getElementById('period-select');
const dateControls = document.getElementById('date-controls');
const dateInput = document.getElementById('date-input');
const dateLabel = document.getElementById('date-label');
const monthYearSelects = document.getElementById('month-year-selects');
const yearSelect = document.getElementById('year-select');

periodSelect.addEventListener('change', () => {
    if(periodSelect.value==='range') {
        dateControls.style.display = 'none';
    } else {
        dateControls.style.display = 'inline';
        dateInput.style.display = (periodSelect.value==='day') ? 'inline-block' : 'none';
        dateLabel.style.display = (periodSelect.value==='day') ? 'inline' : 'none';
        monthYearSelects.style.display = (periodSelect.value==='month') ? 'inline' : 'none';
        yearSelect.style.display = (periodSelect.value==='year') ? 'inline' : 'none';
    }
});

@if($hasResults)
// Prepare data from Laravel
const clients = @json(array_column($results, 'client_name'));
const sales = @json(array_column($results, 'total_sum'));
const profit = @json(array_column($results, 'profit'));

const ctx = document.getElementById('salesChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: clients,
        datasets: [
            {
                label: 'Сумма продаж',
                data: sales,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            },
            {
                label: 'Прибыль',
                data: profit,
                backgroundColor: 'rgba(75, 192, 75, 0.6)',
                borderColor: 'rgba(75, 192, 75, 1)',
                borderWidth: 1
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top'
            },
            title: {
                display: true,
                text: 'Продажи и прибыль по клиентам'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return value.toLocaleString() + ' ₽';
                    }
                }
            }
        }
    }
});
@endif
</script>


<script>
const periodSelect = document.getElementById('period-select');
const dateControls = document.getElementById('date-controls');
const dateInput = document.getElementById('date-input');
const dateLabel = document.getElementById('date-label');
const monthYearSelects = document.getElementById('month-year-selects');
const yearSelect = document.getElementById('year-select');

periodSelect.addEventListener('change', () => {
    if(periodSelect.value==='range') {
        dateControls.style.display = 'none';
    } else {
        dateControls.style.display = 'inline';
        dateInput.style.display = (periodSelect.value==='day') ? 'inline-block' : 'none';
        dateLabel.style.display = (periodSelect.value==='day') ? 'inline' : 'none';
        monthYearSelects.style.display = (periodSelect.value==='month') ? 'inline' : 'none';
        yearSelect.style.display = (periodSelect.value==='year') ? 'inline' : 'none';
    }
});
</script>

<style>
form label { margin-right: 10px; font-weight: bold; }
form select, form input { padding: 5px; margin-right: 10px; }
form button { padding: 6px 12px; cursor: pointer; background:#2d89ef; color:white; border:none; border-radius:4px; }
form button:hover { background:#1b5fbd; }
table th, table td { padding:8px; text-align:left; border:1px solid #ccc; }
table th { background:#f2f2f2; }
</style>
@endsection
