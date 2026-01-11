<?php
require_once MODEL_PATH . "CommissionsModel.php";
require_once SERVER_PATH . "DB.php";

function generateModalEditComision($id)
{
    $db = new DB();
    $CommissionsModel = new CommissionsModel($db);
    $Commissions = $CommissionsModel->getCommissionsById($id);

    $modal = "
    <div class=\"modal editCommissions{$id}\"> 
        <div class=\"modal_content\">
            <div class=\"modal_header\">
                <h2>Actualizar comision</h2>
                <button onclick=\"closeModal('editCommissions{$id}')\">Cerrar</button> 
            </div>
            <div class=\"modal_body\">
                <form action=\"admin_home.php?page=commissions&action=editCommissions\" method=\"POST\" enctype=\"multipart/form-data\">
                    <input type=\"hidden\" name=\"id\" value=\"{$Commissions['id']}\">
                    <div class=\"input_group\">
                        <label>Adjuntar documento</label>
                        <input type=\"file\" name=\"pdf\" accept=\"application/pdf\"required>
                    </div>
                    <button type=\"submit\">Actualizar Comision</button>
                </form>
            </div>
        </div>
    </div>
    ";

    return $modal;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'editCommissions') {
    $id = $_POST['id'];
    if (isset($_FILES['pdf']) && $_FILES['pdf']['error'] === UPLOAD_ERR_OK) {
        $pdfContent = file_get_contents($_FILES['pdf']['tmp_name']);
        $db = new DB();
        $CommissionsModel = new CommissionsModel($db);
        $CommissionsModel->updateCommissionPdf($id, $pdfContent);
    }
}
