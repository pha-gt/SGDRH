function openModal(modalClass) {
    console.log("open modalClass", modalClass);
    $('.' + modalClass).css('display', 'block');
}

generarPDF = function (id) {
    console.log(id);
    window.open('generar_pdf.php?id=' + id, '_blank');
}

generarPDF2 = function (id, id2) {
    console.log(id, id2);
    window.open('generar_pdf.php?id=' + id + '&id2=' + id2, '_blank');
}

function closeModal(modalClass) {
    $('.' + modalClass).css('display', 'none');
}

// function closeModal(className) {
//     const modal = document.querySelector(`.modal.${className}`);
//     if (modal) {
//         modal.remove(); // elimina completamente el modal del DOM
//     }
// }
