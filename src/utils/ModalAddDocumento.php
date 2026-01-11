<?php

require_once MODEL_PATH . "UserModel.php";
require_once SERVER_PATH . "DB.php";

function generateModalDocument()
{

    $modal = "
    <div class=\"modal documento\">
        <div class=\"modal_content\">
            <div class=\"modal_header\">
                <h2>Subir documento</h2>
                <button onclick=\"closeModal('documento')\">Cerrar</button>
            </div>
            <div class=\"modal_body\">
                <form action=\"admin_home.php?page=dashboard&action=addDocument\" method=\"POST\" enctype=\"multipart/form-data\">
                <div class=\"input_group\">
                    <label for=\"empleado\">Empleado</label>
                    <div class=\"select_menu\" id=\"empleado\">
                        <div class=\"select_btn\">
                            <span class=\"sBtn_text\">Selecciona al empleado</span>
                            <i class=\"fa-solid fa-chevron-down\"></i>
                        </div>
                        <ul class=\"options\">";

    $db = new DB();
    $userModel = new UserModel($db);
    $usersList = $userModel->getUsersList();

    foreach ($usersList as $usuario) {
        $modal .= "<li class=\"option\" data-value=\"" . $usuario["usuario_id"] . "\">
                       " . (empty($usuario["usuario_foto"]) ? '<img src="assets/images/avatar.png">' : '<img src="data:image;base64,' . base64_encode($usuario['usuario_foto']) . '" >') . "
                       <span>" . $usuario["usuario_nombre"] . "</span>
                   </li>";
    }

    $modal .= "
                        </ul>
                    </div>
            </div>
                            
            <div class=\"input_group checkbox\">
                <label>Selecciona el tipo de documento</label>
                <div class=\"chip_container\">
                    <div class=\"chip\" data-value=\"Dia economico\">
                        <i class=\"fa-solid fa-circle-dot\"></i>
                        Dia economico
                    </div>
                    <div class=\"chip\" data-value=\"Dia de cumpleaños\">
                        <i class=\"fa-solid fa-circle-dot\"></i>
                        Dia de cumpleaños
                    </div>
                    <div class=\"chip\" data-value=\"Reporte de incidencia\">
                        <i class=\"fa-solid fa-circle-dot\"></i>
                        Reporte de incidencia
                    </div>
                </div>
            </div>
                            
            <div class=\"input_group\">
                <label for=\"documentoFecha\">Selecciona la fecha de creación del documento</label>
                <input type=\"date\" id=\"documentoFecha\" name=\"date\">
            </div>
                            
            <div class=\"input_group\">
                <label for=\"documentoEstatus\">Estatus</label>
                <div class=\"select_menu\" id=\"documentoEstatus\">
                    <div class=\"select_btn\">
                        <span class=\"sBtn_text\">Selecciona el estatus del documento</span>
                        <i class=\"fa-solid fa-chevron-down\"></i>
                    </div>
                    <ul class=\"options\">
                        <li class=\"option\" data-value=\"Entregado\">
                            <span>Entregado</span>
                        </li>
                        <li class=\"option\" data-value=\"Pendiente\">
                            <span>Pendiente</span>
                        </li>
                        <li class=\"option\" data-value=\"Sin Entregar\">
                            <span>Sin Entregar</span>
                        </li>
                    </ul>
                </div>
            </div>
                            
            <div class=\"input_group\">
                <label>Adjuntar documento</label>
                    <input type=\"file\" name=\"documento\">
            </div>
                            
            <input type=\"hidden\" name=\"user\" id=\"user\">
            <input type=\"hidden\" name=\"documentType\" id=\"documentType\">
            <input type=\"hidden\" name=\"status\" id=\"status\">

            <button class=\"insert_documento_btn\">Subir daaaocumento</button>
            </form>
        </div>
    </div>
</div>

<script>

$(document).ready(function () {
    $(document).on(\"click\", \".select_menu .select_btn\", function () {
        $(this).closest(\".select_menu\").toggleClass(\"active\");
    });

    $(document).on(\"click\", \".options .option\", function (e) {
        e.stopPropagation();

        $(this).closest('.options').find('.option').removeClass('selected');
        $(this).addClass('selected');

        let selectedOption = $(this).find(\"h3, span\").first().text();
        let selectedValue = $(this).data('value');

        $(this).closest(\".select_menu\").find(\".sBtn_text\").text(selectedOption);
        $(this).closest(\".select_menu\").toggleClass(\"active\");

        if ($(this).closest('.select_menu').attr('id') === 'empleado') {
            $('#user').val(selectedValue);
        } else if ($(this).closest('.select_menu').attr('id') === 'documentoEstatus') {
            $('#status').val(selectedValue);
        } else if ($(this).closest('.select_menu').attr('id') === 'updateStatus') {
            $('#estatus').val(selectedValue);
        }
    });

    const chips = document.querySelectorAll('.chip');
    const input = document.getElementById('documentType');

    chips.forEach(chip => {
        chip.addEventListener('click', function () {
            chips.forEach(c => {
                c.classList.remove('selected');
            });

            this.classList.add('selected');
            input.value = this.getAttribute('data-value');
        });
    });

});

</script>

";

    return $modal;
}
