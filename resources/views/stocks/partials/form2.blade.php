<table id="resultList"></table>

<script async >

    document.addEventListener('DOMContentLoaded', function(){



const suppliers = [
    { 
        name: 'ABS', 
        // api: 'https://abstd.ru/api-search?auth=3515fab2a59d5d51b91f297a8be3ad5f&with_cross=1&agreement_id=28415',
        api: 'https://abstd.ru/api-brands?auth=3515fab2a59d5d51b91f297a8be3ad5f&format=json',
        search: '&article=', 
    },
    // { name: 'Supplier B', api: '/api/supplier-b' },
    // { name: 'Supplier C', api: '/api/supplier-c' },
];

function addItemRow(supplier) {
    const tbody = document.querySelector('#resultList');
    const tr = document.createElement('tr');
    tr.dataset.supplier = supplier.name;

    // Пока данные не пришли
    tr.innerHTML = `
        <td>${supplier.name}</td>
        <td>–</td>
        <td>–</td>
        <td>–</td>
        <td class="status">Загрузка...</td>
    `;

    tbody.appendChild(tr);
    return tr;
}

function updateRow(tr, data) {
    tr.innerHTML = `
        <td>${tr.dataset.supplier}</td>
        <td>${data.product_name}</td>
        <td>${data.price}</td>
        <td>${data.stock}</td>
        <td class="status">Готово</td>
    `;
}

    document.getElementById('searchButton').addEventListener('click', function(e) {
        e.preventDefault()

        document.querySelector('#resultList').innerHTML = ''
        const searchInput = document.getElementById('searchInput').value;


        suppliers.forEach(supplier => {
            const row = addItemRow(supplier);

            fetch(`${supplier.api}${supplier.search}${encodeURIComponent(searchInput)}`)
                .then(res => res.json())
                .then(data => {
                    updateRow(row, data);
                })
                .catch(err => {
                    row.querySelector('.status').textContent = 'Ошибка';
                    console.error(`Ошибка у ${supplier.name}:`, err);
                });
        });
    });



})
</script>
