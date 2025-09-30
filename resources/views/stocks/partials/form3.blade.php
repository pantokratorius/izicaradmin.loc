<div>
  <input type="text" id="searchInput" placeholder="Введите артикул..." style="width: 200px">
  <button id="searchButton">Найти</button>
</div>

<h3>Поставщики</h3>
<div id="suppliersButtons" style="margin-bottom:10px;"></div>
<button id="selectAllSuppliers" style="margin-bottom:10px;">Все</button>

<h3>Бренды</h3>
<ul id="brandsList" class="brand-list"></ul>

<hr>

<h3>Результаты</h3>
<table id="resultsTable" border="1" cellspacing="0" cellpadding="5">
  <thead>
    <tr>
      <th>Поставщик</th>
      <th>Бренд</th>
      <th>Номер детали</th>
      <th>Название</th>
      <th>Количество</th>
      <th>Цена</th>
      <th>Склад</th>
    </tr>
  </thead>
  <tbody></tbody>
</table>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const brandsList = document.getElementById("brandsList");
  const tbody = document.querySelector("#resultsTable tbody");
  const suppliersButtonsDiv = document.getElementById("suppliersButtons");
  const selectAllBtn = document.getElementById("selectAllSuppliers");

  let brandSet = new Map(); 
  let articleGlobalNumber = "";
  let articleGlobalBrand = "";
  let itemsData = {};
  let selectedSuppliers = new Set();

  const suppliers = ["ABS","Москворечье"];

  // создаем кнопки поставщиков
  suppliers.forEach(s => {
    const btn = document.createElement("button");
    btn.textContent = s;
    btn.style.marginRight = "5px";
    btn.classList.add("supplier-btn");
    btn.dataset.supplier = s;

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
  });

  // кнопка "Все / Снять все"
  selectAllBtn.addEventListener("click", (e)=>{
    e.preventDefault()
    const allSelected = selectedSuppliers.size === suppliers.length;
    selectedSuppliers.clear();
    suppliersButtonsDiv.querySelectorAll(".supplier-btn").forEach(b=>{
      if(!allSelected){
        selectedSuppliers.add(b.dataset.supplier);
        b.classList.add("active");
      } else {
        b.classList.remove("active");
      }
    });
    updateSelectAllText();
    renderResults();
  });

  function updateSelectAllText(){
    selectAllBtn.textContent = selectedSuppliers.size === suppliers.length ? "Снять все" : "Все";
  }

  // поиск брендов
  document.getElementById("searchButton").addEventListener("click", (e)=>{
    e.preventDefault();
    const article = document.getElementById("searchInput").value.trim();
    if(!article) return;

    articleGlobalNumber = article;
    articleGlobalBrand = "";
    brandSet.clear();
    brandsList.innerHTML = "";
    tbody.innerHTML = "";
    itemsData = {};
    selectedSuppliers.clear();
    updateSelectAllText();
    suppliersButtonsDiv.querySelectorAll(".supplier-btn").forEach(b=>b.classList.remove("active"));

    const evtSource = new EventSource(`/api/brands?article=${encodeURIComponent(article)}`);
    suppliers.forEach(s=> evtSource.addEventListener(s, e=> collectBrands(JSON.parse(e.data))));
    evtSource.addEventListener("end", ()=> { evtSource.close(); renderBrands(); });
  });

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
    const evtSource = new EventSource(`/api/items?article=${encodeURIComponent(article)}&brand=${encodeURIComponent(brand)}`);
    suppliers.forEach(s=> evtSource.addEventListener(s, e=> collectItems(s, JSON.parse(e.data))));
    evtSource.addEventListener("end", ()=> evtSource.close());
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

  function renderResults(){
    tbody.innerHTML = "";
    let allItems = [];

    Object.keys(itemsData).forEach(supplier=>{
      if(selectedSuppliers.size && !selectedSuppliers.has(supplier)) return;
      const supplierGroups = itemsData[supplier];
      Object.keys(supplierGroups).forEach(partKey=>{
        supplierGroups[partKey].forEach(item=>{
          allItems.push({...item, supplier});
        });
      });
    });

    allItems.sort((a,b)=>{
      const aMake = (a.part_make||"").toLowerCase();
      const bMake = (b.part_make||"").toLowerCase();
      const aSelected = aMake===articleGlobalBrand?0:1;
      const bSelected = bMake===articleGlobalBrand?0:1;
      if(aSelected!==bSelected) return aSelected-bSelected;

      const aOEM = (a.part_number===articleGlobalNumber && aMake===articleGlobalBrand)?0:1;
      const bOEM = (b.part_number===articleGlobalNumber && bMake===articleGlobalBrand)?0:1;
      if(aOEM!==bOEM) return aOEM-bOEM;

      return (parseFloat(a.price)||0)-(parseFloat(b.price)||0);
    });

    const grouped = {};
    allItems.forEach(item=>{
      const key = `${item.supplier}_${item.part_make}_${item.part_number}`;
      if(!grouped[key]) grouped[key]={supplier:item.supplier, items:[]};
      grouped[key].items.push(item);
    });

    Object.values(grouped).forEach(group=>{
      const groupItems = group.items;
      const hiddenCount = groupItems.length-3;
      const toggleId = `supplier-${group.supplier}-${groupItems[0].part_make}-${groupItems[0].part_number}-${Date.now()}`;

      const headerRow = document.createElement("tr");
      headerRow.style.backgroundColor="#f0f0f0";
      headerRow.innerHTML=`
        <td colspan="7">
          <strong>${group.supplier}</strong> – ${groupItems[0].part_make} ${groupItems[0].part_number}
          ${hiddenCount>0?`<button data-toggle="${toggleId}" style="margin-left:10px;">Показать ещё ${hiddenCount}</button>`:""}
        </td>
      `;
      tbody.appendChild(headerRow);

      groupItems.forEach((item,idx)=>{
        const row = document.createElement("tr");
        row.dataset.group=toggleId;
        if(idx>=3) row.style.display="none";
        const isOEM = (item.part_number===articleGlobalNumber && (item.part_make||"").toLowerCase()===articleGlobalBrand);
        const isSelectedBrand = (item.part_make||"").toLowerCase()===articleGlobalBrand;

        row.innerHTML=`
          <td></td>
          <td style="${isSelectedBrand?'background:#e6f7ff;font-weight:bold;':''}">${item.part_make??"-"}</td>
          <td>${item.part_number??"-"}</td>
          <td>${item.name??"-"}</td>
          <td>${item.quantity??0}</td>
          <td>${item.price??"-"}</td>
          <td>${item.warehouse??"-"}</td>
        `;
        if(isOEM) row.classList.add("oem-row");
        tbody.appendChild(row);
      });

      if(hiddenCount>0){
        const toggleBtn = headerRow.querySelector("button[data-toggle]");
        toggleBtn.addEventListener("click",(e)=>{
          e.preventDefault();
          const rows=tbody.querySelectorAll(`tr[data-group="${toggleId}"]`);
          const isCollapsed=rows[3].style.display==="none";
          rows.forEach((r,idx)=>{if(idx>=3) r.style.display=isCollapsed?"":"none";});
          toggleBtn.textContent=isCollapsed?"Свернуть":`Показать ещё ${hiddenCount}`;
        });
      }
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
.supplier-btn{padding:5px 12px;margin-bottom:5px;border:1px solid #ccc;border-radius:5px;cursor:pointer;background:#f0f0f0;transition:all 0.2s; color: #000}
.supplier-btn.active{background:#4dd0e1;color:#fff;border-color:#00acc1}
#selectAllSuppliers{padding:5px 12px;margin-bottom:10px;border:1px solid #ccc;border-radius:5px;cursor:pointer;background:#d9edf7;transition:all 0.2s; color: #000}
#selectAllSuppliers:hover{background:#bce8f1}
</style>
