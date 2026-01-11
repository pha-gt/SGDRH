const searchInput = document.getElementById('searchInput');
const tableItems = document.querySelectorAll('.table_body_item');
const tableHeader = document.querySelector(".table_header");
const tableContainer = document.getElementById('tableContainer');
const clearInput = document.querySelector(".fa-xmark");
const noResultsMessage = document.getElementById('noResultsMessage');

searchInput.addEventListener('keyup', () => {
    const searchText = searchInput.value.toLowerCase();
    let foundMatch = false;

    tableItems.forEach(item => {
        
        const documentTipo = item.querySelector('.row_tipo').textContent.toLowerCase();
        const documentFecha = item.querySelector('.row_fecha').textContent.toLowerCase();

        if (documentTipo.includes(searchText) || documentFecha.includes(searchText)) {
            item.style.display = 'flex';
            foundMatch = true;
        } else {
            item.style.display = 'none';
        }
    });

    if (foundMatch) {
        tableContainer.style.display = 'block';
        noResultsMessage.style.display = 'none';
    } else {
        tableHeader.style.display = 'none';
        tableContainer.style.display = 'none';
        noResultsMessage.style.display = 'flex';
    }

});

clearInput.addEventListener('click', () => {
    if (searchInput.value.trim() !== "") {
        searchInput.value = "";
        tableItems.forEach(item => {
            item.style.display = "flex";
        });
        tableHeader.style.display = 'flex';
        tableContainer.style.display = 'block';
        noResultsMessage.style.display = 'none';
    }
});