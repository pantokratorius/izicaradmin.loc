<div style="display: flex;">
  <div>
    <input type="text" id="searchInput" name="part_number" placeholder="Введите артикул..." style="width: 200px">
    <button id="searchButton">Найти</button>
  </div>
  <div>
    <input type="number" id="percent" placeholder="Проценты" style="width: 80px; margin-right: 10px; margin-left: 100px"><span id="percent_value">{{round($settings['percent'] > 0 ? $settings['percent'] : $settings['margin'], 0 )}}</span> %
  </div>
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
  <button id="togglePurchasePrice" class="btn btn-sm btn-secondary mb-2">
    👁 Не показывать закупку
</button>
</div>

<h3>Результаты</h3>
<table id="resultsTable" cellspacing="0" cellpadding="5">
  <thead>
    <tr>
      <th>Бренд</th>
      <th>Номер детали</th>
      <th>Название</th>
      <th>Количество</th>
      <th class="purchase-price-col">Цена</th>
      <th>Продажа</th>
      <th>Срок</th>
      <th style="text-align: center">Склад</th>
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
  right: 140px;
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
 document.addEventListener("DOMContentLoaded", () => {
  const button = document.getElementById("togglePurchasePrice");
  let hidden = localStorage.getItem("hidePurchasePrice") === "true";

  const updateState = () => {
    document.body.classList.toggle("hide-purchase-price", hidden);
    button.textContent = hidden ? "👁 Показать закупку" : "👁 Не показать закупку";
  };

  button.addEventListener("click", () => {
    hidden = !hidden;
    localStorage.setItem("hidePurchasePrice", hidden);
    updateState();
  });

  updateState();
});
</script>

