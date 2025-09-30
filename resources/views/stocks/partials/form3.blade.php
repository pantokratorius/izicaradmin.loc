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
  const tbody      = document.querySelector("#resultsTable tbody");

  let brandSet = new Map(); // ключ = lowercase brand, значение = оригинал
  let articleGlobalNumber = "";
  let articleGlobalBrand = ""; // хранится в lowercase

  // Step 1: Get brands
  document.getElementById("searchButton").addEventListener("click", (e) => {
    e.preventDefault();
    const article = document.getElementById("searchInput").value.trim();
    if (!article) return;

    articleGlobalNumber = article;
    articleGlobalBrand = "";
    brandSet.clear();
    brandsList.innerHTML = "";
    tbody.innerHTML = "";

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
      if (b) {
        const key = b.toLowerCase();
        if (!brandSet.has(key)) {
          brandSet.set(key, b); // сохраняем оригинальное название
        }
      }
    });
  }

  function renderBrands() {
    brandsList.innerHTML = "";
    Array.from(brandSet.values()).sort((a, b) => a.localeCompare(b)).forEach(brand => {
      const li = document.createElement("li");
      li.textContent = brand;

      if (brand.toLowerCase() === articleGlobalBrand) {
        li.style.fontWeight = "bold";
        li.style.color = "darkblue";
        li.style.textDecoration = "underline";
      } else {
        li.style.cursor = "pointer";
      }

      li.addEventListener("click", () => {
        articleGlobalBrand = brand.toLowerCase();
        renderBrands(); // перерисовать список с подсветкой
        loadItems(articleGlobalNumber, brand);
      });

      brandsList.appendChild(li);
    });
  }

  // Step 2: Load items
  function loadItems(article, brand) {
    tbody.innerHTML = "";
    itemsData = {}; // сбрасываем данные

    const evtSource = new EventSource(`/api/items?article=${encodeURIComponent(article)}&brand=${encodeURIComponent(brand)}`);
    ["ABS","OtherSupplier","FakeSupplier","Mosvorechie"].forEach(s => {
      evtSource.addEventListener(s, e => collectItems(s, JSON.parse(e.data)));
    });
    evtSource.addEventListener("end", () => evtSource.close());
  }

  let itemsData = {}; // supplier -> part_key -> items[]

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

    Object.keys(itemsData).forEach(supplier => {
      const supplierGroups = itemsData[supplier];

      Object.keys(supplierGroups).forEach(partKey => {
        let groupItems = supplierGroups[partKey];

        // сортировка: сначала выбранный бренд, потом OEM, потом по цене
        groupItems.sort((a, b) => {
          const aBrand = (a.part_make || "").toLowerCase();
          const bBrand = (b.part_make || "").toLowerCase();
          const aSelected = aBrand === articleGlobalBrand ? 0 : 1;
          const bSelected = bBrand === articleGlobalBrand ? 0 : 1;
          if (aSelected !== bSelected) return aSelected - bSelected;

          const aOEM = (aBrand === articleGlobalBrand && a.part_number === articleGlobalNumber) ? 0 : 1;
          const bOEM = (bBrand === articleGlobalBrand && b.part_number === articleGlobalNumber) ? 0 : 1;
          if (aOEM !== bOEM) return aOEM - bOEM;

          return (parseFloat(a.price) || 0) - (parseFloat(b.price) || 0);
        });

        const hiddenCount = groupItems.length - 3;
        const toggleId = `supplier-${supplier}-${partKey}-${Date.now()}`;

        const headerRow = document.createElement("tr");
        headerRow.style.backgroundColor = "#f0f0f0";
        headerRow.innerHTML = `
          <td colspan="7">
            <strong>${supplier}</strong> - ${groupItems[0].part_make} ${groupItems[0].part_number}
            ${hiddenCount > 0 ? `<button data-toggle="${toggleId}" style="margin-left:10px;">Показать ещё ${hiddenCount}</button>` : ""}
          </td>
        `;
        tbody.appendChild(headerRow);

        groupItems.forEach((item, idx) => {
          const row = document.createElement("tr");
          row.dataset.group = toggleId;
          if (idx >= 3) row.style.display = "none";

          const itemBrand = (item.part_make || "").toLowerCase();
          const isSelectedBrand = itemBrand === articleGlobalBrand;
          const isOEM = isSelectedBrand && item.part_number === articleGlobalNumber;

          row.innerHTML = `
            <td>${supplier}</td>
            <td>${item.part_make ?? "-"}</td>
            <td>${item.part_number ?? "-"}</td>
            <td>${item.name ?? "-"}</td>
            <td>${item.quantity ?? 0}</td>
            <td>${item.price ?? "-"}</td>
            <td>${item.warehouse ?? "-"}</td>
          `;

          if (isSelectedBrand) {
            row.style.backgroundColor = "#eaf3ff";
          }
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
            toggleBtn.textContent = isCollapsed ? "Скрыть" : `Показать ещё ${hiddenCount}`;
          });
        }
      });
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

</style> 