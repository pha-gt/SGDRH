<?php

require_once MODEL_PATH . 'DocumentModel.php';
require_once MODEL_PATH . 'CommissionsModel.php';
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

class CommissionController
{
    private $CommissionsModel;
    public  $table_name = 'comisiones';

    public function __construct($db)
    {
        $this->CommissionsModel = new CommissionsModel($db);
    }


    public function showCommission($role, $userID)
    {
        $documents = $this->CommissionsModel->getAllCommissions($role, $userID, $this->table_name);
        require VIEW_PATH . 'document/commission_list.php';

    }

    public function addComision($data) {
        $user_ID = isset($data["usuario_id"]) ? intval($data["usuario_id"]) : null;

        if (empty($user_ID) || !is_int($user_ID)) {
            Session::set('document_warning', "Error: el campo usuario es obligatorio");
            echo "<script>$(location).attr('href', 'admin_home.php?page=commissions');</script>";
            exit;
        }

        if ($this->CommissionsModel->addComision($data, $this->table_name)) {
            Session::set('document_success', 'Comisión registrada correctamente.');
        } else {
            Session::set('document_warning', 'No se pudo registrar la comisión.');
        }
        echo "<script>$(location).attr('href', 'admin_home.php?page=commissions');</script>";
    }

    public function updateCommission($data) {
        if ($this->CommissionsModel->updateComision($data, $this->table_name)) {
            Session::set('document_success', 'Comisión registrada correctamente.');
        } else {
            Session::set('document_warning', 'No se pudo registrar la comisión.');
        }
        echo "<script>$(location).attr('href', 'admin_home.php?page=commissions');</script>";
    }

    public function describeTable($name)
    {
        return $this->CommissionsModel->describeTable($name);

    }

    public function downloadDCommission($id)
    {
        $Commision = $this->CommissionsModel->getCommissionsById($id);

        if ($Commision && isset($Commision['pdf'])) {
            $pdfContent = $Commision['pdf']; 
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="documento.pdf"');
            header('Content-Length: ' . strlen($pdfContent));
            echo $pdfContent;
            exit;
        } else {
            echo '<h1>Error</h1>';
            echo '<p>No se encontró la comisión solicitada o el archivo PDF.</p>';
            exit;
        }
    }
}