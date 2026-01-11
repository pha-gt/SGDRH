<?php

require_once MODEL_PATH . 'DocumentModel.php';
require_once MODEL_PATH . 'LicenciasModel.php';
require_once MODEL_PATH . 'UserModel.php';
require_once EMAIL_PATH . 'Exception.php';
require_once EMAIL_PATH . 'PHPMailer.php';
//require_once EMAIL_PATH . 'SMTP.PHP';
require_once PDF_PATH . 'library/fpdf.php';
require_once UTIL_PATH . 'Session.php';


use FontLib\Table\Type\head;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class LicenciasController
{
    private $LicenciasModel;
    private $userModel;
    public  $table_name = 'licencias';

    public function __construct($db)
    {
        $this->LicenciasModel = new LicenciasModel($db);
        $this->userModel = new UserModel($db); 
    }


    public function showLicencias($role, $userID)
    {
        $documents = $this->LicenciasModel->getAllLicencias($role, $userID, $this->table_name);
        $user = $this->userModel->getUserById($userID);
        require VIEW_PATH . 'document/licencias_list.php';

    }

    public function addLicencias($data) {
        $user_ID = isset($data["usuario_id"]) ? intval($data["usuario_id"]) : null;

        if (empty($user_ID) || !is_int($user_ID)) {
            Session::set('document_warning', "Error: el campo usuario es obligatorio");
            echo "<script>$(location).attr('href', 'admin_home.php?page=licencias');</script>";
            exit;
        }

        if ($this->LicenciasModel->addLicencias($data, $this->table_name)) {
            Session::set('document_success', 'Licencia registrada correctamente.');
        } else {
            Session::set('document_warning', 'No se pudo registrar la Licencia.');
        }

        echo "<script>$(location).attr('href', 'admin_home.php?page=licencias');</script>";
    }

    public function updateLicencias($data) {
        if ($this->LicenciasModel->updateLicencias($data, $this->table_name)) {
            Session::set('document_success', 'Licencia registrada correctamente.');
        } else {
            Session::set('document_warning', 'No se pudo registrar la Licencia.');
        }
        echo "<script>$(location).attr('href', 'admin_home.php?page=licencias');</script>";
    }

    public function describeTable($name)
    {
        return $this->LicenciasModel->describeTable($name);

    }

    public function downloadDLicencias($id)
    {
        $Commision = $this->LicenciasModel->getLicenciasById($id);

        if ($Commision && isset($Commision['pdf'])) {
            $pdfContent = $Commision['pdf']; 
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="documento.pdf"');
            header('Content-Length: ' . strlen($pdfContent));
            echo $pdfContent;
            exit;
        } else {
            echo '<h1>Error</h1>';
            echo '<p>No se encontró la Licencia solicitada o el archivo PDF.</p>';
            exit;
        }
    }
}