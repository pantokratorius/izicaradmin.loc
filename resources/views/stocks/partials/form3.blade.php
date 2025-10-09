<div>
  <input type="text" id="searchInput" placeholder="Введите артикул..." style="width: 200px">
  <button id="searchButton">Найти</button>
</div>

<!-- Лоадер -->
<div id="loader" style="display:none; margin:30px 0; text-align:center; position: absolute; left: 50%; top: 200px">
  <div class="spinner"></div>
  <div style="margin-top:8px; color:#00acc1; font-weight:bold;">Загружаем данные...</div>
</div>

<h3>Поставщики</h3>
<div id="suppliersButtons" style="margin-bottom:10px;"></div>
<button id="selectAllSuppliers" style="margin-bottom:10px;">Все</button>

<h3>Бренды</h3>
<ul id="brandsList" class="brand-list"></ul>

<hr>

<h3>Сортировка</h3>
<div id="sortButtons" style="margin-bottom:10px;">
  <button data-sort="price" class="sort-btn active">По цене</button>
  <button data-sort="delivery" class="sort-btn">По сроку</button>
</div>

<h3>Результаты</h3>
<table id="resultsTable" border="1" cellspacing="0" cellpadding="5">
  <thead>
    <tr>
      <th>Бренд</th>
      <th>Номер детали</th>
      <th>Название</th>
      <th>Количество</th>
      <th>Цена</th>
      <th>Срок</th>
      <th>Склад</th>
      <th>Поставщик</th>
    </tr>
  </thead>
  <tbody></tbody>
</table>

<button id="scrollTopBtn" title="Наверх">▲</button>

<style>

.supplier-btn.empty {
  background: #eee !important;
  color: #888 !important;
  border-color: #ccc !important;
  cursor: default ;
  opacity: 0.7;
}
.mini-loader {
  border: 2px solid #f3f3f3;
  border-top: 2px solid #00acc1;
  border-radius: 50%;
  width: 12px;
  height: 12px;
  margin-left: 6px;
  display: inline-block;
  animation: spin 1s linear infinite;
  vertical-align: middle;
}
#scrollTopBtn {
  display: none; /* скрыта по умолчанию */
  position: fixed;
  bottom: 90px;
  right: 30px;
  z-index: 1000;
  font-size: 18px;
  border: none;
  outline: none;
  background-color: #00abc193;
  color: white;
  cursor: pointer;
  padding: 12px 16px;
  border-radius: 50%;
  box-shadow: 0 4px 6px rgba(0,0,0,0.2);
  transition: opacity 0.3s ease, transform 0.2s ease;
}
#scrollTopBtn:hover {
  background-color: #007c91;
  transform: scale(1.1);
}

/* Спиннер */
.spinner {
  border: 4px solid #f3f3f3;
  border-top: 4px solid #00acc1;
  border-radius: 50%;
  width: 36px;
  height: 36px;
  animation: spin 1s linear infinite;
  margin: auto;
}
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const loader = document.getElementById("loader");
  const brandsList = document.getElementById("brandsList");
  const tbody = document.querySelector("#resultsTable tbody");
  const suppliersButtonsDiv = document.getElementById("suppliersButtons");
  const selectAllBtn = document.getElementById("selectAllSuppliers");
  const sortButtonsDiv = document.getElementById("sortButtons");
  const scrollTopBtn = document.getElementById("scrollTopBtn");

  let brandSet = new Map();
  let articleGlobalNumber = "";
  let articleGlobalBrand = "";
  let itemsData = {};
  let selectedSuppliers = new Set();
  let sortMode = "price";

  const suppliers = ["ABS","Москворечье", "Берг", "Фаворит", "Форум-Авто", 
                        "Профит Лига", "Микадо", "Росско", "STparts", "Авторусь", 
                        "Автоспутник", "Авто-Евро", "Авто Союз", "Ats-Auto"];
  let supplierLoading = {};

  // 🔹 показать лоадер
  function showLoader(){ loader.style.display = "block"; }
  // 🔹 скрыть лоадер
  function hideLoader(){ loader.style.display = "none"; }


  // показать кнопку при прокрутке
  window.addEventListener("scroll", () => {
    if (document.body.scrollTop > 600 || document.documentElement.scrollTop > 600) {
      scrollTopBtn.style.display = "block";
    } else {
      scrollTopBtn.style.display = "none";
    }
  });

  // плавный скролл наверх
  scrollTopBtn.addEventListener("click", (e) => {
    e.preventDefault()
    window.scrollTo({top: 0, behavior: 'instant'});
  });



  // создаем кнопки поставщиков
  suppliers.forEach(s => {
  const btn = document.createElement("button");
  btn.textContent = s;
  btn.style.marginRight = "5px";
  btn.classList.add("supplier-btn");
  btn.dataset.supplier = s;

  // маленький спиннер внутри кнопки
  const loaderSpan = document.createElement("span");
  loaderSpan.classList.add("mini-loader");
  loaderSpan.style.display = "none";
  btn.appendChild(loaderSpan);

  btn.addEventListener("click", (e)=>{
    e.preventDefault()
    if(selectedSuppliers.has(s)){
      selectedSuppliers.delete(s);
      btn.classList.remove("active");
    } else {
      selectedSuppliers.add(s);
      btn.classList.add("active");
    }
    updateSelectAllText();
    renderResults();
  });

  suppliersButtonsDiv.appendChild(btn);
  supplierLoading[s] = false;
});

  // кнопка "Все / Снять все"
  selectAllBtn.addEventListener("click", (e)=>{
    e.preventDefault()
    selectedSuppliers.clear();
    suppliersButtonsDiv.querySelectorAll(".supplier-btn").forEach(b=> b.classList.remove("active"));
    updateSelectAllText();
    renderResults();
  });

  function updateSelectAllText(){
    selectAllBtn.textContent = "Снять все";
  }

 // кнопки сортировки
