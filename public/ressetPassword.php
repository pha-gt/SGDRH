<?php

require_once '../src/config/config.php';
require_once CONTROLLER_PATH . 'UserController.php';
require_once SERVER_PATH . 'db.php';

$userID = isset($_GET['userID']) ? intval($_GET['userID']) : 0;

if ($userID > 0) {
    $db = new DB();
    $controller = new UserController($db);
    $controller->ressetPassword($userID);
    Session::set('user_success', 'Contraseña actualizada con éxito.');
    header("Location: admin_home.php?page=manage_users");
} else {
    Session::set('user_error', 'Error al resetear la contraseña.');
    echo "<script>$(location).attr('href', 'admin_home.php?page=dashboard');</script>";
}
