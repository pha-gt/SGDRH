<script src="assets/js/alert.js"></script>
<script src="assets/js/modal.js"></script>
<script src="assets/js/password.js"></script>

<script>
    function updatePassword() {
        openModal('updatePassword');
        var modal = $(".updatePassword");
        modal.find(".modal_body").html(`<form action="admin_home.php?page=my_profile&action=update_password" method="POST">
                                        <div class="input_group password">
                                            <label for="new-password">Nueva contraseña</label>
                                            <input type="password" name="new_password" id="new-password" placeholder="Ingresa la nueva contraseña">
                                            <i class="fa-solid fa-eye togglePassword"></i>
                                        </div>
                                        <button type="submit">Actualizar contraseña</button>
                                        </form>`);
    }

    function updateAvatar() {
        openModal('updateAvatar');
        var modal = $(".updateAvatar");
        modal.find(".modal_body").html(`<form action="admin_home.php?page=my_profile&action=update_avatar" method="POST" enctype="multipart/form-data">
                                            <div class="input_group">
                                                <label for="avatar">Nuevo Avatar</label>
                                                <input type="file" id="avatar" name="avatar" accept="image/*" required>
                                            </div>
                                            <button type="submit">Actualizar foto de perfil</button>
                                        </form>`);
    }

    $(document).ready(function() {
        $('.modal_body').on('click', '.togglePassword', function() {
            const $passwordInput = $(this).prev('input');
            const type = $passwordInput.attr('type') === 'password' ? 'text' : 'password';
            $passwordInput.attr('type', type);
            $(this).toggleClass('fa-eye fa-eye-slash');
        });

        // Validación de contraseña segura al enviar el formulario
        $(document).on('submit', 'form[action*="update_password"]', function(e) {
            const password = $('#new-password').val();
            // Al menos 8 caracteres, una mayúscula, una minúscula, un número y un carácter especial
            const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
            if (!regex.test(password)) {
                e.preventDefault();
                alert('La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula, un número y un carácter especial.');
                return false;
            }
        });
    });
</script>

<?php if (!empty($user)) : ?>
    <div class="card_profile">
        <div class="card_user_avatar">
            <div class="avatar_stack">
                <?php if ($_SESSION['user_genre'] === 'H') {
                    echo '<img class="avatar" src="assets/images/hombre.png">';
                } else {
                    echo '<img class="avatar" src="assets/images/mujer.png">';
                } ?>
            </div>
            <div class="user_details">
                <span class="user-name"><?= $user['usuario_nombre'] ?></span>
                <span class="user-info">Jefe inmediato: <?= $user['jefeInmediato_nombre'] ?></span>
                <span class="user-info">Correo: <?= $user['usuario_email'] ?></span>
            </div>
            <button class="btn_edit" onclick="updatePassword()">Actualizar contraseña</button>
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
                'Sindicato' => 'sindicato_nombre'
            ];

            foreach ($fields as $label => $field) {
                echo '<div class="input-group">';
                echo '<label for="' . $field . '">' . $label . '</label>';
                echo '<input type="text" id=' . $field . ' value="' . $user[$field] . '" readonly>';
                echo '</div>';
            }
            ?>
        </div>
    </div>
<?php endif; ?>

<?php

if (Session::exists('password_success')) {
    echo showAlert('success', Session::get('password_success'));
    echo "<script>hideAlert('success');</script>";
    Session::delete('password_success');
}
if (Session::exists('password_error')) {
    echo showAlert('error', Session::get('password_error'));
    echo "<script>hideAlert('error');</script>";
    Session::delete('password_error');
}

echo generateModal('updateAvatar', 'Actualizar foto de perfil', true);
echo generateModal('updatePassword', 'Actualizar contraseña', true);

?>