<div>
  <input type="text" id="searchInput" placeholder="Введите артикул...">
  <button id="searchButton">Найти бренды</button>
</div>

<h3>Бренды</h3>
<ul id="brandsList" class="brand-list"></ul>

<hr>

<h3>Результаты</h3>
<table id="resultsTable" border="1" cellspacing="0" cellpadding="5">
  <thead>
    <tr>
      <th>Поставщик</th>
      <th>Бренд</th>
      <th>Номер детали</th>
      <th>Название</th>
      <th>Кол-во</th>
      <th>Цена</th>
      <th>Склад</th>
    </tr>
  </thead>
  <tbody></tbody>
</table>

<style>
  /* Стили брендов */
  .brand-list {
    list-style: none;
    padding: 0;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
  }
  .brand-list li {
    padding: 6px 12px;
    border: 1px solid #ccc;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    background-color: #f9f9f9;
    font-size: 14px;
  }
  .brand-list li:hover {
    background-color: #e0f7fa;
    border-color: #4dd0e1;
  }
  .brand-list li.selected {
    background-color: #4dd0e1;
    color: #fff;
    font-weight: bold;
    border-color: #00acc1;
  }

  /* OEM подсветка */
  .oem-row {
    background-color: #fff8c6 !important;
    font-weight: bold;
  }
  .oem-row td:nth-child(3)::after {
    content: " (OEM)";
    color: red;
    font-weight: bold;
  }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const brandsList = document.getElementById("brandsList");
  const tbody      = document.querySelector("#resultsTable tbody");

  let brandSet = new Set();
  let articleGlobalNumber = "";
  let articleGlobalBrand = "";

  const itemsData = {}; // supplier -> part_key -> items array

  // === ШАГ 1: Получаем бренды ===
  document.getElementById("searchButton").addEventListener("click", (e) => {
    e.preventDefault();
    const article = document.getElementById("searchInput").value.trim();
    if (!article) return;

    articleGlobalNumber = article;
    articleGlobalBrand = "";
    brandsList.innerHTML = "";
    tbody.innerHTML = "";
    brandSet.clear();

    const evtSource = new EventSource(`/api/brands?article=${encodeURIComponent(article)}`);
    ["ABS","OtherSupplier","FakeSupplier","Mosvorechie"].forEach(s => {
      evtSource.addEventListener(s, e => collectBrands(JSON.parse(e.data)));
    });

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
      li.addEventListener("click", () => {
        brandsList.querySelectorAll('li').forEach(el => el.classList.remove('selected'));
        li.classList.add('selected');
        articleGlobalBrand = brand;
        loadItems(articleGlobalNumber, brand);
      });
      brandsList.appendChild(li);
    });
  }

  // === ШАГ 2: Получаем позиции ===
  function loadItems(article, brand) {
    tbody.innerHTML = "";
    Object.keys(itemsData).forEach(s => delete itemsData[s]); // сброс данных

    const evtSource = new EventSource(`/api/items?article=${encodeURIComponent(article)}&brand=${encodeURIComponent(brand)}`);
    ["ABS","OtherSupplier","FakeSupplier","Mosvorechie"].forEach(s => {
      evtSource.addEventListener(s, e => collectItems(s, JSON.parse(e.data)));
    });
    evtSource.addEventListener("end", () => evtSource.close());
  }

  function collectItems(supplier, items) {
    if (!items || items.length === 0) return;
    if (!itemsData[supplier]) itemsData[supplier] = {};

    items.forEach(item => {
      const key = `${item.part_make}_${item.part_number}`;
      if (!itemsData[supplier][key]) itemsData[supplier][key] = [];
      itemsData[supplier][key].push(item);
    });

    renderResults();
  }

  // === Отрисовка результатов ===
  function renderResults() {
    tbody.innerHTML = "";

    Object.keys(itemsData).forEach(supplier => {
      const supplierGroups = itemsData[supplier];

      Object.keys(supplierGroups).forEach(partKey => {
        let groupItems = supplierGroups[partKey];

        // Сортировка:
        // 1. Выбранный бренд (независимо от номера)
        // 2. OEM (совпадает и бренд, и номер)
        // 3. По цене
        groupItems.sort((a, b) => {
          const aSelected = (a.part_make === articleGlobalBrand) ? 0 : 1;
          const bSelected = (b.part_make === articleGlobalBrand) ? 0 : 1;
          if (aSelected !== bSelected) return aSelected - bSelected;

          const aOEM = (a.part_number === articleGlobalNumber && a.part_make === articleGlobalBrand) ? 0 : 1;
          const bOEM = (b.part_number === articleGlobalNumber && b.part_make === articleGlobalBrand) ? 0 : 1;
          if (aOEM !== bOEM) return aOEM - bOEM;

          return (parseFloat(a.price) || 0) - (parseFloat(b.price) || 0);
        });

        const hiddenCount = groupItems.length - 3;
        const toggleId = `supplier-${supplier}-${partKey}-${Date.now()}`;

        // Заголовок группы
        const headerRow = document.createElement("tr");
        headerRow.style.backgroundColor = "#f0f0f0";
        headerRow.innerHTML = `
          <td colspan="7">
            <strong>${supplier}</strong> - ${groupItems[0].part_make} ${groupItems[0].part_number}
            ${hiddenCount > 0 ? `<button data-toggle="${toggleId}" style="margin-left:10px;">Показать ещё ${hiddenCount}</button>` : ""}
          </td>
        `;
        tbody.appendChild(headerRow);

        // Строки деталей
        groupItems.forEach((item, idx) => {
          const row = document.createElement("tr");
          row.dataset.group = toggleId;
          if (idx >= 3) row.style.display = "none";

          const isOEM = (item.part_number === articleGlobalNumber && item.part_make === articleGlobalBrand);
          if (isOEM) row.classList.add("oem-row");

          row.innerHTML = `
            <td>${supplier}</td>
            <td>${item.part_make ?? "-"}</td>
            <td>${item.part_number ?? "-"}</td>
            <td>${item.name ?? "-"}</td>
            <td>${item.quantity ?? 0}</td>
            <td>${item.price ?? "-"}</td>
            <td>${item.warehouse ?? "-"}</td>
          `;

          tbody.appendChild(row);
        });

        // Кнопка "Показать ещё"
        if (hiddenCount > 0) {
          const toggleBtn = headerRow.querySelector("button[data-toggle]");
          toggleBtn.addEventListener("click", (e) => {
            e.preventDefault();
            const rows = tbody.querySelectorAll(`tr[data-group="${toggleId}"]`);
            const isCollapsed = rows[3].style.display === "none";
            rows.forEach((r, idx) => {
              if (idx >= 3) r.style.display = isCollapsed ? "" : "none";
            });
            toggleBtn.textContent = isCollapsed ? "Скрыть" : `Показать ещё ${hiddenCount}`;
          });
        }
      });
    });
  }
});
</script>
