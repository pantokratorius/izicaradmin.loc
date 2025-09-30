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
  document.getElementById("searchButton").addEventListener("click", (e) => {
    e.preventDefault();
    const article = document.getElementById("searchInput").value.trim();
    if (!article) return;

    articleGlobalNumber = article;
    articleGlobalBrand = "";
    brandsList.innerHTML = "";
    tbody.innerHTML = "";
    brandSet.clear();
    Object.keys(itemsData).forEach(s => delete itemsData[s]);

    const evtSource = new EventSource(`/api/brands?article=${encodeURIComponent(article)}`);
    ["ABS","OtherSupplier","FakeSupplier","Mosvorechie"].forEach(supplier => {
      evtSource.addEventListener(supplier, e => collectBrands(JSON.parse(e.data)));
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
    const evtSource = new EventSource(`/api/items?article=${encodeURIComponent(article)}&brand=${encodeURIComponent(brand)}`);
    ["ABS","OtherSupplier","FakeSupplier","Mosvorechie"].forEach(supplier => {
      evtSource.addEventListener(supplier, e => collectItems(supplier, JSON.parse(e.data)));
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

    renderResultsIncremental();
  }

  function renderResultsIncremental() {
    Object.keys(itemsData).forEach(supplier => {
      const supplierGroups = itemsData[supplier];

      Object.keys(supplierGroups).forEach(partKey => {
        const groupItems = supplierGroups[partKey];

        // Sort: selected brand first, then OEM, then price ascending
        groupItems.sort((a, b) => {
          const aBrand = (a.part_make === articleGlobalBrand) ? 0 : 1;
          const bBrand = (b.part_make === articleGlobalBrand) ? 0 : 1;
          if (aBrand !== bBrand) return aBrand - bBrand;

          const aOEM = (a.part_number === articleGlobalNumber && a.part_make === articleGlobalBrand) ? 0 : 1;
          const bOEM = (b.part_number === articleGlobalNumber && b.part_make === articleGlobalBrand) ? 0 : 1;
          if (aOEM !== bOEM) return aOEM - bOEM;

          return (parseFloat(a.price) || 0) - (parseFloat(b.price) || 0);
        });

        const toggleId = `supplier-${supplier}-${partKey}`;
        if (tbody.querySelectorAll(`tr[data-group="${toggleId}"]`).length) return; // skip already rendered

        const hiddenCount = groupItems.length - 3;

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
            // tdNumber.innerHTML += ' <span style="color:red;font-weight:bold;">OEM</span>';
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
