<div>
  <input type="text" id="searchInput" placeholder="Enter article...">
  <button id="searchButton">Find Brands</button>
</div>

<h3>Brands</h3>
<ul id="brandsList"></ul>

<hr>

<h3>Results</h3>
<table id="resultsTable" border="1" cellspacing="0" cellpadding="5">
  <thead>
    <tr>
      <th>Supplier</th>
      <th>Brand</th>
      <th>Part Number</th>
      <th>Name</th>
      <th>Quantity</th>
      <th>Price</th>
      <th>Warehouse</th>
    </tr>
  </thead>
  <tbody></tbody>
</table>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const brandsList = document.getElementById("brandsList");
  const tbody = document.querySelector("#resultsTable tbody");

  let brandSet = new Set();
  let articleGlobalNumber = "";
  let articleGlobalBrand = "";
  const itemsData = {}; // supplier -> part_key -> items array

  // Step 1: Get brands
  document.getElementById("searchButton").addEventListener("click", e => {
    e.preventDefault();
    const article = document.getElementById("searchInput").value.trim();
    if (!article) return;

    articleGlobalNumber = article;
    articleGlobalBrand = "";
    brandSet.clear();
    brandsList.innerHTML = "";
    tbody.innerHTML = "";
    
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
      if (b) brandSet.add(b);
    });
  }

  function renderBrands() {
    brandsList.innerHTML = "";
    Array.from(brandSet).sort((a,b) => a.toLowerCase().localeCompare(b.toLowerCase())).forEach(brand => {
      const li = document.createElement("li");
      li.textContent = brand;
      li.style.cursor = "pointer";
      li.addEventListener("click", () => {
        articleGlobalBrand = brand;
        loadItems(articleGlobalNumber, brand);
      });
      brandsList.appendChild(li);
    });
  }

  // Step 2: Load items by brand + article
  function loadItems(article, brand) {
    tbody.innerHTML = "";
    Object.keys(itemsData).forEach(k => delete itemsData[k]); // clear previous
    const evtSource = new EventSource(`/api/items?article=${encodeURIComponent(article)}&brand=${encodeURIComponent(brand)}`);
    ["ABS","OtherSupplier","FakeSupplier","Москворечье"].forEach(s => {
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

  function renderResults() {
    tbody.innerHTML = "";

    // Сбор всех элементов в один массив для глобальной сортировки
    let allItems = [];
    Object.keys(itemsData).forEach(supplier => {
      const groups = itemsData[supplier];
      Object.keys(groups).forEach(key => {
        groups[key].forEach(item => allItems.push({...item, supplier}));
      });
    });

    // Глобальная сортировка: выбранный бренд -> OEM -> цена
    allItems.sort((a, b) => {
      const aBrand = (a.part_make || "").toLowerCase();
      const bBrand = (b.part_make || "").toLowerCase();
      const selectedBrandLower = articleGlobalBrand.toLowerCase();

      const aSelected = aBrand === selectedBrandLower ? 0 : 1;
      const bSelected = bBrand === selectedBrandLower ? 0 : 1;
      if (aSelected !== bSelected) return aSelected - bSelected;

      const aOEM = (aBrand === selectedBrandLower && a.part_number === articleGlobalNumber) ? 0 : 1;
      const bOEM = (bBrand === selectedBrandLower && b.part_number === articleGlobalNumber) ? 0 : 1;
      if (aOEM !== bOEM) return aOEM - bOEM;

      return (parseFloat(a.price) || 0) - (parseFloat(b.price) || 0);
    });

    // Группировка по поставщикам для отображения
    const grouped = {};
    allItems.forEach(item => {
      if (!grouped[item.supplier]) grouped[item.supplier] = [];
      grouped[item.supplier].push(item);
    });

    Object.keys(grouped).forEach(supplier => {
      const supplierItems = grouped[supplier];

      const headerRow = document.createElement("tr");
      headerRow.style.backgroundColor = "#f0f0f0";
      headerRow.innerHTML = `<td colspan="7"><strong>${supplier}</strong></td>`;
      tbody.appendChild(headerRow);

      supplierItems.forEach(item => {
        const row = document.createElement("tr");
        const itemBrand = (item.part_make || "").toLowerCase();
        const isSelectedBrand = itemBrand === articleGlobalBrand.toLowerCase();
        const isOEM = isSelectedBrand && item.part_number === articleGlobalNumber;

        row.innerHTML = `
          <td>${item.supplier}</td>
          <td>${item.part_make ?? "-"}</td>
          <td>${item.part_number ?? "-"}</td>
          <td>${item.name ?? "-"}</td>
          <td>${item.quantity ?? 0}</td>
          <td>${item.price ?? "-"}</td>
          <td>${item.warehouse ?? "-"}</td>
        `;

        if (isSelectedBrand) row.style.backgroundColor = "#eaf3ff";
        if (isOEM) {
          row.style.backgroundColor = "#fff8c6";
          row.style.fontWeight = "bold";
          row.children[2].innerHTML += ' <span style="color:red;font-weight:bold;">OEM</span>';
        }

        tbody.appendChild(row);
      });
    });
  }
});
</script>
