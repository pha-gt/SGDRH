<?php

require_once '../src/config/config.php';
require_once CONTROLLER_PATH . 'DocumentController.php';
require_once SERVER_PATH . 'db.php';

$docID = isset($_GET['docID']) ? intval($_GET['docID']) : 0;

if ($docID > 0) {
    $db = new DB();
    $controller = new DocumentController($db);
    $controller->deleteDocument($db, $docID);
} else {
    Session::set('document_error', 'Documento no válido.');
}
