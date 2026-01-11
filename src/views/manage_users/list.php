<?php 

if (!empty($users)) : 
    $users = array_reverse($users); // <-- Invierte el orden
?>

    <div class="card_table">
        <div class="card_table_header">
            <h2>Administrar empleados</h2>
            <?php if ($_SESSION['user_role'] == 1) : ?> 
                <div id="btn_diecomasivo" onclick="openModal('exceldiaseco')" title="Cargar días economicos masivo">CSV</div>
                <div class="btn_insert" onclick="openModal('addUser')">Agregar empleado</div>
            <?php endif; ?>
        </div>
        <div class="card_table_body">
            <div class="search_input" id="searchForm">
                <input type="text" id="searchInput" placeholder="Buscar Usuario por nombre - correo - puesto - nomina - estatus">
                <i class="fa-solid fa-xmark" id="clear_input"></i>
            </div>
            <div class="table_header">
                <span class="header_empleado">Empleado</span>
                <span class="header_rol">Rol</span>
                <span class="header_puesto">Puesto</span>
                <span class="header_nomina">Nomina</span>
                <span class="header_estatus">Estatus</span>
                <span class="header_actions">Acciones</span>
            </div>

        </div>
        <div class="table_body" id="tableContainer">
            <?php foreach ($users as $user) : ?>
                <?php if ($user['usuario_id'] != $_SESSION['user_id']) { ?>
                    <div class="table_body_item">
                        <div class="row_user_info">
                            <?php if ($user['usuario_genero'] === 'H') {
                                echo '<img src="assets/images/hombre.png">';
                            } else {
                                echo '<img src="assets/images/mujer.png">';
                            } ?>
                            <div class="info">
                                <span class="user_name"><?= $user["usuario_nombre"]; ?></span>
                                <span class="user_email"><?= $user["usuario_email"] ?></span>
                                <span><?= $user["jefeInmediato_nombre"] ?></span>
                            </div>
                        </div>
                        <span class="row_rol"><?= $user["rol_nombre"] ?></span>
                        <span class="row_puesto"><?= $user["puesto_nombre"] ?></span>
                        <span class="row_nomina"><?= $user["usuario_nomina"] ?></span>
                        <?= $estatusClass = '';
                        switch ($user['usuario_estatus']) {
                            case "Vigente":
                                $estatusClass = 'current';
                                break;
                            case "Baja":
                                $estatusClass = 'not_current';
                                break;
                        }
                        echo "<span class=\"row_estatus {$estatusClass}\">{$user['usuario_estatus']}</span>"; ?>
                        <div class="row_actions">
                            <a href="admin_home.php?page=see_user&action=seeUser&userID=<?= $user["usuario_id"]; ?>" title="Ver a <?= $user["usuario_nombre"]; ?>"><i class="fa-solid fa-eye"></i></a>
                            <?= ($_SESSION['user_role'] == 1) ? '<a href="ressetPassword.php?userID=' . $user["usuario_id"] . '" title="Resetear la contraseña de ' . $user["usuario_nombre"] . '"><i class="fa-solid fa-lock-open"></i></a>' : ''; ?>
                        </div>
                    </div>

                <?php } ?>
            <?php endforeach; ?>
        </div>
        <div class="no_result_message" id="noResultsMessage" style="display: none;">
            <span>No se encontraron coincidencias.</span>
            <i class="fa-solid fa-magnifying-glass"></i>
        </div>
    </div>

    <?php if ($_SESSION['user_role'] == 1) :
        echo generateModalAddUser();
        echo generateModalexceldiaseco();
    endif; ?>
    

<?php else : ?>

    <div class="card_table">
        <div class="card_table_header">
            <h2>Administrar empleados</h2>
            <?php if ($_SESSION['user_role'] == 1) : ?>
                <div class="btn_insert">Agregar empleado</div>
                <div class="btn_insert"></div>
            <?php endif; ?>
        </div>
        <div class="card_table_body">
            <div class="card_table_message">
                <div class="no_result_message">
                    <span>Aun no hay empleados por mostrar</span>
                    <i class="fa-regular fa-folder-open"></i>
                </div>
            </div>
        </div>
    </div>

<?php endif; ?>

<script src="assets/js/alert.js"></script>
<script src="assets/js/modal.js"></script>
<script src="assets/js/search_user.js"></script>

<?php

if (Session::exists('user_success')) {
    echo showAlert('success', Session::get('user_success'));
    echo "<script>hideAlert('success');</script>";
    Session::delete('user_success');
}
if (Session::exists('user_error')) {
    echo showAlert('error', Session::get('user_error'));
    echo "<script>hideAlert('error');</script>";
    Session::delete('user_error');
}

?>