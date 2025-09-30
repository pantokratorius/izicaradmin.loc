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
      <th>Артикул</th>
      <th>Название</th>
      <th>Кол-во</th>
      <th>Цена</th>
      <th>Склад</th>
    </tr>
  </thead>
  <tbody></tbody>
</table>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const brandsList = document.getElementById("brandsList");
  const tbody      = document.querySelector("#resultsTable tbody");

  let brandSet = new Set();
  let articleGlobalNumber = "";
  let articleGlobalBrand  = "";

  // --- шаг 1: поиск брендов
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
      li.style.cursor = "pointer";
      li.addEventListener("click", () => {
        articleGlobalBrand = brand.toLowerCase(); // сохраняем в нижнем регистре
        loadItems(articleGlobalNumber, brand);
      });
      brandsList.appendChild(li);
    });
  }

  // --- шаг 2: загрузка позиций
  const itemsData = {}; // supplier -> part_key -> items[]

  function loadItems(article, brand) {
    tbody.innerHTML = "";
    for (let k in itemsData) delete itemsData[k]; // очистка

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
      const key = `${(item.part_make || "").toLowerCase()}_${item.part_number}`;
      if (!itemsData[supplier][key]) itemsData[supplier][key] = [];
      itemsData[supplier][key].push(item);
    });

    renderResults();
  }

  // --- рендер результатов
  function renderResults() {
  tbody.innerHTML = "";

  let allGroups = [];

  // собираем все группы всех поставщиков в один массив
  Object.keys(itemsData).forEach(supplier => {
    const supplierGroups = itemsData[supplier];
    Object.keys(supplierGroups).forEach(partKey => {
      allGroups.push({
        supplier,
        partKey,
        items: supplierGroups[partKey]
      });
    });
  });

  // сортируем группы глобально
  allGroups.sort((a, b) => {
    const aBrand = (a.items[0].part_make || "").toLowerCase();
    const bBrand = (b.items[0].part_make || "").toLowerCase();

    const aSelected = (aBrand === articleGlobalBrand) ? 0 : 1;
    const bSelected = (bBrand === articleGlobalBrand) ? 0 : 1;
    if (aSelected !== bSelected) return aSelected - bSelected;

    const aOEM = (a.items[0].part_number === articleGlobalNumber && aBrand === articleGlobalBrand) ? 0 : 1;
    const bOEM = (b.items[0].part_number === articleGlobalNumber && bBrand === articleGlobalBrand) ? 0 : 1;
    if (aOEM !== bOEM) return aOEM - bOEM;

    return 0;
  });

  // выводим группы
  allGroups.forEach(group => {
    let groupItems = group.items;

    groupItems.sort((a, b) => {
      const aMake = (a.part_make || "").toLowerCase();
      const bMake = (b.part_make || "").toLowerCase();

      const aSelected = (aMake === articleGlobalBrand) ? 0 : 1;
      const bSelected = (bMake === articleGlobalBrand) ? 0 : 1;
      if (aSelected !== bSelected) return aSelected - bSelected;

      const aOEM = (a.part_number === articleGlobalNumber && aMake === articleGlobalBrand) ? 0 : 1;
      const bOEM = (b.part_number === articleGlobalNumber && bMake === articleGlobalBrand) ? 0 : 1;
      if (aOEM !== bOEM) return aOEM - bOEM;

      return (parseFloat(a.price) || 0) - (parseFloat(b.price) || 0);
    });

    const hiddenCount = groupItems.length - 3;
    const toggleId = `supplier-${group.supplier}-${group.partKey}-${Date.now()}`;

    const headerRow = document.createElement("tr");
    headerRow.style.backgroundColor = "#f0f0f0";
    headerRow.innerHTML = `
      <td colspan="7">
        <strong>${group.supplier}</strong> – ${groupItems[0].part_make} ${groupItems[0].part_number}
        ${hiddenCount > 0 ? `<button data-toggle="${toggleId}" style="margin-left:10px;">Показать ещё ${hiddenCount}</button>` : ""}
      </td>
    `;
    tbody.appendChild(headerRow);

    groupItems.forEach((item, idx) => {
      const row = document.createElement("tr");
      row.dataset.group = toggleId;
      if (idx >= 3) row.style.display = "none";

      const isOEM = (
        item.part_number === articleGlobalNumber &&
        (item.part_make || "").toLowerCase() === articleGlobalBrand
      );
      const isSelectedBrand = (item.part_make || "").toLowerCase() === articleGlobalBrand;

      row.innerHTML = `
        <td>${group.supplier}</td>
        <td style="${isSelectedBrand ? 'background:#e6f7ff;font-weight:bold;' : ''}">
          ${item.part_make ?? "-"}
        </td>
        <td>${item.part_number ?? "-"}</td>
        <td>${item.name ?? "-"}</td>
        <td>${item.quantity ?? 0}</td>
        <td>${item.price ?? "-"}</td>
        <td>${item.warehouse ?? "-"}</td>
      `;

      if (isOEM) {
        row.style.backgroundColor = "#fff8c6";
        row.style.fontWeight = "bold";
        row.children[2].innerHTML += ' <span style="color:red;font-weight:bold;">OEM</span>';
      }

      tbody.appendChild(row);
    });

    if (hiddenCount > 0) {
      const toggleBtn = headerRow.querySelector("button[data-toggle]");
      toggleBtn.addEventListener("click", (e) => {
        e.preventDefault();
        const rows = tbody.querySelectorAll(`tr[data-group="${toggleId}"]`);
        const isCollapsed = rows[3].style.display === "none";
        rows.forEach((r, idx) => {
          if (idx >= 3) r.style.display = isCollapsed ? "" : "none";
        });
        toggleBtn.textContent = isCollapsed
          ? "Свернуть"
          : `Показать ещё ${hiddenCount}`;
      });
    }
  });
}
});
</script>

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