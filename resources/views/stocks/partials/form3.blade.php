<div>
  <input type="text" id="searchInput" placeholder="Введите артикул...">
  <button id="searchButton">Найти бренды</button>
</div>

<h3>Бренды</h3>
<ul id="brandsList"></ul>

<hr>

<h3>Результаты</h3>
<table id="resultsTable" border="1" cellspacing="0" cellpadding="5">
  <thead>
    <tr>
      <th>Поставщик</th>
      <th>Бренд</th>
      <th>Артикул</th>
      <th>Название</th>
      <th>Количество</th>
      <th>Цена</th>
      <th>Склад</th>
    </tr>
  </thead>
  <tbody></tbody>
</table>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const brandsList = document.getElementById("brandsList");
  const tbody = document.querySelector("#resultsTable tbody");

  let brandSet = new Set();
  let articleGlobal = "";
  let brandGlobal = "";

  const suppliers = ["ABS","OtherSupplier","FakeSupplier","Mosvorechie"];
  const itemsData = {}; // поставщик -> бренд -> артикул -> массив товаров

  // --- Шаг 1: Поиск брендов ---
  document.getElementById("searchButton").addEventListener("click", (e) => {
    e.preventDefault();
    const article = document.getElementById("searchInput").value.trim();
    if (!article) return;

    articleGlobal = article;
    brandGlobal = "";
    brandSet.clear();
    brandsList.innerHTML = "";
    tbody.innerHTML = "";
    Object.keys(itemsData).forEach(s => delete itemsData[s]); // сброс данных

    const evtSource = new EventSource(`/api/brands?article=${encodeURIComponent(article)}`);
    suppliers.forEach(s => evtSource.addEventListener(s, e => collectBrands(JSON.parse(e.data))));
    evtSource.addEventListener("end", () => {
      evtSource.close();
      renderBrands();
    });
  });

  function collectBrands(brands) {
    brands.forEach(b => {
      if (b) brandSet.add(b);
    });
  }

  function renderBrands() {
    brandsList.innerHTML = "";
    Array.from(brandSet).sort().forEach(brand => {
      const li = document.createElement("li");
      li.textContent = brand;
      li.style.cursor = "pointer";
      li.addEventListener("click", () => {
        brandGlobal = brand;
        loadItems(articleGlobal, brand);
      });
      brandsList.appendChild(li);
    });
  }

  // --- Шаг 2: Загрузка товаров ---
  function loadItems(article, brand) {
    tbody.innerHTML = "";
    suppliers.forEach(s => {
      if (!itemsData[s]) itemsData[s] = {};
      if (!itemsData[s][brand]) itemsData[s][brand] = {};
    });

    const evtSource = new EventSource(`/api/items?article=${encodeURIComponent(article)}&brand=${encodeURIComponent(brand)}`);
    suppliers.forEach(s => evtSource.addEventListener(s, e => collectItems(s, JSON.parse(e.data))));
    evtSource.addEventListener("end", () => evtSource.close());
  }

  function collectItems(supplier, items) {
    if (!items || items.length === 0) return;
    items.forEach(item => {
      const brand = item.part_make || "-";
      const partNumber = item.part_number || "-";
      if (!itemsData[supplier][brand]) itemsData[supplier][brand] = {};
      if (!itemsData[supplier][brand][partNumber]) itemsData[supplier][brand][partNumber] = [];
      itemsData[supplier][brand][partNumber].push(item);
    });

    renderResults();
  }

  // --- Шаг 3: Отображение таблицы ---
  function renderResults() {
    tbody.innerHTML = "";

    Object.keys(itemsData).forEach(supplier => {
      const brandGroups = itemsData[supplier];

      // Сначала выбранный бренд
      const sortedBrands = Object.keys(brandGroups).sort((a, b) => {
        if (a === brandGlobal) return -1;
        if (b === brandGlobal) return 1;
        return a.localeCompare(b);
      });

      sortedBrands.forEach(brand => {
        const parts = brandGroups[brand];
        Object.keys(parts).forEach(partNumber => {
          let groupItems = parts[partNumber];

          // Сортировка: OEM в начале, затем по возрастанию цены
          groupItems.sort((a, b) => {
            const aOEM = (a.part_number === articleGlobal && a.part_make === brandGlobal) ? 0 : 1;
            const bOEM = (b.part_number === articleGlobal && b.part_make === brandGlobal) ? 0 : 1;
            if (aOEM !== bOEM) return aOEM - bOEM;
            return (parseFloat(a.price) || 0) - (parseFloat(b.price) || 0);
          });

          const toggleId = `supplier-${supplier}-${brand}-${partNumber}-${Date.now()}`;
          const hiddenCount = groupItems.length - 3;

          // Заголовок группы
          const headerRow = document.createElement("tr");
          headerRow.style.backgroundColor = "#f0f0f0";
          headerRow.innerHTML = `
            <td colspan="7">
              <strong>${supplier}</strong> - ${brand} ${partNumber}
              ${hiddenCount > 0 ? `<button data-toggle="${toggleId}" style="margin-left:10px;">Показать еще ${hiddenCount}</button>` : ""}
            </td>
          `;
          tbody.appendChild(headerRow);

          // Строки товаров
          groupItems.forEach((item, idx) => {
            const row = document.createElement("tr");
            row.dataset.group = toggleId;
            if (idx >= 3) row.style.display = "none";

            const isOEM = (item.part_number === articleGlobal && item.part_make === brandGlobal);

            row.innerHTML = `
              <td>${supplier}</td>
              <td>${item.part_make ?? "-"}</td>
              <td>${item.part_number ?? "-"}</td>
              <td>${item.name ?? "-"}</td>
              <td>${item.quantity ?? 0}</td>
              <td>${item.price ?? "-"}</td>
              <td>${item.warehouse ?? "-"}</td>
            `;

            if (isOEM) {
              row.style.backgroundColor = "#fff8c6";
              row.style.fontWeight = "bold";
              const tdNumber = row.children[2];
              tdNumber.innerHTML += ' <span style="color:red;font-weight:bold;">OEM</span>';
            }

            tbody.appendChild(row);
          });

          // Кнопка "показать больше"
          if (hiddenCount > 0) {
            const toggleBtn = headerRow.querySelector("button[data-toggle]");
            toggleBtn.addEventListener("click", (e) => {
              e.preventDefault();
              const rows = tbody.querySelectorAll(`tr[data-group="${toggleId}"]`);
              const isCollapsed = rows[3].style.display === "none";
              rows.forEach((r, idx) => { if (idx >= 3) r.style.display = isCollapsed ? "" : "none"; });
              toggleBtn.textContent = isCollapsed ? "Свернуть" : `Показать еще ${hiddenCount}`;
            });
          }
        });
      });
    });
  }
});
</script>
