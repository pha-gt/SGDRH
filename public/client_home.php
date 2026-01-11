<?php

require_once '../src/config/config.php';
require_once CONTROLLER_PATH . 'DocumentController.php';
require_once CONTROLLER_PATH . 'UserController.php';
require_once SERVER_PATH . 'DB.php';
require_once UTIL_PATH . 'Session.php';

// Verify if session is active
Session::start();
if (!Session::isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$action = isset($_GET['action']) ? $_GET['action'] : '';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include VIEW_PATH . 'content/include/header.php'; ?>
</head>

<body>


    <?php include VIEW_PATH . 'content/template/sidebar.php'; ?>

    <div class="container_main">

        <?php include VIEW_PATH . 'content/template/navbar.php'; ?>

        <div class="content">

            <?php

            $db = new DB();
            $userID = Session::get('user_id');
            $role = Session::get('user_role');

            switch ($page) {
                case 'dashboard':
                    $documentController = new DocumentController($db);
                    if ($action === 'addDiaEconomico' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                        $permiso = $_POST['permiso'];
                        $startDate = $_POST['start-date'];
                        $endDate = $_POST['end-date'];
                        $documentController->generateDiaEconomico($db, $userID, $startDate, $endDate, $permiso);
                    } else if ($action === 'addDiaCumple' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                        $documentController->generateDiaCumple($db, $userID);
                    } else if ($action === 'addReporteIncidencia' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                        $date = $_POST['fecha'];
                        $incidencia = $_POST['incidencia'];
                        $motivo = $_POST['motivo'];
                        $documentController->generateReporteIncidencia($db, $userID, $incidencia, $date);
                    } else {
                        $documentController->showAllDocuments($role, $userID);
                    }
                    break;
                case 'my_profile':
                    $userController = new UserController($db);
                    if ($action === 'update_password' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                        $newPassword = $_POST['new_password'];
                        $userController->updatePassword($userID, $newPassword);
                    } else {
                        $userController->showProfile($userID);
                    }
                    break;
                default:
                    include VIEW_PATH . 'content/404.php';
                    break;
            }
            ?>
        </div>
    </div>

</body>

</html>