<style>
  .hide-purchase-price .purchase-price-col {
    display: none !important;
  }
  .purchase-price-col {
    white-space: nowrap;
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
  let suppliersRawBrands = {};
  let itemsData = {};
  let selectedSuppliers = new Set();
  let sortMode = "price";

  const suppliers = ["ABS","Москворечье", "Берг", "Фаворит", "Форум-Авто",
                        "Профит Лига", "Микадо", "Росско", "STparts", "Авторусь",
                        "Автоспутник", "Авто-Евро", "Авто Союз", "Ats-Auto", "АвтоТрейд"];
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
    suppliers.forEach(s=> evtSource.addEventListener(s, e=> collectBrands(s, JSON.parse(e.data))));
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

  const brandGroupsRaw = {
    @foreach($brandGroups as $group)
      "{{ $group->display_name }}": "{{ implode(',', $group->aliases) }}",
    @endforeach
  };

  const brandGroups = {};
  Object.keys(brandGroupsRaw).forEach(key => {
      brandGroups[key] = brandGroupsRaw[key]
        .split(',')
        .map(s => s.trim())
        .filter(s => s.length > 0);
  });

  // Collect brands from API per supplier
  function collectBrands(supplier, brands) {
    if (!suppliersRawBrands[supplier]) suppliersRawBrands[supplier] = [];

    brands.forEach(rawName => {
        if (!rawName) return;

        // Track raw brand for this supplier
        if (!suppliersRawBrands[supplier].includes(rawName)) {
            suppliersRawBrands[supplier].push(rawName);
        }

        // Check if this rawName matches any group alias
        let matched = false;
        for (const displayName in brandGroups) {
            const aliases = brandGroups[displayName];
            if (aliases.some(a => a.toLowerCase() === rawName.toLowerCase())) {
                // Use normalized/display_name as key
                if (!brandSet.has(displayName)) brandSet.set(displayName, []);
                if (!brandSet.get(displayName).includes(rawName)) brandSet.get(displayName).push(rawName);
                matched = true;
                break;
            }
        }

        // If no group matched, just use rawName as its own display
        if (!matched) {
            if (!brandSet.has(rawName.toUpperCase())) brandSet.set(rawName.toUpperCase(), [rawName.toUpperCase()]);
        }
    });
}


function renderBrands() {
    brandsList.innerHTML = "";
    Array.from(brandSet.keys()).sort((a,b)=>a.localeCompare(b)).forEach(displayName => {
        const li = document.createElement("li");
        li.textContent = displayName;
        li.classList.toggle('selected', displayName === articleGlobalBrand);

        li.addEventListener("click", () => {
            articleGlobalBrand = displayName;
            renderBrands();

            // Build a supplier → raw brand map
            const supplierBrandMap = {};
            suppliers.forEach(supplier => {
                const rawBrands = suppliersRawBrands[supplier] || [];
                const aliases = brandGroups[displayName] || [];
                const match = rawBrands.find(r => aliases.some(a => a.toLowerCase() === r.toLowerCase()));
                supplierBrandMap[supplier] = match || null; // null if no match
            });

            // Pass supplierBrandMap to loadItems
            loadItems(articleGlobalNumber, supplierBrandMap);
        });

        brandsList.appendChild(li);
    });
}


function loadItems(article, supplierBrandMap) {
  tbody.innerHTML = "";
  itemsData = {};
  showLoader();

  const brandGroups = {};
  const suppliersWithoutBrand = [];
  let activeConnections = 0;

  // 🔹 Group suppliers by brand & track those without brand
  suppliers.forEach(supplier => {
    const rawBrand = supplierBrandMap[supplier];
    if (rawBrand) {
      if (!brandGroups[rawBrand]) brandGroups[rawBrand] = [];
      brandGroups[rawBrand].push(supplier);
    } else {
      suppliersWithoutBrand.push(supplier);
    }
    setSupplierLoading(supplier, true);
  });

  console.log("📌 Brand groups:", brandGroups);
  console.log("📌 Suppliers without brand:", suppliersWithoutBrand);

  // 🔹 Function to close loader + update buttons after all done
  function checkAllDone() {
    if (activeConnections === 0) {
      hideLoader();

      // Apply "empty" class & disable empty suppliers
      suppliers.forEach(s => {
        const btn = suppliersButtonsDiv.querySelector(`[data-supplier="${s}"]`);
        if (!itemsData[s] || Object.keys(itemsData[s]).length === 0) {
          btn.classList.add("empty");
          btn.disabled = true;
        } else {
          btn.classList.remove("empty");
          btn.disabled = false;
        }
      });
    }
  }

  // 🔹 Open EventSources per brand group
  Object.entries(brandGroups).forEach(([brand, suppliersList]) => {
    const url = `/api/items?article=${encodeURIComponent(article)}&brand=${encodeURIComponent(brand)}`;
    console.log(`[REQUEST] Brand: "${brand}", Suppliers: ${suppliersList.join(", ")}, URL: ${url}`);

    const evt = new EventSource(url);
    activeConnections++;

    suppliersList.forEach(supplier => {
      evt.addEventListener(supplier, e => {
        const data = JSON.parse(e.data);
        console.log(`[RESPONSE] Supplier: "${supplier}", Brand: "${brand}"`, data);
        collectItems(supplier, data);
        setSupplierLoading(supplier, false);
      });
    });

    evt.addEventListener("end", () => {
      evt.close();
      activeConnections--;
      checkAllDone();
    });
  });

  // 🔹 Common EventSource for suppliers without brand (use clicked brand)
  if (suppliersWithoutBrand.length > 0) {
    const brandParam = articleGlobalBrand || "";
    const url = `/api/items?article=${encodeURIComponent(article)}&brand=${encodeURIComponent(brandParam)}`;
    console.log(`[COMMON REQUEST] Brand: "${brandParam}", Suppliers: ${suppliersWithoutBrand.join(", ")}, URL: ${url}`);

    const evt = new EventSource(url);
    activeConnections++;

    suppliersWithoutBrand.forEach(supplier => {
      evt.addEventListener(supplier, e => {
        const data = JSON.parse(e.data);
        console.log(`[COMMON RESPONSE] Supplier: "${supplier}", Brand: "${brandParam}"`, data);
        collectItems(supplier, data);
        setSupplierLoading(supplier, false);
      });
    });

    evt.addEventListener("end", () => {
      evt.close();
      activeConnections--;
      checkAllDone();
    });
  }

  // 🔹 If no EventSources were opened
  if (activeConnections === 0) {
    hideLoader();
  }
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


function getBrandGroupKey(brand) {
  const cleaned = cleanBrand(brand);
  for (const [groupKey, variants] of Object.entries(brandGroups)) {
    if (variants.map(cleanBrand).includes(cleaned)) {
      return groupKey;
    }
  }
  return cleaned; // fallback to itself if not found in groups
}


// 🔹 Build reverse mapping: alias -> displayName
const aliasToDisplay = {};
Object.entries(brandGroups).forEach(([display, aliases]) => {
  aliases.forEach(a => aliasToDisplay[a.toLowerCase()] = display);
});

// 🔹 Group by normalized brand → part_number
const grouped = {};
allItems.forEach(item => {
  const rawBrand = item.part_make || "";
  const normalizedBrand = aliasToDisplay[rawBrand.toLowerCase()] || rawBrand; // fallback to raw if not in brandGroups
  const brandKey = cleanBrand(normalizedBrand);
  const numberKey = cleanNumber(item.part_number);

  if (!grouped[brandKey]) grouped[brandKey] = { brand: normalizedBrand, parts: {} };
  if (!grouped[brandKey].parts[numberKey]) {
    grouped[brandKey].parts[numberKey] = { number: item.part_number, items: [] };
  }
  grouped[brandKey].parts[numberKey].items.push(item);
});

// 🔹 Sort items inside each part_number by price or delivery
Object.values(grouped).forEach(brandGroup => {
  Object.values(brandGroup.parts).forEach(partGroup => {
    partGroup.items.sort((a, b) => {
      if (sortMode === "delivery") {
        const parseDays = v => {
          if (!v) return Infinity;
          v = String(v).toLowerCase().trim();
          if (v.includes("налич")) return 0;
          const numbers = v.match(/\d+/g);
          return numbers ? Math.min(...numbers.map(Number)) : Infinity;
        };
        return parseDays(a.delivery) - parseDays(b.delivery);
      } else {
        return (parseFloat(a.price) || 0) - (parseFloat(b.price) || 0);
      }
    });
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

 
        // Part groups sorted by OEM + price
        const partGroups = Object.values(parts).sort((a, b) => {
  // OEM group stays prioritized
  const aIsOEM = cleanNumber(a.number) === selectedNumber && cleanBrand(brand) === selectedBrand;
  const bIsOEM = cleanNumber(b.number) === selectedNumber && cleanBrand(brand) === selectedBrand;
  if (aIsOEM !== bIsOEM) return bIsOEM - aIsOEM;

  if (sortMode === "delivery") {
    const parseDays = v => {
      if (v === null || v === undefined) return Infinity;
      v = String(v).toLowerCase().trim();
      if (v.includes("налич")) return 0;
      if (/\b0\b/.test(v)) return 0;
      const numbers = v.match(/\d+/g);
      return numbers ? Math.min(...numbers.map(Number)) : Infinity;
    };

    const aMinDelivery = Math.min(...a.items.map(i => parseDays(i.delivery)));
    const bMinDelivery = Math.min(...b.items.map(i => parseDays(i.delivery)));
    // If both infinite / equal, fallback to price for stable sort
    if (aMinDelivery === bMinDelivery) {
      const aPrice = Math.min(...a.items.map(i => parseFloat(i.price) || Infinity));
      const bPrice = Math.min(...b.items.map(i => parseFloat(i.price) || Infinity));
      return aPrice - bPrice;
    }
    return aMinDelivery - bMinDelivery;
  } else {
    // default: sort by cheapest price in group
    const aPrice = Math.min(...a.items.map(i => parseFloat(i.price) || Infinity));
    const bPrice = Math.min(...b.items.map(i => parseFloat(i.price) || Infinity));
    return aPrice - bPrice;
  }
});

        partGroups.forEach(partGroup => {
    const { number, items } = partGroup;
    const hiddenCount = items.length - 3;
    const toggleId = `group-${brand}-${number}-${Date.now()}`;

    // Part header
    const partHeader = document.createElement("tr");
    partHeader.style.backgroundColor = "#f0f0f0";
    partHeader.id = `brand-${cleanBrand(brand)}`;
    partHeader.innerHTML = `

        ${hiddenCount > 0 ? `<td colspan="8"><button data-toggle="${toggleId}" style="margin-left:10px;">Показать ещё ${hiddenCount}</button></td>` : ""}
    `;
    tbody.appendChild(partHeader);

    // Items
    items.forEach((item, idx) => {
    const row = document.createElement("tr");
    row.dataset.group = toggleId;
    if (idx >= 3) row.style.display = "none"; // hide extra rows

    const itemBrandGroup = getBrandGroupKey(item.part_make);
    const selectedBrandGroup = getBrandGroupKey(selectedBrand);

    const isOEM =
      itemBrandGroup === selectedBrandGroup &&
      cleanNumber(item.part_number) === selectedNumber;

    const isSelectedBrand = itemBrandGroup === selectedBrandGroup;

      const percent = document.querySelector('#percent_value').textContent

    // Only first row gets article & name; remove borders for subsequent rows
    // const borderStyle = idx === 0 ? "" : "border-top:0;border-bottom:0;";
    const borderStyle = idx === 0 ? "" : "";

    row.innerHTML = `
        <td style="${borderStyle}">${idx === 0 ? brand ?? "-" : ""}</td>
        <td style="${borderStyle}">${idx === 0 ? item.part_number ?? "-" : ""}</td>
        <td style="${borderStyle}">${idx === 0 ? item.name ?? "-" : ""}</td>
        <td>${item.quantity ?? 0}</td>
        <td class="purchase-price-col" title="${item.price > 0 ? parseFloat(item.price).toFixed(2) : "-"}">${numberFormat2(item.price) ?? "-"}</td>
        <td style="white-space: nowrap;" title="${item.price ? (item.price * (1 + percent / 100)).toFixed(2) : "-"}"><b>${item.price ? (numberFormat(item.price * (1 + percent / 100))) : "-"}</b></td>
        <td>${item.delivery ?? "-"}</td>
        <td style="text-align:center; max-width: 200px">${item.warehouse ?? "-"}<br><p style="color: #2196f3; margin: 5px 0 0" class="supplier_name">${item.supplier ?? "-"}</p></td>
    `;

    if (isOEM) row.classList.add("oem-row");
    else if(isSelectedBrand) row.classList.add("brand-row");

      row.addEventListener("dblclick", () => {
      showQuantityPopup(item, row);
    });

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
        // const separator = document.createElement("tr");
        // separator.innerHTML = `<td colspan="8" style="height:8px;background:#fff;"></td>`;
        // tbody.appendChild(separator);
    });

// 🔹 Создать кнопки для перехода к брендам
    const brandNavDiv = document.getElementById("brandNav");
    if (brandNavDiv) brandNavDiv.remove(); // удалить старую панель, если была
      const navDiv = document.createElement("div");
      navDiv.id = "brandNav"; navDiv.className = "shrink";
      // navDiv.style.margin = "15px 0 0 220px";
      // navDiv.style.display = "flex";
      // navDiv.style.flexWrap = "wrap";
      // navDiv.style.gap = "8px";
      // navDiv.style.width = "calc(100% - 220px)"; // создаем кнопки навигации
      brandEntries.forEach(bg => {
        const btn = document.createElement("button");
        btn.textContent = bg.brand;
        btn.className = "brand-nav-btn";
        btn.addEventListener("click", (e) => {
          e.preventDefault();
          const target = document.getElementById(`brand-${bg.brand.toLowerCase()}`);
          if (target) { target.scrollIntoView({ behavior: "instant", block: "start" });
        }
      });
        navDiv.appendChild(btn);
      }); // вставляем панель над таблицей
      const table = document.getElementById("resultsTable");
      table.parentNode.insertBefore(navDiv, table);
      document.querySelector('#scrollTopBtn').style.bottom =   20 + 'px'
      // 🔹 Подсветка активного бренда при прокрутке
      const brandSections = brandEntries.map(bg => ({
        id: `brand-${bg.brand.toLowerCase()}`,
        name: bg.brand
      }
      ));
      window.removeEventListener("scroll", highlightActiveBrand);
      window.addEventListener("scroll", highlightActiveBrand);
      function highlightActiveBrand() {
        let current = ""; const scrollY = window.scrollY - 400; // небольшой отступ сверху
        for (let section of brandSections) {
          const el = document.getElementById(section.id);
          if (el && el.offsetTop <= scrollY) current = section.name;
        }
        document.querySelectorAll(".brand-nav-btn").forEach(btn => {
          btn.classList.toggle("active", btn.textContent === current);
        });
      }


}



function showQuantityPopup(item, row) {
  // If popup already exists, remove it first
  const existing = document.getElementById("quantityPopup");
  if (existing) existing.remove();

  // Create overlay
  const overlay = document.createElement("div");
  overlay.id = "quantityPopup";
  overlay.style.position = "fixed";
  overlay.style.top = 0;
  overlay.style.left = 0;
  overlay.style.width = "100%";
  overlay.style.height = "100%";
  overlay.style.background = "rgba(0,0,0,0.4)";
  overlay.style.display = "flex";
  overlay.style.justifyContent = "center";
  overlay.style.alignItems = "center";
  overlay.style.zIndex = 9999;

  // Create popup content
  const box = document.createElement("div");
  box.style.background = "#fff";
  box.style.padding = "20px";
  box.style.borderRadius = "10px";
  box.style.boxShadow = "0 4px 15px rgba(0,0,0,0.3)";
  box.style.textAlign = "center";
  box.style.minWidth = "280px";
  box.innerHTML = `
    <h3>Добавить товар</h3>
    <p><strong>${item.part_make}</strong> — ${item.part_number}</p>
    <p>${item.name}</p>
    <label>Количество:</label><br>
    <input type="number" id="popupQtyInput" value="1" min="1" style="margin:10px 0;padding:5px;width:80px;text-align:center;">
    <br>
    <button id="popupConfirm" style="margin-right:10px;padding:5px 12px;">✅ Добавить</button>
    <button id="popupCancel" style="padding:5px 12px;">❌ Отмена</button>
  `;

  overlay.appendChild(box);
  document.body.appendChild(overlay);

  // Focus input immediately
  const qtyInput = box.querySelector("#popupQtyInput");
  qtyInput.focus();

  // Cancel action
  box.querySelector("#popupCancel").addEventListener("click", () => overlay.remove());

  // Confirm action
  box.querySelector("#popupConfirm").addEventListener("click", () => {
    const qty = parseInt(qtyInput.value);
    if (isNaN(qty) || qty <= 0) {
      alert("Введите корректное количество");
      return;
    }
    overlay.remove();
    addToStocks(item, row, qty); // 👈 function to send data to backend
  });
}



// 🔹 Row flash helper
function rowFlash(row, color) {
  row.style.backgroundColor = color;
  setTimeout(() => (row.style.backgroundColor = ""), 700);
}

// 🔹 Toast popup helper
function showToast(message, type = "success") {
  let container = document.getElementById("toastContainer");
  if (!container) {
    container = document.createElement("div");
    container.id = "toastContainer";
    Object.assign(container.style, {
      position: "fixed",
      top: "30px",
      right: "30px",
      zIndex: 9999,
      display: "flex",
      flexDirection: "column",
      gap: "8px",
    });
    document.body.appendChild(container);
  }

  const toast = document.createElement("div");
  Object.assign(toast.style, {
    background: type === "error" ? "#f8d7da" : "#d4edda",
    color: type === "error" ? "#721c24" : "#155724",
    padding: "10px 15px",
    borderRadius: "6px",
    boxShadow: "0 2px 5px rgba(0,0,0,0.2)",
    fontSize: "14px",
    transition: "opacity 0.5s",
  });
  toast.textContent = message;

  container.appendChild(toast);

  setTimeout(() => {
    toast.style.opacity = "0";
    setTimeout(() => toast.remove(), 500);
  }, 2500);
}



let selectedStocks = [];

function addToStocks(item, row, quantity) {
  const percent = parseFloat(document.querySelector('#percent_value').textContent) || 0;
  const supplier = row.querySelector('.supplier_name')?.textContent?.trim() || "";

  const stockData = {
    part_number: item.part_number ?? "",
    part_make: item.part_make ?? "",
    name: item.name ?? "",
    quantity,
    purchase_price: parseFloat(item.price) || 0,
    sell_price: item.price ? (item.price * (1 + percent / 100)).toFixed(2) : 0,
    warehouse: item.warehouse ?? "",
    supplier,
  };

  const key = `${stockData.part_make}_${stockData.part_number}_${stockData.supplier}`;
  const existing = selectedStocks.find(s => `${s.part_make}_${s.part_number}_${s.supplier}` === key);

  if (existing) {
    existing.quantity += 1;
  } else {
    selectedStocks.push(stockData);
  }

  fetch("{{ route('store_ajax') }}", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
    },
    body: JSON.stringify(stockData),
  })
    .then(r => {
      if (!r.ok) throw new Error("HTTP " + r.status);
      return r.json();
    })
    .then(response => {
      rowFlash(row, "#d4edda");

      const qty = response?.data?.quantity ?? 1;

      if (response.message?.includes("increased")) {
        showToast(`➕ Кол-во увеличено (теперь ${qty} шт.)`);
      } else {
        showToast(`✅ Добавлено на склад (теперь ${qty} шт.)`);
      }
    })
    .catch(err => {
      console.error("Ошибка при добавлении на склад:", err);
      rowFlash(row, "#ffe6e6");
      showToast("❌ Ошибка при добавлении на склад", "error");
    });
}


// 🔹 Row flash helper
function rowFlash(row, color) {
  row.style.backgroundColor = color;
  setTimeout(() => (row.style.backgroundColor = ""), 700);
}

// 🔹 Toast popup helper
function showToast(message, type = "success") {
  let container = document.getElementById("toastContainer");
  if (!container) {
    container = document.createElement("div");
    container.id = "toastContainer";
    Object.assign(container.style, {
      position: "fixed",
      top: "30px",
      right: "30px",
      zIndex: 9999,
      display: "flex",
      flexDirection: "column",
      gap: "8px",
    });
    document.body.appendChild(container);
  }

  const toast = document.createElement("div");
  Object.assign(toast.style, {
    background: type === "error" ? "#f8d7da" : "#d4edda",
    color: type === "error" ? "#721c24" : "#155724",
    padding: "10px 15px",
    borderRadius: "6px",
    boxShadow: "0 2px 5px rgba(0,0,0,0.2)",
    fontSize: "14px",
    transition: "opacity 0.5s",
  });
  toast.textContent = message;

  container.appendChild(toast);

  setTimeout(() => {
    toast.style.opacity = "0";
    setTimeout(() => toast.remove(), 500);
  }, 2500);
}




});
</script>
<script>
const percentInput = document.getElementById('percent');

percentInput.addEventListener('blur', function() {
    const element = this;
    let percent = this.value;

    fetch('{{ route("settings.updatePercent") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ percent: percent })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Remove old success message if exists
            document.querySelectorAll('.successMessage').forEach(el => el.remove());

            // Create success div
            const msgDiv = document.createElement('div');
            msgDiv.className = 'successMessage';
            msgDiv.style.cssText = `
                float: right;
                background: #d4edda;
                color: #155724;
                padding: 10px 15px;
                border: 1px solid #c3e6cb;
                border-radius: 4px;
                margin-bottom: 15px;
            `;
            msgDiv.textContent = data.message;

            // Insert into .main
            document.querySelector('.main').prepend(msgDiv);

            // Clear input
            element.value = '';

            // Update displayed percent value
            document.querySelector('#percent_value').textContent = data.value;

            // 🔹 Recalculate "Продажа" dynamically
            const newPercent = parseFloat(data.value) || 0;
            document.querySelectorAll('#resultsTable tbody tr').forEach(row => {
                const priceCell = row.children[4];   // column with original price
                const saleCell  = row.children[5];   // column with selling price ("Продажа")

                if (priceCell && saleCell) {
                    const basePrice = parseFloat(priceCell.title);
                    if (!isNaN(basePrice)) {
                        const newSale = (basePrice * (1 + newPercent / 100)).toFixed(2);
                        saleCell.textContent = numberFormat(newSale);
                    }
                }
            });

            // Remove after 3 seconds
            setTimeout(() => msgDiv.remove(), 3000);
        } else {
            console.error('Update failed');
        }
    })
    .catch(err => console.error(err));
});

</script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const brandsList = document.getElementById("brandsList");

    // Tooltip element
    const tooltip = document.createElement("div");
    tooltip.className = "copy-tooltip";
    document.body.appendChild(tooltip);

    brandsList.addEventListener("contextmenu", function(e) {
        e.preventDefault();
        const li = e.target.closest("li");
        if (!li) return;

        const brandName = li.textContent.trim();

        // ✅ Safe clipboard copy (with fallback)
        const copyText = async (text) => {
            if (navigator.clipboard && window.isSecureContext) {
                try {
                    await navigator.clipboard.writeText(text);
                    return true;
                } catch (err) {
                    console.error("Clipboard write failed:", err);
                    return false;
                }
            } else {
                // fallback for HTTP/non-secure context
                const textarea = document.createElement("textarea");
                textarea.value = text;
                textarea.style.position = "fixed";
                textarea.style.opacity = "0";
                document.body.appendChild(textarea);
                textarea.focus();
                textarea.select();
                try {
                    document.execCommand("copy");
                    document.body.removeChild(textarea);
                    return true;
                } catch (err) {
                    console.error("Fallback copy failed:", err);
                    document.body.removeChild(textarea);
                    return false;
                }
            }
        };

        copyText(brandName).then(success => {
            if (success) {
                tooltip.textContent = `Скопировано: ${brandName}`;
                tooltip.style.left = e.pageX + 10 + "px";
                tooltip.style.top = e.pageY + 10 + "px";
                tooltip.style.opacity = 1;
                setTimeout(() => tooltip.style.opacity = 0, 1500);
            } else {
                tooltip.textContent = "Ошибка копирования";
                tooltip.style.left = e.pageX + 10 + "px";
                tooltip.style.top = e.pageY + 10 + "px";
                tooltip.style.opacity = 1;
                setTimeout(() => tooltip.style.opacity = 0, 1500);
            }
        });
    });
});

