export function addReporteIncidencia() {
    var modal = $(".addIncidencia");
    modal.find(".modal_body").html(`
    <form id="reporteIncidenciaForm" action="admin_home.php?page=dashboard&action=addReporteIncidencia" method="POST">

        <div class="input_group checkbox">
            <label>Selecciona el tipo de incidencia</label>
            <div class="chip_container">
                <div class="chip" data-value="salida-anticipada">
                    <i class="fa-solid fa-circle-dot"></i>
                    Salida anticipada
                </div>
                <div class="chip" data-value="retardo">
                    <i class="fa-solid fa-circle-dot"></i>
                    Retardo
                </div>
                <div class="chip" data-value="omision-entrada">
                    <i class="fa-solid fa-circle-dot"></i>
                    Omision Entrada
                </div>
                <div class="chip" data-value="omision-salida">
                    <i class="fa-solid fa-circle-dot"></i>
                    Omision Salida
                </div>
            </div>
        </div>

        <div class="input_group">
            <label>Selecciona la fecha de la incidencia</label>
            <input type="date" id="fecha" name="fecha">
        </div>
        
        <div class="input_group">
            <label>Motivo de la incidencia</label>
            <input type="text" id="motivo" name="motivo" placeholder="Ingresa el motivo de la incidencia">
        </div>

        <input type="hidden" name="incidencia" id="incidencia">

        <button type="submit">Generar reporte de incidencia</button>

    </form>
    `);

    modal.show();

    $(document).ready(function () {
        const $inputFecha = $('#fecha');
        const añoActual = new Date().getFullYear();

        const minFecha = `${añoActual}-01-01`;
        const maxFecha = `${añoActual}-12-31`;

        $inputFecha.attr('min', minFecha);
        $inputFecha.attr('max', maxFecha);
    });

    $('.chip').on('click', function() {
        $('.chip').removeClass('selected');
        $(this).addClass('selected');
        $('#incidencia').val($(this).data('value'));
    });

    document.getElementById('reporteIncidenciaForm').addEventListener('submit', function (event) {
        const fecha = document.getElementById('fecha').value;
        const motivo = document.getElementById('motivo').value;
        const incidencia = document.getElementById('incidencia').value;

        console.log('fecha:', fecha, 'motivo:', motivo, 'incidencia:', incidencia);

        if (!fecha || !motivo || !incidencia) {
            event.preventDefault();
            alert('Por favor, completa todos los campos antes de enviar el formulario.');
        }
    });
}