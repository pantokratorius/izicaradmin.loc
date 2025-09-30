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
  let articleGlobal = "";

  document.getElementById("searchButton").addEventListener("click", (e) => {
    e.preventDefault();
    const article = document.getElementById("searchInput").value.trim();
    if (!article) return;

    articleGlobal = article;
    brandSet.clear();
    brandsList.innerHTML = "";
    tbody.innerHTML = "";

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
    brands.forEach(b => { if(b) brandSet.add(b); });
  }

  function renderBrands() {
    brandsList.innerHTML = "";
    Array.from(brandSet).sort().forEach(brand => {
      const li = document.createElement("li");
      li.textContent = brand;
      li.style.cursor = "pointer";
      li.addEventListener("click", () => loadItems(articleGlobal, brand));
      brandsList.appendChild(li);
    });
  }

  function loadItems(article, brand) {
    tbody.innerHTML = "";

    const evtSource = new EventSource(`/api/items?article=${encodeURIComponent(article)}&brand=${encodeURIComponent(brand)}`);
    ["ABS","OtherSupplier","FakeSupplier","Mosvorechie"].forEach(supplier => {
      evtSource.addEventListener(supplier, e => addResults(supplier, JSON.parse(e.data)));
    });
    evtSource.addEventListener("end", () => evtSource.close());
  }

  function addResults(supplier, results) {
    if (!results || results.length === 0) return;

    // Group by part_make + part_number
    const grouped = {};
    results.forEach(item => {
      const key = `${item.part_make ?? ''}||${item.part_number ?? ''}`;
      if (!grouped[key]) grouped[key] = [];
      grouped[key].push(item);
    });

    // Sort each group by price ascending
    for (const key in grouped) {
      grouped[key].sort((a,b) => parseFloat(a.price ?? 0) - parseFloat(b.price ?? 0));
    }

    // Sort groups: OEM / searched article first
    const sortedKeys = Object.keys(grouped).sort((a,b) => {
      const aOem = a.includes(articleGlobal) ? -1 : 0;
      const bOem = b.includes(articleGlobal) ? -1 : 0;
      return aOem - bOem;
    });

    sortedKeys.forEach(key => {
      const items = grouped[key];
      const toggleId = `supplier-${supplier}-${key}-${Date.now()}`;

      // Header row
      const headerRow = document.createElement("tr");
      headerRow.style.backgroundColor = "#f0f0f0";
      headerRow.innerHTML = `<td colspan="7"><strong>${supplier} - ${key.replace('||',' / ')}</strong></td>`;
      tbody.appendChild(headerRow);

      // Insert items (first 3 visible, rest hidden)
 items.forEach((item, idx) => {
  const row = document.createElement("tr");
  row.dataset.group = toggleId;
  if (idx >= 3) row.style.display = "none";

  // Check if OEM / searched article
  const isOEM = (item.part_number ?? "").includes(articleGlobal);

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
    row.style.backgroundColor = "#fff8c6"; // light yellow for OEM
    row.style.fontWeight = "bold";
  }

  tbody.appendChild(row);
});

      // Show more toggle
      if (items.length > 3) {
        const toggleBtn = document.createElement("button");
        toggleBtn.textContent = `Show ${items.length - 3} more`;
        toggleBtn.style.marginLeft = "10px";
        toggleBtn.addEventListener("click", (e) => {
          e.preventDefault()
          const rows = tbody.querySelectorAll(`tr[data-group="${toggleId}"]`);
          const isCollapsed = rows[3].style.display === "none";
          rows.forEach((r, idx) => { if(idx >= 3) r.style.display = isCollapsed ? "" : "none"; });
          toggleBtn.textContent = isCollapsed ? "Show less" : `Show ${items.length - 3} more`;
        });

        const headerCell = headerRow.querySelector("td");
        headerCell.appendChild(toggleBtn);
      }
    });
  }
});
</script>

