<?php if (!empty($user)) : ?>

    <div class="container">

        <a href="admin_home.php?page=manage_users"><i class="fa-solid fa-arrow-left"></i>Regresar</a>

        <div class="card_profile">
            <div class="card_user_avatar">
                <div class="avatar_stack">
                    <?php if ($user['usuario_genero'] === 'H') {
                        echo '<img src="assets/images/hombre.png">';
                    } else {
                        echo '<img src="assets/images/mujer.png">';
                    } ?>
                </div>
                <div class="user_details">
                    <span class="user-name"><?= $user['usuario_nombre']; ?></span>
                    <span class="user-info">Jefe inmediato: <?= $user['jefeInmediato_nombre']; ?></span>
                    <span class="user-info">Correo: <?= $user['usuario_email']; ?></span>
                    <span class="user-info">Genero: <?= ($user['usuario_genero'] == "H") ? 'Hombre' : 'Mujer' ?></span>
                </div>
            </div>
            <div class="card_user_info">
                <?php
                $fields = [
                    'Nomina' => 'usuario_nomina',
                    'CURP' => 'usuario_curp',
                    'RFC' => 'usuario_rfc',
                    'Fecha de ingreso' => 'usuario_fechaIngreso',
                    'Día de cumpleaños' => 'usuario_fechaCumpleaños',
                    'Área de adscripción' => 'areaAdscripcion_nombre',
                    'Puesto' => 'puesto_nombre',
                    'Sindicato' => 'sindicato_nombre',
                    'Días económicos' => 'dias_economicos'
                    
                ];

                foreach ($fields as $label => $field) {
                    echo '<div class="input_group">';
                    echo '<label for="' . $label . '">' . $label . '</label>';
                    echo '<input type="text" id="' . $label . '" value="' . $user[$field] . '" readonly>';
                    echo '</div>';
                }

                if ($_SESSION['user_role'] == 1) {
                    echo "<div class=\"card_user_actions\">
                            <button class=\"btn_edit\" data-id=" . $user['usuario_id'] . " onclick=\"openModal('editUser')\">Modificar</button>
                          </div>";
                }

                ?>

            </div>
        </div>

    </div>

    <?php if ($_SESSION['user_role'] == 1) :

        echo generateModalEditUser($user['usuario_id']);

    ?>

        <script src="assets/js/modal.js"></script>

    <?php endif; ?>

<?php else : ?>

    <?= "Usuario no encontrado"; ?>

<?php endif; ?>

<?php

if (Session::exists('profile_success')) {
    echo showAlert('success', Session::get('profile_success'));
    echo "<script>hideAlert('success');</script>";
    Session::delete('profile_success');
}
if (Session::exists('profile_error')) {
    echo showAlert('error', Session::get('profile_error'));
    echo "<script>hideAlert('error');</script>";
    Session::delete('profile_error');
}

?>