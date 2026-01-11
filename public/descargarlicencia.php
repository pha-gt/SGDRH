
<?php

require_once '../src/config/config.php';
require_once CONTROLLER_PATH . 'LicenciasController.php';
require_once SERVER_PATH . 'DB.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    $db = new DB();
    $Comision = new LicenciasController($db);
    $Comision->downloadDLicencias($id);
} else {
    Session::set('download_error', 'ID de documento no válido.');
}
