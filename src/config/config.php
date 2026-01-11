<?php

// Default timezone
date_default_timezone_set('America/Mexico_City');

// Base route of the project
define('BASE_PATH', realpath(dirname(__FILE__) . '/../../'));

// Paths to home directories
define('CONTROLLER_PATH', BASE_PATH . '/src/controllers/');
define('EMAIL_PATH', BASE_PATH . '/email/');
define('MODEL_PATH', BASE_PATH . '/src/models/');
define('PDF_PATH', BASE_PATH . '/pdf/');
//define('PDF_PATH', BASE_PATH . '../pdf/');
define('SERVER_PATH', BASE_PATH . '/server/');
define('UTIL_PATH', BASE_PATH . '/src/utils/');
define('VIEW_PATH', BASE_PATH . '/src/views/');
define('VIEW_LIST', BASE_PATH . '/src/views/document/');


require_once UTIL_PATH . 'Modal.php';
require_once UTIL_PATH . 'ModalAddDocumento.php';
require_once UTIL_PATH . 'ModalEditCommissions.php';
require_once UTIL_PATH . 'ModalDeleteCommissions.php';
require_once UTIL_PATH . 'ModalAddComision.php';
require_once UTIL_PATH . 'ModalEditDocumento.php';
//require_once UTIL_PATH . 'ModalAddlicencias.php';
//require_once UTIL_PATH . 'ModalEditlicencias.php';
//require_once UTIL_PATH . 'ModalDeleteLicencias.php';
require_once UTIL_PATH . 'ModalAddUser.php';
require_once UTIL_PATH . 'ModalEditUser.php';
require_once UTIL_PATH . 'Alert.php';
require_once UTIL_PATH . 'Modaldiasexcel.php';


//require_once UTIL_PATH . 'ModalAddTimeByTime.php';
//require_once UTIL_PATH . 'ModalEditTimeByTime.php';
//require_once UTIL_PATH . 'ModalUploadFileTimeByTime.php';
//require_once UTIL_PATH . 'ModalDeleteTimeByTime.php';
define('APP_NAME', 'SGDRH');
