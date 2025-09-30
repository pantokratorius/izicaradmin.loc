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

  let articleNumber = "";
  let selectedBrand = "";
  const itemsData = {}; // supplier -> part_make_part_number -> items array

  // Step 1: Get brands
  document.getElementById("searchButton").addEventListener("click", e => {
    e.preventDefault();
    const article = document.getElementById("searchInput").value.trim();
    if (!article) return;

    articleNumber = article;
    selectedBrand = "";
    brandsList.innerHTML = "";
    tbody.innerHTML = "";
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

  const brandSet = new Set();
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
        selectedBrand = brand;
        loadItems(articleNumber, brand);
      });
      brandsList.appendChild(li);
    });
  }

  // Step 2: Load items
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

    Object.keys(itemsData).forEach(supplier => {
      const groups = itemsData[supplier];

      Object.keys(groups).forEach(partKey => {
        let groupItems = groups[partKey];

        // Sort: OEM first, then by price ascending
        groupItems.sort((a, b) => {
          // Selected brand always first
          const aSelected = (a.part_make === selectedBrand) ? -1 : 1;
          const bSelected = (b.part_make === selectedBrand) ? -1 : 1;
          if (aSelected !== bSelected) return aSelected - bSelected;

          // OEM (part_number matches articleNumber) comes next
          const aOEM = (a.part_number === articleNumber) ? -1 : 1;
          const bOEM = (b.part_number === articleNumber) ? -1 : 1;
          if (aOEM !== bOEM) return aOEM - bOEM;

          // Then sort by price ascending
          return (parseFloat(a.price) || 0) - (parseFloat(b.price) || 0);
        });

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

          const isOEM = (item.part_number === articleNumber && item.part_make === selectedBrand);

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

        // Toggle
        if (hiddenCount > 0) {
          const toggleBtn = headerRow.querySelector("button[data-toggle]");
          toggleBtn.addEventListener("click", e => {
            e.preventDefault();
            const rows = tbody.querySelectorAll(`tr[data-group="${toggleId}"]`);
            const isCollapsed = rows[3].style.display === "none";
            rows.forEach((r, idx) => { if(idx>=3) r.style.display = isCollapsed ? "" : "none"; });
            toggleBtn.textContent = isCollapsed ? "Show less" : `Show ${hiddenCount} more`;
          });
        }
      });
    });
  }
});
</script>
