
<?php

require_once '../src/config/config.php';
require_once CONTROLLER_PATH . 'CommissionController.php';
require_once SERVER_PATH . 'DB.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    $db = new DB();
    $Comision = new CommissionController($db);
    $Comision->downloadDCommission($id);
} else {
    Session::set('download_error', 'ID de documento no válido.');
}
