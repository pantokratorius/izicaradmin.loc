<style>
  th {
    cursor: pointer;
    position: relative;
    padding-right: 15px;
  }
  th .arrow {
    position: absolute;
    right: 5px;
    font-size: 0.8em;
  }
  #filterBox {
    margin-bottom: 10px;
    padding: 5px;
    width: 200px;
  }
</style>

<input type="text" id="filterBox" placeholder="Filter results...">

<table id="resultsTable" border="1" cellspacing="0" cellpadding="5">
  <thead>
    <tr>
      <th onclick="sortTable(0)">Supplier <span class="arrow"></span></th>
      <th onclick="sortTable(1)">Brand <span class="arrow"></span></th>
      <th onclick="sortTable(2)">Part Number <span class="arrow"></span></th>
      <th onclick="sortTable(3)">Name <span class="arrow"></span></th>
      <th onclick="sortTable(4)">Quantity <span class="arrow"></span></th>
      <th onclick="sortTable(5)">Price <span class="arrow"></span></th>
      <th onclick="sortTable(6)">Warehouse <span class="arrow"></span></th>
    </tr>
  </thead>
  <tbody></tbody>
</table>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const tbody   = document.querySelector("#resultsTable tbody");
  const headers = document.querySelectorAll("#resultsTable thead th");
  let sortDirections = {}; // remember last sort direction per column

  // Sorting
  window.sortTable = function(colIndex) {
    const rows = Array.from(tbody.querySelectorAll("tr"));
    const isNumeric = [4, 5].includes(colIndex); // Quantity, Price

    sortDirections[colIndex] = !sortDirections[colIndex];
    const direction = sortDirections[colIndex] ? 1 : -1;

    rows.sort((a, b) => {
      let A = a.cells[colIndex].innerText.trim();
      let B = b.cells[colIndex].innerText.trim();

      if (isNumeric) {
        A = parseFloat(A) || 0;
        B = parseFloat(B) || 0;
        return (A - B) * direction;
      }
      return A.localeCompare(B) * direction;
    });

    tbody.innerHTML = "";
    rows.forEach(r => tbody.appendChild(r));

    headers.forEach(h => h.querySelector(".arrow").textContent = "");
    headers[colIndex].querySelector(".arrow").textContent = direction === 1 ? "▲" : "▼";
  }

  // Live filter
  document.getElementById("filterBox").addEventListener("input", function() {
    const query = this.value.toLowerCase();
    const rows = tbody.querySelectorAll("tr");
    rows.forEach(row => {
      const text = row.innerText.toLowerCase();
      row.style.display = text.includes(query) ? "" : "none";
    });
  });

  // Search button → SSE
  document.getElementById("searchButton").addEventListener("click", function(e) {
    e.preventDefault();

    const article = document.getElementById("searchInput").value.trim();
    if (!article) return;

    tbody.innerHTML = ""; // reset results

    const evtSource = new EventSource(`/api/parts?article=${encodeURIComponent(article)}`);

    function addResults(supplier, results) {
      results.forEach(item => {
        const row = document.createElement("tr");
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
    }

    evtSource.addEventListener("ABS", e => addResults("ABS", JSON.parse(e.data)));
    evtSource.addEventListener("OtherSupplier", e => addResults("OtherSupplier", JSON.parse(e.data)));
    evtSource.addEventListener("FakeSupplier", e => addResults("FakeSupplier", JSON.parse(e.data)));

    evtSource.addEventListener("end", () => {
      console.log("✅ All suppliers finished");
      evtSource.close();
    });
  });
});
</script>
