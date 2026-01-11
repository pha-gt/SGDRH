export function addDiaCumple() {
    // Agrega estilos para el select si no existen aún
    if (!document.getElementById('diaCumpleStyles')) {
        const style = document.createElement('style');
        style.id = 'diaCumpleStyles';
        style.innerHTML = `
            #isNonWorkingSelect {
                padding: 8px 12px;
                border-radius: 6px;
                border: 1px solid #00A29A;
                background: #f8f8f8;
                color: #222;
                font-size: 1rem;
                margin-top: 6px;
                margin-bottom: 10px;
                transition: border-color 0.2s;
            }
            #isNonWorkingSelect:focus {
                border-color: #00796b;
                outline: none;
                background: #e0f7fa;
            }
            label#titulo1 {
                font-weight: bold;
                color: black;
                margin-bottom: 4px;
                display: block;
            }
        `;
        document.head.appendChild(style);
    }

    const publicHolidays = [
        '01-01', '02-05', '03-21', '05-01', '09-16', '11-02', '11-20', '12-25'
    ];

    function isNonWorkingDay(date) {
        const day = date.getDay();
        const formattedDate = date.toISOString().slice(5, 10);
        return day === 0 || day === 6 || publicHolidays.includes(formattedDate);
    }

    let dateInputHtml = `
        <div class="input_group">
            <label for="isNonWorkingSelect" id="pregunta">¿Tu cumpleaños es en día inhábil?</label>
            <select name="isNonWorking" id="isNonWorkingSelect">
                <option value="no" selected>No</option>
                <option value="yes">Sí</option>
            </select>
        </div>
        <div id="dayOptionContainer" style="display:none;">
            <div class="date_container_birthday">
                <div class="date_input">
                
                    <label for="before">Día antes</label>
                    <input type="radio" name="dayOption" id="before" value="before">
                    <label for="after">Día después</label>
                    <input type="radio" name="dayOption" id="after" value="after">
                </div>
            </div>
        </div>
        <input type="hidden" name="dayOption" id="dayOptionExact" value="exact">
    `;

    var modal = $(".addDiaCumple");
    modal.find(".modal_body").html(
        `<form action="admin_home.php?page=dashboard&action=addDiaCumple" method="POST">
             <div class="input_group date">
                <label id="titulo1"></label>
                ${dateInputHtml}
            </div>
            <button type="submit">Generar día de cumpleaños</button>
        </form>`
    );

    // Cambia la lógica para el select
    modal.find('#isNonWorkingSelect').on('change', function() {
        if ($(this).val() === 'yes') {
            $('#dayOptionContainer').show();
            $('#dayOptionExact').prop('disabled', true); // No enviar el hidden
        } else {
            $('#dayOptionContainer').hide();
            $('#dayOptionExact').prop('disabled', false); // Enviar el hidden
            // Desmarcar radios de dayOption si estaban seleccionados
            modal.find('input[name="dayOption"]').prop('checked', false);
        }
    });

    // Por defecto, ocultar opciones y habilitar el hidden
    $('#dayOptionContainer').hide();
    $('#dayOptionExact').prop('disabled', false);

    modal.show();
}