</script>
<style>
/* Tooltip style */
.copy-tooltip {
    position: absolute;
    background: #4CAF50;
    color: white;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 12px;
    pointer-events: none;
    opacity: 0;
    transition: opacity 0.3s;
    z-index: 1000;
}
</style>

<style>
.brand-list{list-style:none;padding:0;display:flex;flex-wrap:wrap;gap:8px;}
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
  bottom: 50%; /* adjust to your layout */
  transform: translateY(50%);
  right: 0;
  width: 0; /* collapsed width */
  max-height: 100vh;
  overflow-y: auto;
  overflow-x: hidden;
  background: linear-gradient(to right, rgb(255 255 255 / 5%) 10px, rgb(255 255 255 / 98%) 10px, rgba(255, 255, 255, 0.5) 100%);
  border-left: 50px solid transparent;
  border-radius: 10px 0 0 10px;
  /* box-shadow: -2px 0 6px rgba(0,0,0,0.1); */
  transition: width 0.3s ease;
  padding: 10px 5px;
  z-index: 1000;
  box-sizing: border-box;
}

/* expand on hover */
#brandNav:hover {
  width: auto; 
  background: #fff;
    border-left: 10px solid #fff;
}

/* content inside */
#brandNav.shrink {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  justify-content: center;
  gap: 6px;
}

/* brand buttons */
.brand-nav-btn {
  background: none;
  border: none;
  color: #333;
  padding: 3px 5px;
  text-align: left;
  width: 100%;
  font-size: 13px;
  border-radius: 6px;
  cursor: pointer;
  transition: background 0.2s, color 0.2s, transform 0.2s;
  white-space: nowrap;
}

/* icons can be added later if needed */
.brand-nav-btn:hover {
  background: #e6f2ff;
  color: #007bff;
}

/* highlight active */
.brand-nav-btn.active {
  background: #007bff;
  color: white;
  font-weight: bold;
}

/* scroll customization */
#brandNav::-webkit-scrollbar {
  width: 4px;
}
#brandNav::-webkit-scrollbar-thumb {
  background-color: rgba(0,0,0,0.2);
  border-radius: 10px;
}

</style>
