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

  const suppliers = ["ABS","OtherSupplier","FakeSupplier","Mosvorechie"];

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
    suppliers.forEach(s => {
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
    itemsData = {}; // reset items

    const evtSource = new EventSource(`/api/items?article=${encodeURIComponent(article)}&brand=${encodeURIComponent(brand)}`);
    suppliers.forEach(s => {
      evtSource.addEventListener(s, e => collectItems(s, JSON.parse(e.data)));
    });
    evtSource.addEventListener("end", () => evtSource.close());
  }

  let itemsData = {}; // supplier -> part_key -> items array

  function collectItems(supplier, items) {
  if (!items || items.length === 0) return;

  if (!itemsData[supplier]) itemsData[supplier] = {};

  items.forEach(item => {
    const key = `${item.part_make}_${item.part_number}`;
    if (!itemsData[supplier][key]) itemsData[supplier][key] = [];
    // avoid duplicates
    if (!itemsData[supplier][key].some(i => i.part_number === item.part_number && i.part_make === item.part_make)) {
      itemsData[supplier][key].push(item);
    }
  });

  // Only re-render affected supplier group
  renderSupplierGroups(supplier);
}

  function renderResults() {
    tbody.innerHTML = "";

    Object.keys(itemsData).forEach(supplier => {
      const supplierGroups = itemsData[supplier];

      Object.keys(supplierGroups).forEach(partKey => {
        let groupItems = supplierGroups[partKey];

        // Sort: selected brand first, OEM second, then by price ascending
        groupItems.sort((a, b) => {
          const aPriority = (a.part_make === articleGlobalBrand && a.part_number === articleGlobalNumber) ? 0 :
                            (a.part_make === articleGlobalBrand) ? 1 : 2;
          const bPriority = (b.part_make === articleGlobalBrand && b.part_number === articleGlobalNumber) ? 0 :
                            (b.part_make === articleGlobalBrand) ? 1 : 2;

          if (aPriority !== bPriority) return aPriority - bPriority;
          return (parseFloat(a.price) || 0) - (parseFloat(b.price) || 0);
        });

        // Show only 3 cheapest
        const hiddenCount = groupItems.length - 3;

        // Header row
        const toggleId = `supplier-${supplier}-${partKey}-${Date.now()}`;
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
            row.children[2].innerHTML += ' <span style="color:red;font-weight:bold;">OEM</span>';
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
