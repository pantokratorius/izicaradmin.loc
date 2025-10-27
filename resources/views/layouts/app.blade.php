<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>@yield('title', 'IZICAR')</title>
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background: #f5f8fb;
    }
    .sidebar {
      width: 220px;
      background: #14213d;
      color: #fff;
      height: 100vh;
      position: fixed;
      top: 0;
      left: 0;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }
    .sidebar .logo { font-size: 20px; font-weight: bold; text-align: center; padding: 20px; background: #1f2d50; }
    .sidebar ul { list-style: none; padding: 0; margin: 0; }
    .sidebar ul li { padding: 12px 20px; cursor: pointer; }
    .sidebar ul li a { color: #fff; text-decoration: none; padding: 10px 30px; }
    .sidebar ul li:hover { background: #1f2d50; }
    .sidebar .bottom { padding: 20px; font-size: 14px; border-top: 1px solid rgba(255,255,255,0.2); }

    .main { margin-left: 220px; padding: 20px; }
    .page-header { font-size: 20px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; }
    .search-box { padding: 6px 10px; border: 1px solid #ccc; border-radius: 4px; width: 250px; }

    table { width: 100%; border-collapse: collapse; background: #fff; }
    th, td { border: 1px solid #e0e0e0; padding: 10px; text-align: left; font-size: 14px; }
    th { background: #f1f3f5; }
    tr:hover { background: #f9fafc; }

    .btn {
      padding: 6px 12px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 13px;
    }
    .btn-add { background: #28a745; color: #fff; text-decoration: none; }
    .btn-edit { background: #007bff; color: #fff; text-decoration: none; }
    .btn-delete { background: #dc3545; color: #fff; }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar">
    <div>
      <div class="logo">IZICAR</div>
      <ul>
        <li>
            <a href="{{route('clients.index')}}"><img src="{{asset('storage/svg/clients.svg') }}" /> Клиенты</a>
        </li>
        <li>
            <a href="{{route('vehicles.index')}}"><img src="{{asset('storage/svg/autos.svg') }}" /> Автомобили</a>
        </li>
        <li>
            <a href="{{route('orders.index')}}"><img src="{{asset('storage/svg/orders.svg') }}" /> Заказы</a>
        </li>
        <li>
            <a href="{{route('settings.edit')}}"><img src="{{asset('storage/svg/settings.svg') }}" /> Настройки</a>
        </li>
        <li>
          <a href="{{route('reports.index')}}"><img src="{{asset('storage/svg/reports.svg') }}" /> Отчеты</a>  
        </li>
        <li>
          <a href="{{route('stocks.index')}}"><img src="{{asset('storage/svg/stock.svg') }}" /> Склад</a>  
        </li>
        <li>
          <a href="{{ route('brand-groups.index') }}" target="_blank"><img style="width: 18px; height: 16px; color: #fff" src="{{asset('storage/svg/brands.svg') }}" target="_blank" /> Бренды</a>  
        </li>
        <li>Проценка</li>
        <li>Черновики</li>
        <li>Деньги</li>
        <li>Звонки</li>
        <li>Чаты</li>
      </ul>
    </div>
    <div class="bottom">
      Поддержка<br>
      Управление<br><br>
      {{ Auth::user()->name ?? 'Гость' }}
    </div>
  </div>

  <!-- Main Content -->
  <div class="main">
           @if(session('success'))
        <div class="successMessage" style="float: right; background: #d4edda; color: #155724; padding: 10px 15px; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 15px;">
          {{ session('success') }}
        </div>
      @endif

      @if(session('error'))
        <div class="errorMessage" style="float: right; background: #f8d7da; color: #721c24; padding: 10px 15px; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 15px;">
          {{ session('error') }}
        </div>
      @endif
      @if($errors->any())
    <div style="float: right;background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 10px;">
        <ul style="margin:0; padding-left: 20px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
    @yield('content')
  </div>
    <script>
    // === Global Number Formatter (like PHP number_format) ===
    window.numberFormat = function (value) {
  const num = parseFloat(value);
  if (isNaN(num)) return '-';

  // 🔹 Round to nearest 50, but .5 goes DOWN (like 125 → 100)
  const divided = num / 50;
  const rounded = (divided % 1 === 0.5) 
    ? Math.floor(divided) * 50 
    : Math.round(divided) * 50;

  return rounded
    .toFixed(2)                       // two decimals
    .replace('.', ',')                // comma as decimal separator
    .replace(/\B(?=(\d{3})+(?!\d))/g, ' '); // spaces for thousands
};
  </script>
</body>
</html>


