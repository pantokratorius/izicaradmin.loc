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
  let articleGlobal = "";

  // Step 1: Get brands
  document.getElementById("searchButton").addEventListener("click", (e) => {
    e.preventDefault();
    const article = document.getElementById("searchInput").value.trim();
    if (!article) return;

    articleGlobal = article;
    brandsList.innerHTML = "";
    tbody.innerHTML = "";
    brandSet.clear();

    const evtSource = new EventSource(`/api/brands?article=${encodeURIComponent(article)}`);

    evtSource.addEventListener("ABS", e => collectBrands(JSON.parse(e.data)));
    evtSource.addEventListener("OtherSupplier", e => collectBrands(JSON.parse(e.data)));
    evtSource.addEventListener("FakeSupplier", e => collectBrands(JSON.parse(e.data)));

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
      li.addEventListener("click", () => loadItems(articleGlobal, brand));
      brandsList.appendChild(li);
    });
  }

  // Step 2: Get items by brand + article
  function loadItems(article, brand) {
    tbody.innerHTML = "";

    const evtSource = new EventSource(`/api/items?article=${encodeURIComponent(article)}&brand=${encodeURIComponent(brand)}`);

    evtSource.addEventListener("ABS", e => addResults("ABS", JSON.parse(e.data)));
    evtSource.addEventListener("OtherSupplier", e => addResults("OtherSupplier", JSON.parse(e.data)));
    evtSource.addEventListener("FakeSupplier", e => addResults("FakeSupplier", JSON.parse(e.data)));

    evtSource.addEventListener("end", () => {
      evtSource.close();
    });
  }

  function addResults(supplier, results) {
    if (!results || results.length === 0) return;

    const toggleId = `supplier-${supplier}-${Date.now()}`;
    const hiddenCount = results.length - 3;

    // Insert supplier header row with toggle
    const headerRow = document.createElement("tr");
    headerRow.style.backgroundColor = "#f0f0f0";
    headerRow.innerHTML = `
      <td colspan="7">
        <strong>${supplier}</strong>
        ${results.length > 3 
          ? `<button data-toggle="${toggleId}" style="margin-left:10px;">Show ${hiddenCount} more</button>` 
          : ""}
      </td>
    `;
    tbody.appendChild(headerRow);

    // Insert items
    results.forEach((item, idx) => {
      const row = document.createElement("tr");
      row.dataset.group = toggleId;
      if (idx >= 3) row.style.display = "none"; // hide items beyond 3 by default
      row.innerHTML = `
        <td>${supplier}</td>
        <td>${item.part_make ?? "-"}</td>
        <td>${item.part_number ?? "-"}</td>
        <td>${item.name ?? "-"}</td>
        <td>${item.quantity ?? 0}</td>
        <td>${item.price ?? "-"}</td>
        <td>${item.warehouse ?? "-"}</td>
      `;
      tbody.appendChild(row);
    });

    // Add toggle functionality
    if (results.length > 3) {
      const toggleBtn = headerRow.querySelector("button[data-toggle]");
      toggleBtn.addEventListener("click", (e) => {
        e.preventDefault()
        const rows = tbody.querySelectorAll(`tr[data-group="${toggleId}"]`);
        const isCollapsed = rows[3].style.display === "none";

        rows.forEach((r, idx) => {
          if (idx >= 3) r.style.display = isCollapsed ? "" : "none";
        });

        toggleBtn.textContent = isCollapsed 
          ? "Show less"
          : `Show ${hiddenCount} more`;
      });
    }
  }
});
</script>
