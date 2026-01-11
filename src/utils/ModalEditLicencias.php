<?php

require_once MODEL_PATH . "LicenciasModel.php";
require_once SERVER_PATH . "DB.php";

function generateModalEditLicencias($id)
{
    $db = new DB();
    $LicenciasModel = new LicenciasModel($db);
    $Licencias = $LicenciasModel->getLicenciasById($id);

    $modal = "
    <div class=\"modal editlicencias{$id}\"> 
        <div class=\"modal_content\">
            <div class=\"modal_header\">
                <h2>Actualizar licencia</h2>
                <button onclick=\"closeModal('editlicencias{$id}')\">Cerrar</button> 
            </div>
            <div class=\"modal_body\">
                <form action=\"admin_home.php?page=licencias&action=editlicencias\" method=\"POST\" enctype=\"multipart/form-data\">
                

                    <input type=\"hidden\" name=\"id\" value=\"{$Licencias['id']}\">
                    <div class=\"input_group\">
                        <label>Adjuntar documento</label>
                        <input type=\"file\" name=\"pdf\" accept=\"application/pdf\"required>
                    </div>
                    <button type=\"submit\">Actualizar licencia</button>
                </form>
            </div>
        </div>
    </div>
    ";

    return $modal;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'editlicencias') {
    $id = $_POST['id'];
    if (isset($_FILES['pdf']) && $_FILES['pdf']['error'] === UPLOAD_ERR_OK) {
        $pdfContent = file_get_contents($_FILES['pdf']['tmp_name']);
        $db = new DB();
        $LicenciasModel = new LicenciasModel($db);
        $LicenciasModel->updateLicenciasPdf($id, $pdfContent);
        
    }
}