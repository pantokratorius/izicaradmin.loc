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

  // Step 1: Search brands
  document.getElementById("searchButton").addEventListener("click", (e) => {
    e.preventDefault();
    const article = document.getElementById("searchInput").value.trim();
    if (!article) return;

    articleGlobalNumber = article;
    articleGlobalBrand = "";
    brandSet.clear();
    tbody.innerHTML = "";
    brandsList.innerHTML = "";

    Object.keys(itemsData).forEach(k => delete itemsData[k]);

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
        articleGlobalBrand = brand;
        loadItems(articleGlobalNumber, brand);
      });
      brandsList.appendChild(li);
    });
  }

  // Step 2: Load items by brand + article
  function loadItems(article, brand) {
    tbody.innerHTML = "";

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

  function renderResults() {
    tbody.innerHTML = "";

    // Merge items across suppliers by part_make + part_number
    const mergedItems = {};
    Object.keys(itemsData).forEach(supplier => {
      const groups = itemsData[supplier];
      Object.keys(groups).forEach(partKey => {
        groups[partKey].forEach(item => {
          const key = `${item.part_make}_${item.part_number}`;
          if (!mergedItems[key]) mergedItems[key] = [];
          mergedItems[key].push({ ...item, supplier });
        });
      });
    });

    Object.keys(mergedItems).sort().forEach(partKey => {
      let items = mergedItems[partKey];

      // Sort OEM first, selected brand next, then price ascending
      items.sort((a, b) => {
        const aOEM = isOEM(a) ? 0 : isSelectedBrand(a) ? 1 : 2;
        const bOEM = isOEM(b) ? 0 : isSelectedBrand(b) ? 1 : 2;
        if (aOEM !== bOEM) return aOEM - bOEM;
        return (parseFloat(a.price) || 0) - (parseFloat(b.price) || 0);
      });

      const visibleItems = items.slice(0, 3);
      const hiddenCount = items.length - 3;
      const toggleId = `part-${partKey}-${Date.now()}`;

      // Header row
      const headerRow = document.createElement("tr");
      headerRow.style.backgroundColor = "#f0f0f0";
      headerRow.innerHTML = `
        <td colspan="7">
          <strong>${visibleItems[0].part_make} ${visibleItems[0].part_number}</strong>
          ${hiddenCount > 0 ? `<button data-toggle="${toggleId}" style="margin-left:10px;">Show ${hiddenCount} more</button>` : ""}
        </td>
      `;
      tbody.appendChild(headerRow);

      // Items rows
      items.forEach((item, idx) => {
        const row = document.createElement("tr");
        row.dataset.group = toggleId;
        if (idx >= 3) row.style.display = "none";

        const isOemItem = isOEM(item);
        const isSelected = isSelectedBrand(item);

        row.innerHTML = `
          <td>${item.supplier}</td>
          <td>${item.part_make ?? "-"}</td>
          <td>${item.part_number ?? "-"}</td>
          <td>${item.name ?? "-"}</td>
          <td>${item.quantity ?? 0}</td>
          <td>${item.price ?? "-"}</td>
          <td>${item.warehouse ?? "-"}</td>
        `;

        if (isOemItem) {
          row.style.backgroundColor = "#fff8c6";
          row.style.fontWeight = "bold";
          row.children[2].innerHTML += ' <span style="color:red;font-weight:bold;">OEM</span>';
        } else if (isSelected) {
          row.style.backgroundColor = "#e0f7fa";
        }

        tbody.appendChild(row);
      });

      // Toggle button
      if (hiddenCount > 0) {
        const toggleBtn = headerRow.querySelector("button[data-toggle]");
        toggleBtn.addEventListener("click", (e) => {
          e.preventDefault();
          const rows = tbody.querySelectorAll(`tr[data-group="${toggleId}"]`);
          const isCollapsed = rows[3].style.display === "none";
          rows.forEach((r, idx) => {
            if (idx >= 3) r.style.display = isCollapsed ? "" : "none";
          });
          toggleBtn.textContent = isCollapsed ? "Show less" : `Show ${hiddenCount} more`;
        });
      }
    });
  }

  function isOEM(item) {
    return item.part_number === articleGlobalNumber && item.part_make === articleGlobalBrand;
  }

  function isSelectedBrand(item) {
    return articleGlobalBrand && item.part_make === articleGlobalBrand && !isOEM(item);
  }
});
</script>
