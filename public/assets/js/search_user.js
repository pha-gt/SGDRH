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
        const employeeName = item.querySelector('.user_name').textContent.toLowerCase();
        const employeeEmail = item.querySelector('.user_email').textContent.toLowerCase();
        const employeePuesto = item.querySelector('.row_puesto').textContent.toLowerCase();
        const employeeNomina = item.querySelector('.row_nomina').textContent.toLowerCase();
        const employeeStatus = item.querySelector('.row_estatus').textContent.toLowerCase();

        if (employeeName.includes(searchText) || employeeEmail.includes(searchText) || employeePuesto.includes(searchText) || employeeNomina.includes(searchText) || employeeStatus.includes(searchText)) {
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