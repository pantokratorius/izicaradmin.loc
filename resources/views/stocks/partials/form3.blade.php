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

  let brandSet = new Set();
  let articleGlobalNumber = "";
  let articleGlobalBrand = "";

  // Step 1: Get brands
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

  const itemsData = {}; // supplier -> part_key -> items array

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

  // Flatten all items with supplier info
  let allItems = [];
  Object.keys(itemsData).forEach(supplier => {
    Object.keys(itemsData[supplier]).forEach(partKey => {
      itemsData[supplier][partKey].forEach(item => {
        item._supplier = supplier; // attach supplier for rendering
        item._partKey = partKey;
        allItems.push(item);
      });
    });
  });

  // Sort: OEM items globally first, then by supplier -> partKey -> price
  allItems.sort((a, b) => {
    const aOEM = (a.part_number === articleGlobalNumber && a.part_make === articleGlobalBrand) ? 0 : 1;
    const bOEM = (b.part_number === articleGlobalNumber && b.part_make === articleGlobalBrand) ? 0 : 1;
    if (aOEM !== bOEM) return aOEM - bOEM;

    // Sort by supplier, then partKey, then price
    if (a._supplier !== b._supplier) return a._supplier.localeCompare(b._supplier);
    if (a._partKey !== b._partKey) return a._partKey.localeCompare(b._partKey);
    return (parseFloat(a.price) || 0) - (parseFloat(b.price) || 0);
  });

  // Group by supplier + partKey
  const grouped = {};
  allItems.forEach(item => {
    if (!grouped[item._supplier]) grouped[item._supplier] = {};
    if (!grouped[item._supplier][item._partKey]) grouped[item._supplier][item._partKey] = [];
    grouped[item._supplier][item._partKey].push(item);
  });

  // Render grouped items
  Object.keys(grouped).forEach(supplier => {
    const supplierGroups = grouped[supplier];

    Object.keys(supplierGroups).forEach(partKey => {
      const groupItems = supplierGroups[partKey];
      const hiddenCount = groupItems.length - 3;
      const toggleId = `supplier-${supplier}-${partKey}-${Date.now()}`;

      // Header row
      const headerRow = document.createElement("tr");
      headerRow.style.backgroundColor = "#f0f0f0";
      headerRow.innerHTML = `
        <td colspan="7">
          <strong>${supplier}</strong> - ${groupItems[0].part_make} ${groupItems[0].part_number}
          ${hiddenCount > 0 ? `<button data-toggle="${toggleId}" style="margin-left:10px;">Show ${hiddenCount} more</button>` : ""}
        </td>
      `;
      tbody.appendChild(headerRow);

      // Item rows
      groupItems.forEach((item, idx) => {
        const row = document.createElement("tr");
        row.dataset.group = toggleId;
        if (idx >= 3) row.style.display = "none";

        const isOEM = (item.part_number === articleGlobalNumber && item.part_make === articleGlobalBrand);

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
  });
}

});
</script>


<style>
  .oem-badge {
    background-color: #ff6b6b;
    color: white;
    font-weight: bold;
    padding: 2px 4px;
    margin-left: 4px;
    border-radius: 3px;
    font-size: 0.75em;
  }
</style>
