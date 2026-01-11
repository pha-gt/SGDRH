<?php

require_once '../src/config/config.php';
require_once CONTROLLER_PATH . 'RolesController.php';
require_once SERVER_PATH . 'db.php';

$rolId = isset($_GET['rolId']) ? intval($_GET['rolId']) : 0;

if ($rolId > 0) {
    $db = new DB();
    $controller = new RolesController($db);
    $controller->deleteRol($rolId);
} else {
    Session::set('document_error', 'Documento no válido.');
}
