function showAlert(type, message) {
    var alertDiv = $("#" + type);
    alertDiv.find(".message").html(message);
    alertDiv.addClass('show');
    alertDiv.show();
    if (alertDiv.length) {
        setTimeout(function () {
            alertDiv.removeClass("show");
            alertDiv.addClass("hidden");
        }, 5000);
    }
}

function hideAlert(type) {
    var alertElement = $("#" + type);
    if (alertElement.length) {
        setTimeout(function () {
            alertElement.addClass("hidden");
        }, 5000);
    }
}

function closeAlert(type) {
    var alertElement = $("#" + type);
    if (alertElement.length) {
        alertElement.hide();
    }
}

function deleteAlert(formId, usuario_nombre, folio) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: `Eliminar archivo de ${usuario_nombre} con Folio: ${folio}`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById(formId).submit();
        }
    });
}