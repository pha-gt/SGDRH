<?php

require_once 'src/config/config.php';
require_once 'src/utils/Session.php';

// Start session
Session::start();

// Verify if user is logged in
if (Session::isLoggedIn()) {
    $role = Session::get('user_role');
    if ($role == 3) {
        header('Location: public/client_home.php');
    } else {
        header('Location: public/admin_home.php');
    }
    exit;
} else {
    header('Location: public/login.php');
    exit;
}
