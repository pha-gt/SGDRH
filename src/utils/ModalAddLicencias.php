<?php
require_once MODEL_PATH . "UserModel.php";
require_once SERVER_PATH . "DB.php";

function generateModalLicencias($areaAdscripcion_id)
{
    $db = new DB();
    $userModel = new UserModel($db);
    $usersList = $userModel->getUsersList();
    $areaAdscripcionId = $_SESSION['user_area'];
    $useName = $_SESSION['user_name'];
    $userRoleId = $_SESSION['user_role'];

    $diasRestantesPorUsuario = [];
    $puestosEspeciales = [16, 17, 18, 19, 20, 21];
    $puestosEspecialesJson = json_encode($puestosEspeciales);

    foreach ($usersList as $usuario) {
        $usuarioId = $usuario["usuario_id"];
        $fechaIngreso = new DateTime($usuario['usuario_fechaIngreso']);
        $fechaActual = new DateTime();
        $diferenciaDias = $fechaIngreso->diff($fechaActual)->days;

        $diasTotales = 0;
        if (in_array($usuario['puesto_id'], $puestosEspeciales)) {
            if ($diferenciaDias < 90) $diasTotales = 15;
            elseif ($diferenciaDias < 180) $diasTotales = 30;
            elseif ($diferenciaDias < 365) $diasTotales = 60;
            else $diasTotales = 60;
        } else {
            if ($diferenciaDias < 90) $diasTotales = 15;
            elseif ($diferenciaDias < 180) $diasTotales = 30;
            else $diasTotales = 60;
        }

        $diasUtilizados = 0;
        $licencias = $userModel->getLicenciasByUsuarioId($usuarioId);
        foreach ($licencias as $licencia) {
            if ($licencia['status'] === 'Entregado') {
                $inicio = new DateTime($licencia['fecha_salida']);
                $fin = new DateTime($licencia['fecha_regreso']);
                while ($inicio <= $fin) {
                    $dia = $inicio->format('N');
                    if ($dia < 6) $diasUtilizados++;
                    $inicio->modify('+1 day');
                }
            }
        }

        $diasRestantesPorUsuario[$usuarioId] = max(0, $diasTotales - $diasUtilizados);
    }

    $diasRestantesJson = json_encode($diasRestantesPorUsuario);

    ob_start();
    ?>

<div class="modal licencias">
    <div class="modal_content">
        <div class="modal_header">
            <h2>Crear Licencias</h2>
            <button onclick="closeModal('licencias')">Cerrar</button>
        </div>
        <div class="modal_body">
            <form action="admin_home.php?page=licencias&action=licencias" method="POST" enctype="multipart/form-data">
                <div class="input_group">
                    <label for="empleado">Empleado</label>
                    <div class="select_menu" id="usuario_id_menu">
                        <div class="select_btn">
                            <span class="sBtn_text">Selecciona al empleado</span>
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <ul class="options">
                            <li class="input_group">
                                <input type="text" class="search_input" placeholder="Buscar empleado..." />
                            </li>
                            <?php foreach ($usersList as $usuario) {
                                if (($userRoleId != 1 && $userRoleId != 2) && $usuario['usuario_nombre'] == $useName) continue;
                                if ($userRoleId == 4 && $usuario['areaAdscripcion_id'] != $areaAdscripcionId) continue;
                                echo '<li class="option" data-value="' . $usuario["usuario_id"] . '" data-puesto="' . $usuario["puesto_id"] . '">'
                                    . (empty($usuario["usuario_foto"]) ? '<img src="assets/images/avatar.png">' : '<img src="data:image;base64,' . base64_encode($usuario['usuario_foto']) . '" >')
                                    . '<span>' . $usuario["usuario_nombre"] . '</span></li>';
                            } ?>
                        </ul>
                    </div>
                    <input type="hidden" name="usuario_id" id="usuario_id" required>
                </div>

                <div id="dias_restantes_info" style="margin-bottom:10px; font-weight:bold;"></div>

                <div id="seis_meses_group" class="input_group" style="display:none;">
                    <label for="viaticos">¿Se tomarán 6 meses?</label>
                    <div class="select_menu" id="meses_menu">
                        <div class="select_btn">
                            <span class="sBtn_text">Selecciona</span>
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <ul class="options">
                            <li class="option" data-value="No">No</li>
                            <li class="option" data-value="Si">Sí</li>
                        </ul>
                    </div>
                    <input type="hidden" name="viaticos" id="viaticos" value="No" required>
                </div>

                <div class="input_group">
                    <label for="fecha_salida">Fecha de Salida</label>
                    <input type="date" id="fecha_salida" name="fecha_salida" required>
                </div>

                <div class="input_group">
                    <label for="fecha_regreso">Fecha de Regreso</label>
                    <input type="date" id="fecha_regreso" name="fecha_regreso" required>
                </div>

                <input type="hidden" name="status" id="status">
                <button class="insert_Licencias_btn">Crear Licencias</button>
            </form>
        </div>
    </div>
</div>

<script>
    const diasRestantesPorUsuario = <?= $diasRestantesJson ?>;
    const puestosEspeciales = <?= $puestosEspecialesJson ?>;
    let diasRestantes = 0;
    let diasRestantess = 0;
    let puestoActual = null;

    $(document).on("input", ".search_input", function () {
        const searchTerm = $(this).val().toLowerCase();
        $(this).closest(".options").find(".option").each(function () {
            const text = $(this).text().toLowerCase();
            if (text.includes(searchTerm)) {
                $(this).show();
            } else if (!$(this).hasClass("input_group")) {
                $(this).hide();
            }
        });
    });

    $(document).ready(function () {
        $(document).on("click", ".select_menu .select_btn", function () {
            $(this).closest(".select_menu").toggleClass("active");
        });

        $(document).on("click", ".options .option", function (e) {
            e.stopPropagation();
            const menu = $(this).closest(".select_menu");
            const selectedOption = $(this).find("h3, span").first().text() || $(this).text().trim();
            const selectedValue = $(this).data("value");
            const puestoId = $(this).data("puesto");

            menu.find(".option").removeClass("selected");
            $(this).addClass("selected");
            menu.find(".sBtn_text").text(selectedOption);
            menu.removeClass("active");

            if (menu.attr("id") === "usuario_id_menu") {
                $("#usuario_id").val(selectedValue);
                puestoActual = parseInt(puestoId);
                if (puestosEspeciales.includes(puestoActual)) {
                    $("#seis_meses_group").show();
                } else {
                    $("#seis_meses_group").hide();
                    $("#viaticos").val("No");
                    diasRestantes = diasRestantesPorUsuario[selectedValue] || 0;
                    $("#dias_restantes_info").text("Días restantes: " + diasRestantes);
                }
                diasRestantes = diasRestantesPorUsuario[selectedValue] || 0;
                $("#dias_restantes_info").text("Días restantes: " + diasRestantes);
            }

            if (menu.attr("id") === "meses_menu") {
            $("#viaticos").val(selectedValue);
            diasRestantess = selectedValue === "Si" ? 180 : diasRestantes;
            $("#dias_restantes_info").text("Días restantes: " + diasRestantess);

        
            $("#fecha_salida").val('');
            $("#fecha_regreso").val('');
        }
        });

        $('#fecha_salida').on('change', function () {
            const fechaSalida = new Date(this.value);
            if (isNaN(fechaSalida.getTime())) return;

            let diasDisponibles = diasRestantess;
            let fechaMaxima = new Date(fechaSalida);
            while (diasDisponibles > 0) {
                fechaMaxima.setDate(fechaMaxima.getDate() + 1);
                const diaSemana = fechaMaxima.getDay();
                if (diaSemana !== 0 && diaSemana !== 6) diasDisponibles--;
            }

            $('#fecha_regreso').attr('max', fechaMaxima.toISOString().split('T')[0]);
        });

        $('#fecha_regreso').on('change', function () {
            const fechaSalida = new Date($('#fecha_salida').val());
            const fechaRegreso = new Date(this.value);
            if (fechaRegreso < fechaSalida) {
                alert('La fecha de regreso no puede ser anterior a la fecha de salida.');
                this.value = '';
                return;
            }

            let diasSeleccionados = 0;
            let fechaTemp = new Date(fechaSalida);
            while (fechaTemp <= fechaRegreso) {
                const diaSemana = fechaTemp.getDay();
                if (diaSemana !== 0 && diaSemana !== 6) diasSeleccionados++;
                fechaTemp.setDate(fechaTemp.getDate() + 1);
            }

            if (diasSeleccionados > diasRestantess) {
                alert('No puedes seleccionar más días de los disponibles. Te quedan ' + diasRestantess + ' días.');
                this.value = '';
            }
        });

        // $('form').on('submit', function (e) {
        //     const usuarioInput = $('#usuario_id').val();
        //     if (!usuarioInput) {z
        //         alert('Por favor selecciona un empleado.');
        //         e.preventDefault();
        //     }
        // });
    });
</script>

<?php
    return ob_get_clean();
}
?>
