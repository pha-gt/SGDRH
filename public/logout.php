<?php

require_once '../src/config/config.php';
require_once CONTROLLER_PATH . 'LoginController.php';

$controller = new LoginController();
$controller->logout();
