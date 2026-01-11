<?php

require_once MODEL_PATH . "ConfigModel.php";
require_once MODEL_PATH . "UserModel.php";
require_once SERVER_PATH . "DB.php";

function generateModalEditUser($userID)
{
    $db = new DB();
    $userModel = new UserModel($db);
    $user = $userModel->getUserById($userID);
    $configModel = new ConfigModel($db);

    if (!$user) {
        return "<div class=\"modal editUser\">
        <div class=\"modal_content\">
            <div class=\"modal_header\">
                <h2>Editar empleado</h2>
                <button onclick=\"closeModal('editUser')\">Cerrar</button>
            </div>
            <div class=\"modal_body\">
                <span>Usuario no encontrado</span>
            </div>
        </div>";
    }

    $modal = "
    <div class=\"modal editUser\">
        <div class=\"modal_content\">
            <div class=\"modal_header\">
                <h2>Editar empleado </h2>
                <button onclick=\"closeModal('editUser')\">Cerrar</button>
            </div>
            <div class=\"modal_body\">
                <form id=\"editUserForm\" action=\"admin_home.php?page=manage_users&action=editUser\" method=\"POST\">
                    <input type=\"hidden\" name=\"empleadoID\" value=\"{$user['usuario_id']}\">
                    
                    <div class=\"input_group\">
                        <label for=\"empleadoNombre\">Nombre</label>
                        <input type=\"text\" name=\"empleadoNombre\" id=\"empleadoNombre\" value=\"{$user['usuario_nombre']}\" placeholder=\"Ingresa el nombre del empleado\">
                    </div>

                    <div class=\"input_group\">
                        <label for=\"empleadoCorreo\">Correo</label>
                        <input type=\"email\" name=\"empleadoCorreo\" id=\"empleadoCorreo\" value=\"{$user['usuario_email']}\" placeholder=\"Ingresa el correo del empleado\">
                    </div>

                    <div class=\"input_group\">
                        <label for=\"empleadoCurp\">Curp</label>
                        <input type=\"text\" name=\"empleadoCurp\" id=\"empleadoCurp\" value=\"{$user['usuario_curp']}\" placeholder=\"Ingresa el curp del empleado\"
                        pattern=\"^[A-Z]{4}[0-9]{6}[HM]{1}[A-Z]{2}[A-Z0-9]{3}[0-9A-Z]{2}$\" 
                        title=\"El CURP debe seguir el formato correcto (AAAA000101HDFRRL09)\" required >
                    </div>

                    <div class=\"input_group\">
                        <label for=\"empleadoRFC\">RFC</label>
                        <input type=\"text\" name=\"empleadoRFC\" id=\"empleadoRFC\" value=\"{$user['usuario_rfc']}\" placeholder=\"Ingresa el rfc del empleado\"
                        pattern=\"^[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}$\"
                        title=\"El RFC debe seguir el formato correcto (por ejemplo, ABCD123456XYZ)\" required>
                    </div>

                    <div class=\"input_group\">
                        <label for=\"empleadoNomina\">Numero de nomina</label>
                        <input type=\"text\" name=\"empleadoNomina\" id=\"empleadoNomina\" value=\"{$user['usuario_nomina']}\" placeholder=\"Ingresa el numero de nomina del empleado\"
                        pattern=\"^[A-ZÑ&]{3}[0-9]{4}$\"
                        title=\"El número de nómina debe seguir el formato correcto (por ejemplo, ABC1234 )\" required>
                    </div>";

    $rolList = $configModel->getRoles();
    $modal .= "
                    <div class=\"input_group\">
                        <label for=\"empleadoRol\">Rol</label>
                        <div class=\"select_menu\" id=\"empleadoRol\">
                            <div class=\"select_btn\">
                                <span class=\"sBtn_text\">" . $user['rol_nombre'] . "</span>
                                <i class=\"fa-solid fa-chevron-down\"></i>
                            </div>
                            <ul class=\"options\">";
    foreach ($rolList as $rol) {
        $modal .= "<li class=\"option\" data-value=\"" . $rol["rol_id"] . "\">
                       <span>" . $rol["rol_nombre"] . "</span>
                   </li>";
    }
    $modal .= "
                            </ul>
                        </div>
                    </div>";

    $puestoList = $configModel->getPuestos();
    $modal .= "
                    <div class=\"input_group\">
                        <label for=\"empleadoPuesto\">Puesto</label>
                        <div class=\"select_menu\" id=\"empleadoPuesto\">
                            <div class=\"select_btn\">
                                <span class=\"sBtn_text\">" . $user['puesto_nombre'] . "</span>
                                <i class=\"fa-solid fa-chevron-down\"></i>
                            </div>
                            <ul class=\"options\">";
    foreach ($puestoList as $puesto) {
        $modal .= "<li class=\"option\" data-value=\"" . $puesto["puesto_id"] . "\">
                       <span>" . $puesto["puesto_nombre"] . "</span>
                   </li>";
    }
    $modal .= "
                            </ul>
                        </div>
                    </div>";

    $jefesList = $configModel->getJefes();
    $modal .= "
                    <div class=\"input_group\">
                        <label for=\"empleadoInmediato\">Jefe inmediato</label>
                        <div class=\"select_menu\" id=\"empleadoInmediato\">
                            <div class=\"select_btn\">
                                <span class=\"sBtn_text\">" . $user['jefeInmediato_nombre'] . "</span>
                                <i class=\"fa-solid fa-chevron-down\"></i>
                            </div>
                            <ul class=\"options\">";
    foreach ($jefesList as $jefe) {
        $modal .= "<li class=\"option\" data-value=\"" . $jefe["jefeInmediato_id"] . "\">
                        " . (empty($jefe["usuario_foto"]) ? '<img src="assets/images/avatar.png">' : '<img src="data:image;base64,' . base64_encode($jefe['usuario_foto']) . '" >') . "
                        <div class=\"jefeInmediato_info\">
                            <h3>" . $jefe['jefeInmediato_nombre'] . "</h3>
                            <span>" . $jefe["areaAdscripcion_nombre"] . "</span>
                        </div>
                   </li>";
    }
    $modal .= "
                            </ul>
                        </div>
                    </div>";

    $areasList = $configModel->getAreas();
    $modal .= "
                    <div class=\"input_group\">
                        <label for=\"empleadoAdscripcion\">Área de adscripción</label>
                        <div class=\"select_menu\" id=\"empleadoAdscripcion\">
                            <div class=\"select_btn\">
                                <span class=\"sBtn_text\">" . $user['areaAdscripcion_nombre'] . "</span>
                                <i class=\"fa-solid fa-chevron-down\"></i>
                            </div>
                            <ul class=\"options\">";
    foreach ($areasList as $area) {
        $modal .= "<li class=\"option\" data-value=\"" . $area["areaAdscripcion_id"] . "\">
                        <span>" . $area["areaAdscripcion_nombre"] . "</span>
                   </li>";
    }
    $modal .= "
                            </ul>
                        </div>
                    </div>";

    $sindicatosList = $configModel->getSindicatos();
    $modal .= "
                    <div class=\"input_group\">
                        <label for=\"empleadoSindicato\">Sindicato</label>
                        <div class=\"select_menu\" id=\"empleadoSindicato\">
                            <div class=\"select_btn\">
                                <span class=\"sBtn_text\">" . $user['sindicato_nombre'] . "</span>
                                <i class=\"fa-solid fa-chevron-down\"></i>
                            </div>
                            <ul class=\"options\">";
    foreach ($sindicatosList as $sindicato) {
        $modal .= "<li class=\"option\" data-value=\"" . $sindicato["sindicato_id"] . "\">
                        <span>" . $sindicato["sindicato_nombre"] . "</span>
                   </li>";
    }
    $modal .= "
                            </ul>
                        </div>
                    </div>

                    <div class=\"input_group\">
                        <label for=\"empleadoEstatus\">Estatus</label>
                        <div class=\"select_menu\" id=\"empleadoEstatus\">
                            <div class=\"select_btn\">
                                <span class=\"sBtn_text\">" . $user['usuario_estatus'] . "</span>
                                <i class=\"fa-solid fa-chevron-down\"></i>
                            </div>
                            <ul class=\"options\">
                                <li class=\"option\" data-value=\"Vigente\">
                                    <span>Vigente</span>
                                </li>
                                <li class=\"option\" data-value=\"Baja\">
                                    <span>Baja</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class=\"input_group\">
                        <label for=\"empleadoDiasEconomicos\">Días económicos</label>
                        <input type=\"number\" name=\"userDiasEconomicos\" id=\"empleadoDiasEconomicos\" value=\"{$user['dias_economicos']}\" placeholder=\"Ingresa los días económicos\" min=\"0\" required>
                    </div>
                    

                    <input type=\"hidden\" name=\"empleadoRol\" id=\"rol\" value=\"{$user['rol_id']}\">
                    <input type=\"hidden\" name=\"empleadoPuesto\" id=\"puesto\" value=\"{$user['puesto_id']}\">
                    <input type=\"hidden\" name=\"empleadoJefe\" id=\"jefe\" value=\"{$user['jefeInmediato_id']}\">
                    <input type=\"hidden\" name=\"empleadoAdscripcion\" id=\"adscripcion\" value=\"{$user['areaAdscripcion_id']}\">
                    <input type=\"hidden\" name=\"empleadoSindicato\" id=\"sindicato\" value=\"{$user['sindicato_id']}\">
                    <input type=\"hidden\" name=\"empleadoEstatus\" id=\"estatus\" value=\"{$user['usuario_estatus']}\">

                    <button type=\"submit\">Actualizar empleado</button>
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

        let selectedOption = $(this).find(\"span\").first().text();
        let selectedValue = $(this).data('value');

        $(this).closest(\".select_menu\").find(\".sBtn_text\").text(selectedOption);
        $(this).closest(\".select_menu\").toggleClass(\"active\");

        if ($(this).closest('.select_menu').attr('id') === 'empleadoGenero') {
            $('#genero').val(selectedValue);
        } else if ($(this).closest('.select_menu').attr('id') === 'empleadoRol') {
            $('#rol').val(selectedValue);
        } else if($(this).closest('.select_menu').attr('id') === 'empleadoPuesto'){
            $('#puesto').val(selectedValue);
        }else if($(this).closest('.select_menu').attr('id') === 'empleadoInmediato'){
            $('#jefe').val(selectedValue);
        }else if($(this).closest('.select_menu').attr('id') === 'empleadoAdscripcion'){
            $('#adscripcion').val(selectedValue);
        }else if($(this).closest('.select_menu').attr('id') === 'empleadoSindicato'){
            $('#sindicato').val(selectedValue);
        }else if($(this).closest('.select_menu').attr('id') === 'empleadoEstatus'){
            $('#estatus').val(selectedValue);
        }

    });

    $('#editUserForm').on('submit', function(event) {
        const nombre = $('#empleadoNombre').val().trim();
        const correo = $('#empleadoCorreo').val().trim();
        const curp = $('#empleadoCurp').val().trim();
        const rfc = $('#empleadoRFC').val().trim();
        const nomina = $('#empleadoNomina').val().trim();
        const rol = $('#rol').val().trim();
        const puesto = $('#puesto').val().trim();
        const jefe = $('#jefe').val().trim();
        const adscripcion = $('#adscripcion').val().trim();
        const sindicato = $('#sindicato').val().trim();
        const estatus = $('#estatus').val().trim();

        if (!nombre || !correo || !curp || !rfc || !nomina || !rol || !puesto || !jefe || !adscripcion || !sindicato || !estatus) {
            event.preventDefault();
            alert('Por favor, completa todos los campos antes de enviar el formulario.');
        }
    });

});

</script>

";

    return $modal;
}
