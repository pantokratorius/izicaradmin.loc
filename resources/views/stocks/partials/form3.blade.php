<div>
  <input type="text" id="searchInput" placeholder="Введите артикул..." style="width: 200px">
  <button id="searchButton">Найти</button>
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

  let brandSet = new Map(); // ключ = lower, значение = оригинал
  let articleGlobalNumber = "";
  let articleGlobalBrand = ""; // хранится в lowercase
  let itemsData = {}; // supplier -> part_key -> items[]

  // Шаг 1: поиск брендов
  document.getElementById("searchButton").addEventListener("click", (e) => {
    e.preventDefault();
    const article = document.getElementById("searchInput").value.trim();
    if (!article) return;

    articleGlobalNumber = article;
    articleGlobalBrand = "";
    brandSet.clear();
    brandsList.innerHTML = "";
    tbody.innerHTML = "";
    itemsData = {};

    const evtSource = new EventSource(`/api/brands?article=${encodeURIComponent(article)}`);
    ["ABS","OtherSupplier","FakeSupplier","Москворечье"].forEach(s => {
      evtSource.addEventListener(s, e => collectBrands(JSON.parse(e.data)));
    });
    evtSource.addEventListener("end", () => {
      evtSource.close();
      renderBrands();
    });
  });

  function collectBrands(brands) {
    brands.forEach(b => {
      if (b) {
        const key = b.toLowerCase();
        if (!brandSet.has(key)) brandSet.set(key, b);
      }
    });
  }

  function renderBrands() {
    brandsList.innerHTML = "";
    Array.from(brandSet.values()).sort((a,b)=>a.localeCompare(b)).forEach(brand=>{
      const li = document.createElement("li");
      li.textContent = brand;
      if (brand.toLowerCase() === articleGlobalBrand) li.classList.add('selected');
      li.addEventListener("click", () => {
        articleGlobalBrand = brand.toLowerCase();
        renderBrands();
        loadItems(articleGlobalNumber, brand);
      });
      brandsList.appendChild(li);
    });
  }

  // Шаг 2: загрузка товаров
  function loadItems(article, brand) {
    tbody.innerHTML = "";
    itemsData = {};
    const evtSource = new EventSource(`/api/items?article=${encodeURIComponent(article)}&brand=${encodeURIComponent(brand)}`);
    ["ABS","OtherSupplier","FakeSupplier","Москворечье"].forEach(s => {
      evtSource.addEventListener(s, e => collectItems(s, JSON.parse(e.data)));
    });
    evtSource.addEventListener("end", () => evtSource.close());
  }

  function collectItems(supplier, items) {
    if (!items || !items.length) return;
    if (!itemsData[supplier]) itemsData[supplier] = {};
    items.forEach(item => {
      const key = `${item.part_make}_${item.part_number}`;
      if (!itemsData[supplier][key]) itemsData[supplier][key] = [];
      itemsData[supplier][key].push(item);
    });
    renderResults();
  }

  function renderResults() {
    tbody.innerHTML = "";
    let allItems = [];

    // собираем все позиции всех поставщиков в один массив
    Object.keys(itemsData).forEach(supplier=>{
      const supplierGroups = itemsData[supplier];
      Object.keys(supplierGroups).forEach(partKey=>{
        supplierGroups[partKey].forEach(item=>{
          allItems.push({...item, supplier});
        });
      });
    });

    // глобальная сортировка: выбранный бренд -> OEM -> остальное, затем по цене
    allItems.sort((a,b)=>{
      const aMake = (a.part_make||"").toLowerCase();
      const bMake = (b.part_make||"").toLowerCase();

      const aSelected = aMake===articleGlobalBrand?0:1;
      const bSelected = bMake===articleGlobalBrand?0:1;
      if(aSelected!==bSelected) return aSelected-bSelected;

      const aOEM = (a.part_number===articleGlobalNumber && aMake===articleGlobalBrand)?0:1;
      const bOEM = (b.part_number===articleGlobalNumber && bMake===articleGlobalBrand)?0:1;
      if(aOEM!==bOEM) return aOEM-bOEM;

      return (parseFloat(a.price)||0)-(parseFloat(b.price)||0);
    });

    // группируем по поставщикам и part_make + part_number для toggle
    const grouped = {};
    allItems.forEach(item=>{
      const key = `${item.supplier}_${item.part_make}_${item.part_number}`;
      if(!grouped[key]) grouped[key] = {supplier: item.supplier, items: []};
      grouped[key].items.push(item);
    });

    Object.values(grouped).forEach(group=>{
      const groupItems = group.items;
      const hiddenCount = groupItems.length-3;
      const toggleId = `supplier-${group.supplier}-${groupItems[0].part_make}-${groupItems[0].part_number}-${Date.now()}`;

      const headerRow = document.createElement("tr");
      headerRow.style.backgroundColor="#f0f0f0";
      headerRow.innerHTML=`
        <td colspan="7">
          <strong>${group.supplier}</strong> – ${groupItems[0].part_make} ${groupItems[0].part_number}
          ${hiddenCount>0?`<button data-toggle="${toggleId}" style="margin-left:10px;">Показать ещё ${hiddenCount}</button>`:""}
        </td>
      `;
      tbody.appendChild(headerRow);

      groupItems.forEach((item,idx)=>{
        const row = document.createElement("tr");
        row.dataset.group=toggleId;
        if(idx>=3) row.style.display="none";
        const isOEM = (item.part_number===articleGlobalNumber && (item.part_make||"").toLowerCase()===articleGlobalBrand);
        const isSelectedBrand = (item.part_make||"").toLowerCase()===articleGlobalBrand;

        row.innerHTML=`
          <td>${item.supplier}</td>
          <td style="${isSelectedBrand?'background:#e6f7ff;font-weight:bold;':''}">${item.part_make??"-"}</td>
          <td>${item.part_number??"-"}</td>
          <td>${item.name??"-"}</td>
          <td>${item.quantity??0}</td>
          <td>${item.price??"-"}</td>
          <td>${item.warehouse??"-"}</td>
        `;
        if(isOEM) row.classList.add("oem-row");
        tbody.appendChild(row);
      });

      if(hiddenCount>0){
        const toggleBtn = headerRow.querySelector("button[data-toggle]");
        toggleBtn.addEventListener("click",(e)=>{
          e.preventDefault();
          const rows=tbody.querySelectorAll(`tr[data-group="${toggleId}"]`);
          const isCollapsed=rows[3].style.display==="none";
          rows.forEach((r,idx)=>{if(idx>=3) r.style.display=isCollapsed?"":"none";});
          toggleBtn.textContent=isCollapsed?"Свернуть":`Показать ещё ${hiddenCount}`;
        });
      }
    });
  }
});
</script>

<style>
.brand-list{list-style:none;padding:0;display:flex;flex-wrap:wrap;gap:8px}
.brand-list li{padding:6px 12px;border:1px solid #ccc;border-radius:6px;cursor:pointer;transition:all 0.2s;background:#f9f9f9;font-size:14px}
.brand-list li:hover{background:#e0f7fa;border-color:#4dd0e1}
.brand-list li.selected{background:#4dd0e1;color:#fff;font-weight:bold;border-color:#00acc1}
.oem-row{background:#fff8c6 !important;font-weight:bold}
</style>
