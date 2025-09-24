<table id="resultList"></table>

<script>

    document.addEventListener('DOMContentLoaded', function(){


document.getElementById('searchButton').addEventListener('click', function(e){
    e.preventDefault();
    const searchInput = document.getElementById('searchInput').value;
    const tbody = document.querySelector('#resultList');
    tbody.innerHTML = '';

    fetch(`/api/supplier-search-stream?search=${encodeURIComponent(searchInput)}`)
        .then(res => {
            const reader = res.body.getReader();
            const decoder = new TextDecoder();
            let buffer = '';

            function read() {
                reader.read().then(({ done, value }) => {
                    if (done) return;
                    buffer += decoder.decode(value, { stream: true });

                    let lines = buffer.split("\n");
                    buffer = lines.pop(); // последний кусок оставляем для следующего

                    lines.forEach(line => {
                        if(!line.trim()) return;
                        const supplierResult = JSON.parse(line);

                        if (supplierResult.error) {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `<td>${supplierResult.supplier}</td><td colspan="4">Ошибка: ${supplierResult.error}</td>`;
                            tbody.appendChild(tr);
                        } else if (supplierResult.data && supplierResult.data.length > 0) {
                            supplierResult.data.forEach(item => {
                                const tr = document.createElement('tr');
                                tr.innerHTML = `
                                    <td>${supplierResult.supplier}</td>
                                    <td>${item.name ?? item.article}</td>
                                    <td>${item.price ?? '-'}</td>
                                    <td>${item.stock ?? '-'}</td>
                                    <td>Готово</td>
                                `;
                                tbody.appendChild(tr);
                            });
                        } else {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `<td>${supplierResult.supplier}</td><td colspan="4">Нет результатов</td>`;
                            tbody.appendChild(tr);
                        }
                    });

                    read();
                });
            }

            read();
        })
        .catch(err => console.error(err));
});



})
</script>
