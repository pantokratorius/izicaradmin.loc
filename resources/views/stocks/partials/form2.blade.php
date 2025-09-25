<ul id="resultList"></ul>

<script>
document.addEventListener('DOMContentLoaded', function(){

    document.getElementById('searchButton').addEventListener('click', function(e){
        e.preventDefault();
        const searchInput = document.getElementById('searchInput').value;
        const ul = document.querySelector('#resultList');
        ul.innerHTML = '';

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
                                const li = document.createElement('li');
                                li.textContent = `${supplierResult.supplier}: Ошибка – ${supplierResult.error}`;
                                ul.appendChild(li);
                            } else if (supplierResult.data && supplierResult.data.length > 0) {
                                supplierResult.data.forEach(item => {
                                    const li = document.createElement('li');
                                    li.textContent = item
                                    ul.appendChild(li);
                                });
                            } else {
                                const li = document.createElement('li');
                                li.textContent = `<b>${supplierResult.supplier}</b>: Нет результатов`;
                                ul.appendChild(li);
                            }
                        });

                        read();
                    });
                }

                read();
            })
            .catch(err => console.error(err));
    });

});
</script>
