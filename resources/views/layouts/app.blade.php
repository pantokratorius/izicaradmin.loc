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
    .sidebar ul li a { color: #fff; text-decoration: none }
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
            <a href="{{route('clients.index')}}">Клиенты</a>
        </li>
        <li>Автомобили</li>
        <li>Заказы</li>
        <li>Проценка</li>
        <li>Склад</li>
        <li>Черновики</li>
        <li>Деньги</li>
        <li>Отчеты</li>
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
</body>
</html>