sortButtonsDiv.querySelectorAll("button").forEach(btn=>{
  btn.addEventListener("click", (e)=>{

    e.preventDefault()
    // снять подсветку у всех
    sortButtonsDiv.querySelectorAll("button").forEach(b => b.classList.remove("active"));
    // подсветить текущую
    btn.classList.add("active");

    sortMode = btn.dataset.sort;
    renderResults();
  });
});


  // поиск брендов
  document.getElementById("searchButton").addEventListener("click", (e)=>{
    e.preventDefault();
    const article = document.getElementById("searchInput").value.trim();
    if(!article) return;

    showLoader();

    articleGlobalNumber = article;
    articleGlobalBrand = "";
    brandSet.clear();
    brandsList.innerHTML = "";
    tbody.innerHTML = "";
    itemsData = {};
    selectedSuppliers.clear();
    suppliersButtonsDiv.querySelectorAll(".supplier-btn").forEach(b=>b.classList.remove("active"));

    const evtSource = new EventSource(`/api/brands?article=${encodeURIComponent(article)}`);
    suppliers.forEach(s=> evtSource.addEventListener(s, e=> collectBrands(JSON.parse(e.data))));
    evtSource.addEventListener("end", ()=> {
      evtSource.close();
      hideLoader();
      renderBrands();
    });
  });

  function setSupplierLoading(supplier, state){
    supplierLoading[supplier] = state;
    const btn = suppliersButtonsDiv.querySelector(`[data-supplier="${supplier}"]`);
    if(btn){
      const loader = btn.querySelector(".mini-loader");
      loader.style.display = state ? "inline-block" : "none";
    }
  }

  function collectBrands(brands){
    brands.forEach(b=>{
      if(b){
        const key = b.toLowerCase();
        if(!brandSet.has(key)) brandSet.set(key,b);
      }
    });
  }

  function renderBrands(){
    brandsList.innerHTML = "";
    Array.from(brandSet.values()).sort((a,b)=>a.localeCompare(b)).forEach(brand=>{
      const li = document.createElement("li");
      li.textContent = brand;
      li.classList.toggle('selected', brand.toLowerCase()===articleGlobalBrand);

      li.addEventListener("click", ()=>{
        articleGlobalBrand = brand.toLowerCase();
        renderBrands();
        loadItems(articleGlobalNumber, brand);
      });

      brandsList.appendChild(li);
    });
  }

 function loadItems(article, brand){
  tbody.innerHTML = "";
  itemsData = {};
  showLoader();

  suppliers.forEach(s => setSupplierLoading(s, true)); // все в ожидании

  const evtSource = new EventSource(`/api/items?article=${encodeURIComponent(article)}&brand=${encodeURIComponent(brand)}`);
  suppliers.forEach(s=> evtSource.addEventListener(s, e=> {
    collectItems(s, JSON.parse(e.data));
    setSupplierLoading(s, false); // получены данные
  }));

  evtSource.addEventListener("end", ()=> {
    evtSource.close();
    hideLoader();

    // ✅ отмечаем пустые кнопки только после загрузки запчастей
    suppliers.forEach(s=>{
      const btn = suppliersButtonsDiv.querySelector(`[data-supplier="${s}"]`);
      if(!itemsData[s] || Object.keys(itemsData[s]).length===0){
        btn.classList.add("empty");
        btn.disabled = true;   // делаем неактивной
      } else {
        btn.classList.remove("empty");
        btn.disabled = false;  // снова активна
      }
    });
  });
}



