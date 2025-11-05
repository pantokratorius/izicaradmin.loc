<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Клиенты - Контакты</title>
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
    .sidebar ul li:hover { background: #1f2d50; }
    .sidebar .bottom { padding: 20px; font-size: 14px; border-top: 1px solid rgba(255,255,255,0.2); }

    .main { margin-left: 220px; padding: 20px; }
    .page-header { font-size: 20px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; }
    .search-box { padding: 6px 10px; border: 1px solid #ccc; border-radius: 4px; width: 280px; }

    table { width: 100%; border-collapse: collapse; background: #fff; }
    th, td { border: 1px solid #e0e0e0; padding: 10px; text-align: left; font-size: 14px; }
    th { background: #f1f3f5; }
    tr:hover { background: #f9fafc; }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar">
    <div>
      <div class="logo">IZICAR</div>
      <ul>
        <li>Клиенты</li>
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

  <!-- Main -->
  <div class="main">

    <table>
      <thead>
        <tr>
          <th>№</th>
          <th>Полное имя</th>
          <th>Сделки</th>
          <th>Количество</th>
          <th>Способ связи</th>
          <th>Основной телефон</th>
          <th>Дата рождения</th>
        </tr>
      </thead>
      <tbody>
        @forelse($clients as $client)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $client->full_name }}</td>
            <td>{{ $client->deals_count ?? '-' }}</td>
            <td>{{ $client->orders_count ?? '-' }}</td>
            <td>{{ $client->contact_method ?? 'Нет' }}</td>
            <td>{{ $client->phone }}</td>
            <td>{{ $client->birth_date ?? '' }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="7" style="text-align: center;">Нет данных</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</body>
</html>
