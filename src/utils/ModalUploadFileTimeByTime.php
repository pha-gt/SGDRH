<?php 

function generateModalUploadFile($docID, $folio, $userName) {
    $modal = "
    <div class=\"modal timebytimeUploadFile{$docID}\">
        <div class=\"modal_content\">
            <div class=\"modal_header\">
                <h2>Subir documento</h2>
                <button onclick=\"closeModal('timebytimeUploadFile{$docID}')\">Cerrar</button>
            </div>
            <strong>Registro: $userName Folio: $folio</strong>
            <div class=\"modal_body\">
                <form action=\"admin_home.php?page=TimeByTime&action=timebytimeUploadFile\" method=\"POST\" enctype=\"multipart/form-data\">
                    <h3 for=\"document\">Seleccionar documento:</h3>
                    <div class=\"input_group\">
                    <input type=\"file\" name=\"archivo\" id=\"document\" requiered>
                    </div>
                    <input type=\"hidden\" name=\"docID\" value=\"{$docID}\">
                    <button type=\"submit\">Subir documento</button>
                </form>
            </div>
        </div>
    </div>";
    
    return $modal;
}

?>