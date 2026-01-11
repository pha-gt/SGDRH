<?php

require_once '../src/config/config.php';
require_once CONTROLLER_PATH . 'DocumentController.php';
//require_once CONTROLLER_PATH . 'TimeBytimeController.php';
require_once SERVER_PATH . 'DB.php';

$docID = isset($_GET['docID']) ? intval($_GET['docID']) : 0;
$docID_timebytime = isset($_GET['docID_timebytime']) && !empty($_GET['docID_timebytime']) ? intval($_GET['docID_timebytime']) : 0;

if ($docID > 0) {
    $db = new DB();
    $controller = new DocumentController($db);
    $controller->downloadDocument($docID);
}elseif ($docID_timebytime > 0) {
    $db = new DB();
    $TimeByTimeController = new TimeBytimeController($db);
    $archivo = $TimeByTimeController->downloadFile($docID_timebytime);
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . $archivo['folio'] . '.pdf"');
    header('Content-Length: ' . strlen($archivo['archivo']));
    echo $archivo['archivo'];
}
 else {
    Session::set('download_error', 'ID de documento no válido.');
} 