function collectItems(supplier, items){
  if(!items || !items.length) return;

  if(!itemsData[supplier]) itemsData[supplier] = {};
  items.forEach(item=>{
    const key = `${item.part_make}_${item.part_number}`;
    if(!itemsData[supplier][key]) itemsData[supplier][key]=[];
    itemsData[supplier][key].push(item);
  });
  renderResults();
}

  function renderResults() {
    tbody.innerHTML = "";
    let allItems = [];

    // 🔹 Collect all items
    Object.keys(itemsData).forEach(supplier => {
        if (selectedSuppliers.size && !selectedSuppliers.has(supplier)) return;
        const supplierGroups = itemsData[supplier];
        Object.keys(supplierGroups).forEach(partKey => {
            supplierGroups[partKey].forEach(item => {
                allItems.push({ ...item, supplier });
            });
        });
    });

    const cleanBrand = b => (b || "").toLowerCase().trim();
    const cleanNumber = n => (n || "").replace(/[^a-z0-9]/gi, "").toLowerCase();
    const selectedBrand = cleanBrand(articleGlobalBrand);
    const selectedNumber = cleanNumber(articleGlobalNumber);

    // 🔹 Group by brand → part_number
    const grouped = {};
    allItems.forEach(item => {
        const brandKey = cleanBrand(item.part_make);
        const numberKey = cleanNumber(item.part_number);

        if (!grouped[brandKey]) grouped[brandKey] = { brand: item.part_make, parts: {} };
        if (!grouped[brandKey].parts[numberKey]) {
            grouped[brandKey].parts[numberKey] = { number: item.part_number, items: [] };
        }
        grouped[brandKey].parts[numberKey].items.push(item);
    });

    // 🔹 Sort items inside each part_number by price
    Object.values(grouped).forEach(brandGroup => {
        Object.values(brandGroup.parts).forEach(partGroup => {
            partGroup.items.sort((a, b) => (parseFloat(a.price) || 0) - (parseFloat(b.price) || 0));
        });
    });

    // 🔹 Compute cheapest price per brand
    const brandEntries = Object.values(grouped).map(bg => {
        const cheapest = Math.min(...Object.values(bg.parts).flatMap(pg => pg.items.map(i => parseFloat(i.price) || Infinity)));
        return { ...bg, cheapest };
    });

    // 🔹 Sort brands
    brandEntries.sort((a, b) => {
        const aBrand = cleanBrand(a.brand);
        const bBrand = cleanBrand(b.brand);
        const aSelected = aBrand === selectedBrand;
        const bSelected = bBrand === selectedBrand;
        if (aSelected !== bSelected) return bSelected - aSelected;
        if (!aSelected && !bSelected) return a.cheapest - b.cheapest;
        return a.brand.localeCompare(b.brand);
    });

    // 🔹 Render
        brandEntries.forEach(brandGroup => {
        const { brand, parts } = brandGroup;

        // Brand header
        const brandHeader = document.createElement("tr");
        brandHeader.style.backgroundColor = "#d9edf7";
        brandHeader.innerHTML = `<td colspan="8" style="font-weight:bold;">${brand}</td>`;
        brandHeader.id = `brand-${cleanBrand(brand)}`;
        tbody.appendChild(brandHeader);

        // Part groups sorted by OEM + price
        const partGroups = Object.values(parts).sort((a, b) => {
            const aIsOEM = cleanNumber(a.number) === selectedNumber && cleanBrand(brand) === selectedBrand;
            const bIsOEM = cleanNumber(b.number) === selectedNumber && cleanBrand(brand) === selectedBrand;
            if (aIsOEM !== bIsOEM) return bIsOEM - aIsOEM;
            const aPrice = Math.min(...a.items.map(i => parseFloat(i.price) || Infinity));
            const bPrice = Math.min(...b.items.map(i => parseFloat(i.price) || Infinity));
            return aPrice - bPrice;
        });

        partGroups.forEach(partGroup => {
    const { number, items } = partGroup;
    const hiddenCount = items.length - 3;
    const toggleId = `group-${brand}-${number}-${Date.now()}`;

    // Part header
    const partHeader = document.createElement("tr");
    partHeader.style.backgroundColor = "#f0f0f0";
    partHeader.innerHTML = `<td colspan="8">
        <strong>${number}</strong>
        ${hiddenCount > 0 ? `<button data-toggle="${toggleId}" style="margin-left:10px;">Показать ещё ${hiddenCount}</button>` : ""}
    </td>`;
    tbody.appendChild(partHeader);

    // Items
    items.forEach((item, idx) => {
    const row = document.createElement("tr");
    row.dataset.group = toggleId;
    if (idx >= 3) row.style.display = "none"; // hide extra rows

    const isOEM = cleanBrand(item.part_make) === selectedBrand && cleanNumber(item.part_number) === selectedNumber;
    const isSelectedBrand = cleanBrand(item.part_make) === selectedBrand;

    // Only first row gets article & name; remove borders for subsequent rows
    const borderStyle = idx === 0 ? "" : "border-top:0;border-bottom:0;";
    
    row.innerHTML = `
        <td style="${borderStyle}${isSelectedBrand ? 'background:#e6f7ff;font-weight:bold;' : ''}"></td>
        <td style="${borderStyle}">${idx === 0 ? item.part_number ?? "-" : ""}</td>
        <td style="${borderStyle}">${idx === 0 ? item.name ?? "-" : ""}</td>
        <td>${item.quantity ?? 0}</td>
        <td>${item.price ?? "-"}</td>
        <td>${item.delivery ?? "-"}</td>
        <td>${item.warehouse ?? "-"}</td>
        <td>${item.supplier ?? "-"}</td>
    `;

    if (isOEM) row.classList.add("oem-row");
    else if(isSelectedBrand) row.classList.add("brand-row");
    tbody.appendChild(row);
});

    // Expand/collapse
    if (hiddenCount > 0) {
        const toggleBtn = partHeader.querySelector("button[data-toggle]");
        toggleBtn.addEventListener("click", (e) => {
            e.preventDefault();
            const rows = tbody.querySelectorAll(`tr[data-group="${toggleId}"]`);
            const isCollapsed = rows[3].style.display === "none";
            rows.forEach((r, idx) => {
                if (idx >= 3) r.style.display = isCollapsed ? "" : "none";
            });
            toggleBtn.textContent = isCollapsed ? "Свернуть" : `Показать ещё ${hiddenCount}`;
        });
    }
});

        // Separator
        const separator = document.createElement("tr");
        separator.innerHTML = `<td colspan="8" style="height:8px;background:#fff;"></td>`;
        tbody.appendChild(separator);
    });
}




});
</script>

