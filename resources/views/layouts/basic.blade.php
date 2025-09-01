<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>IZICAR - Автомобиль</title>
  <style>
    body {
      margin: 0;
      font-family: "Segoe UI", Arial, sans-serif;
      background: #f5f7fb;
      color: #333;
    }
    /* Sidebar */
    .sidebar {
      position: fixed;
      top: 0; left: 0; bottom: 0;
      width: 220px;
      background: #1c2b4a;
      color: #fff;
      display: flex;
      flex-direction: column;
      padding: 20px 15px;
    }
    .sidebar h2 {
      font-size: 18px;
      margin-bottom: 25px;
    }
    .sidebar a {
      display: block;
      color: #cdd3e0;
      text-decoration: none;
      margin: 8px 0;
      font-size: 14px;
    }
    .sidebar a:hover {
      color: #fff;
    }
    /* Main layout */
    .main {
      margin-left: 240px;
      padding: 20px;
    }
    .page-header {
      font-size: 18px;
      font-weight: bold;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    /* Card style */
    .card {
      background: #fff;
      border-radius: 8px;
      padding: 20px;
      margin-bottom: 20px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    /* Form grid */
    .form-section {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 15px;
    }
    .form-section label {
      font-size: 13px;
      margin-bottom: 5px;
      display: block;
    }
    .form-section input,
    .form-section select {
      width: 100%;
      padding: 8px;
      border: 1px solid #ccc;
      border-radius: 6px;
    }
    /* Tabs */
    .tabs {
      display: flex;
      gap: 15px;
      margin-bottom: 15px;
      border-bottom: 1px solid #ddd;
    }
    .tabs button {
      background: none;
      border: none;
      font-size: 14px;
      padding: 8px 12px;
      cursor: pointer;
      color: #555;
      border-bottom: 2px solid transparent;
    }
    .tabs button.active {
      color: #007bff;
      border-bottom: 2px solid #007bff;
      font-weight: 500;
    }
    /* Tab content */
    .tab-content {
      display: none;
    }
    .tab-content.active {
      display: block;
    }
    /* Table */
    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 14px;
    }
    table th, table td {
      padding: 10px;
      border-bottom: 1px solid #eee;
      text-align: left;
    }
    table th {
      background: #f9fafc;
      font-weight: bold;
    }
    .btn {
      display: inline-block;
      padding: 7px 14px;
      background: #007bff;
      color: #fff;
      border-radius: 6px;
      font-size: 14px;
      text-decoration: none;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar">
    <h2>IZICAR</h2>
    <a href="{{route('clients.index')}}">Клиенты</a>
    <a href="#">Автомобили</a>
    <a href="#">Заказы</a>
    <a href="#">Проценка</a>
    <a href="#">Склад</a>
    <a href="#">Черновики</a>
    <a href="#">Деньги</a>
    <a href="#">Отчеты</a>
    <a href="#">Звонки</a>
    <a href="#">Чаты</a>
    <a href="#">Поддержка</a>
    <a href="#">Управление</a>
  </div>

  <!-- Main content -->
  <div class="main">
    <div class="page-header">🚗 Land Rover Freelander 2 поколение [2-й рестайлинг]</div>

    <div class="card form-section">
      <div>
        <label>VIN</label>
        <input type="text" value="SALFA2BB8FH426247">
      </div>
      <div>
        <label>Страхователь КАСКО</label>
        <input type="text">
      </div>
      <div>
        <label>Дата окончания КАСКО</label>
        <input type="date">
      </div>
      <div>
        <label>Страхователь ОСАГО</label>
        <input type="text">
      </div>
      <div>
        <label>Дата окончания ОСАГО</label>
        <input type="date">
      </div>
      <div>
        <label>Описание задачи</label>
        <input type="text">
      </div>
      <div>
        <label>Тип транспортного средства</label>
        <select><option>Легковой автомобиль</option></select>
      </div>
      <div>
        <label>Бренд</label>
        <input type="text" value="Land Rover">
      </div>
      <div>
        <label>Модель</label>
        <input type="text" value="Freelander">
      </div>
      <div>
        <label>Поколение</label>
        <input type="text" value="2 поколение [2-й рестайлинг]">
      </div>
    </div>

    <div class="card">
      <div class="tabs">
        <button class="active" data-tab="deals">Сделки</button>
        <button data-tab="drivers">Водители</button>
        <button data-tab="works">Работы</button>
        <button data-tab="parts">Запчасти</button>
        <button data-tab="rec-works">Рекомендации - работы</button>
        <button data-tab="rec-parts">Рекомендации - запчасти</button>
        <button data-tab="text-rec">Текстовые рекомен...</button>
      </div>

      <!-- Deals -->
      <div class="tab-content active" id="deals">
        <a href="#" class="btn">+ Новая сделка</a>
        <table>
          <thead>
            <tr>
              <th>№</th>
              <th>Номер сделки</th>
              <th>Статус</th>
              <th>Подразделение</th>
              <th>Воронка</th>
              <th>Дата первого з...</th>
              <th>Дата создания</th>
            </tr>
          </thead>
          <tbody>
            <tr><td>1</td><td>9</td><td>Неразобранное</td><td>Розничный...</td><td>Розничный...</td><td>-</td><td>17 июня 2025</td></tr>
          </tbody>
        </table>
      </div>

      <!-- Drivers -->
      <div class="tab-content" id="drivers">
        <a href="#" class="btn">+ Новый водитель</a>
        <table>
          <thead>
            <tr><th>№</th><th>Имя</th><th>Телефон</th><th>Вод. удостоверение</th></tr>
          </thead>
          <tbody>
            <tr><td>1</td><td>Иван Петров</td><td>+7 999 123 45 67</td><td>77 11 223344</td></tr>
            <tr><td>2</td><td>Алексей Смирнов</td><td>+7 912 555 66 77</td><td>77 22 334455</td></tr>
          </tbody>
        </table>
      </div>

      <!-- Works -->
      <div class="tab-content" id="works">
        <a href="#" class="btn">+ Новая работа</a>
        <table>
          <thead>
            <tr><th>№</th><th>Работа</th><th>Стоимость</th><th>Статус</th></tr>
          </thead>
          <tbody>
            <tr><td>1</td><td>Замена масла</td><td>2500 ₽</td><td>Выполнено</td></tr>
            <tr><td>2</td><td>Диагностика двигателя</td><td>1500 ₽</td><td>В процессе</td></tr>
          </tbody>
        </table>
      </div>

      <!-- Parts -->
      <div class="tab-content" id="parts">
        <a href="#" class="btn">+ Добавить запчасть</a>
        <table>
          <thead>
            <tr><th>№</th><th>Название</th><th>Код</th><th>Цена</th><th>Наличие</th></tr>
          </thead>
          <tbody>
            <tr><td>1</td><td>Фильтр масляный</td><td>LR-123</td><td>800 ₽</td><td>Есть</td></tr>
            <tr><td>2</td><td>Свеча зажигания</td><td>LR-456</td><td>600 ₽</td><td>Нет</td></tr>
          </tbody>
        </table>
      </div>

      <!-- Recommendations Works -->
      <div class="tab-content" id="rec-works">
        <p>Рекомендации по работам: провести ТО каждые 10 000 км.</p>
      </div>

      <!-- Recommendations Parts -->
      <div class="tab-content" id="rec-parts">
        <p>Рекомендации по запчастям: заменить тормозные колодки.</p>
      </div>

      <!-- Text Recommendations -->
      <div class="tab-content" id="text-rec">
        <p>Текстовые рекомендации будут отображаться здесь.</p>
      </div>
    </div>
  </div>

  <script>
    const tabs = document.querySelectorAll('.tabs button');
    const contents = document.querySelectorAll('.tab-content');

    tabs.forEach(tab => {
      tab.addEventListener('click', () => {
        tabs.forEach(btn => btn.classList.remove('active'));
        contents.forEach(c => c.classList.remove('active'));

        tab.classList.add('active');
        document.getElementById(tab.dataset.tab).classList.add('active');
      });
    });
  </script>
</body>
</html>
