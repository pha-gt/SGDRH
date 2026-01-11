<?php

require_once "../src/config/config.php";
require_once CONTROLLER_PATH . "LoginController.php";

$controller = new LoginController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->login();
} else {
    $controller->showLoginForm();
}
