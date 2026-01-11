export function addDiaEconomico() {
    var modal = $(".addDiaEconomico");
    modal.find(".modal_body").html(`
    <form id="diaEconomicoForm" action="admin_home.php?page=dashboard&action=addDiaEconomico" method="POST">
        <div class="input_group checkbox">
            <label>Selecciona el tipo de permiso</label>
            <div class="chip_container">
                <div class="chip" name="permiso-programado" data-value="permiso-programado">
                    <i class="fa-solid fa-circle-dot"></i>
                    Permiso programado
                </div>
                <div class="chip" name="permiso-fortuito" data-value="permiso-fortuito">
                    <i class="fa-solid fa-circle-dot"></i>
                    Permiso fortuito
                </div>
            </div>
        </div>
    
        <div class="input_group date">
            <label>Dias de ausencia</label>
            <div class="date_container">
                <div class="date_input">
                    <span>Fecha de inicio</span>
                    <input type="date" name="start-date" disabled>
                </div>
                <div class="date_input">
                    <span>Fecha de regreso</span>
                    <input type="date" name="end-date" disabled>
                </div>
            </div>
            <span id="diasContados" style="display:block;margin-top:10px;color:#009;">Días seleccionados: 0</span>
        </div>
        <input type="hidden" name="dias_economicos" id="dias_economicos" value="">
        <input type="hidden" name="permiso" id="permiso" value="">
        <button type="submit">Generar dia económico</button>
    </form>
    `);

    document.addEventListener('click', function (event) {
        if (event.target.classList.contains('chip')) {
            const chips = document.querySelectorAll('.chip');
            chips.forEach(c => {
                c.classList.remove('selected');
            });

            event.target.classList.add('selected');

            const selectedChipValue = event.target.getAttribute('data-value');
            document.querySelector('#permiso').value = selectedChipValue;

            const startDateInput = document.querySelector('input[name="start-date"]');
            const endDateInput = document.querySelector('input[name="end-date"]');

            function limpiarInputsDeFecha() {
                startDateInput.value = '';
                endDateInput.value = '';
            }

            const today = new Date().toISOString().split('T')[0];

            if (selectedChipValue === 'permiso-programado') {
                startDateInput.removeAttribute('disabled');
                endDateInput.removeAttribute('disabled');
                limpiarInputsDeFecha();
                startDateInput.setAttribute('min', today);
                startDateInput.removeAttribute('max');
                endDateInput.setAttribute('min', today);
                endDateInput.removeAttribute('max');
            } else if (selectedChipValue === 'permiso-fortuito') {
                startDateInput.removeAttribute('disabled');
                endDateInput.removeAttribute('disabled');
                limpiarInputsDeFecha();
                startDateInput.setAttribute('max', today);
                startDateInput.removeAttribute('min');
                endDateInput.setAttribute('max', today);
                endDateInput.removeAttribute('min');
            }
        }
    });

    document.getElementById('diaEconomicoForm').addEventListener('submit', function (event) {
        const startDateInput = document.querySelector('input[name="start-date"]');
        const endDateInput = document.querySelector('input[name="end-date"]');
        const permisoValue = document.querySelector('#permiso').value;
        const diasInput = document.getElementById('dias_economicos');

        if (!startDateInput.value || !endDateInput.value || !permisoValue) {
            event.preventDefault();
            alert('Por favor, completa todos los campos antes de enviar el formulario.');
            return;
        }

        const diasSeleccionados = parseInt(diasInput.value, 10);
        if (isNaN(diasSeleccionados) || diasSeleccionados <= 0) {
            event.preventDefault();
            alert('Limite de días excedido.');
            return;
        }
    });

    function actualizarDiasContados() {
        const startDateInput = document.querySelector('input[name="start-date"]');
        const endDateInput = document.querySelector('input[name="end-date"]');
        const diasContados = document.getElementById('diasContados');
        const diasInput = document.getElementById('dias_economicos');

        const maxDiasEconomicos = window.maxDiasEconomicos || 0;
        const diasEconomicosActuales = window.diasEconomicosActuales || 0;

        if (startDateInput.value && endDateInput.value) {
            const inicio = new Date(startDateInput.value);
            const regreso = new Date(endDateInput.value);
            if (regreso >= inicio) {
                const diffTime = regreso.getTime() - inicio.getTime();
                const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24)) + 1;
                if ((diasEconomicosActuales + diffDays) > maxDiasEconomicos) {
                    diasContados.textContent = "¡No puedes exceder el máximo de días económicos!";
                    diasInput.value = '';
                } else {
                    diasContados.textContent = "Días seleccionados: " + diffDays;
                    diasInput.value = diffDays;
                }
            } else {
                diasContados.textContent = "Días seleccionados: 0";
                diasInput.value = '';
            }
        } else {
            diasContados.textContent = "Días seleccionados: 0";
            diasInput.value = '';
        }
    }

    document.querySelector('input[name="start-date"]').addEventListener('change', actualizarDiasContados);
    document.querySelector('input[name="end-date"]').addEventListener('change', actualizarDiasContados);

    modal.show();
}