<style>
.brand-list{list-style:none;padding:0;display:flex;flex-wrap:wrap;gap:8px}
.brand-list li{padding:6px 12px;border:1px solid #ccc;border-radius:6px;cursor:pointer;transition:all 0.2s;background:#f9f9f9;font-size:14px}
.brand-list li:hover{background:#e0f7fa;border-color:#4dd0e1}
.brand-list li.selected{background:#4dd0e1;color:#fff;border-color:#00acc1}
.oem-row{background:#fff8c6 !important;font-weight:bold}
.brand-row{background:#fffce9 !important;font-weight:bold}
.supplier-btn{padding:5px 12px;margin-bottom:5px;border:1px solid #ccc;border-radius:5px;cursor:pointer;background:#f0f0f0;transition:all 0.2s; color: #000}
.supplier-btn.active{background:#4dd0e1;color:#fff;border-color:#00acc1}
#selectAllSuppliers{padding:5px 12px;margin-bottom:10px;border:1px solid #ccc;border-radius:5px;cursor:pointer;background:#d9edf7;transition:all 0.2s; color: #000}
#selectAllSuppliers:hover{background:#bce8f1}

.sort-btn {
  padding: 5px 12px;
  margin-right: 5px;
  border: 1px solid #ccc;
  border-radius: 5px;
  cursor: pointer;
  background: #f0f0f0;
  transition: all 0.2s;
  color: #000;
}
.sort-btn:hover {
  background: #e0f7fa;
  border-color: #4dd0e1;
}
.sort-btn.active {
  background: #4dd0e1;
  color: #fff;
  border-color: #00acc1;
}



td > button { 
  padding: 6px 10px !important ;
  background: #03a9f4 !important;
}




#brandNav {
  position: fixed;
  bottom: 0;
  left: 0;
  width: 100%;
  background: #fff;
  padding: 12px 10px;
  border-top: 1px solid #ddd;
  box-shadow: 0 -2px 6px rgba(0, 0, 0, 0.05);
  z-index: 1000;
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  transition: padding 0.3s ease, font-size 0.3s ease;
}

#brandNav.shrink {
  padding: 6px 10px;
}

.brand-nav-btn {
  padding: 6px 12px;
  border: 1px solid #ccc;
  border-radius: 5px;
  background: #f0f0f0;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 14px;
  color: #000;
}

#brandNav.shrink .brand-nav-btn {
  font-size: 12px;
  padding: 4px 8px;
}

.brand-nav-btn:hover {
  background: #e0f7fa;
  border-color: #4dd0e1;
}

.brand-nav-btn.active {
  background: #4dd0e1;
  color: #fff;
  border-color: #00acc1;
  
}


</style>
