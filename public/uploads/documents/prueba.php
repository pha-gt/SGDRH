<?php

require_once MODEL_PATH . "DocumentModel.php";
require_once SERVER_PATH . "DB.php";




function generateModalEditDocument($docID)
{
    
    $db = new DB();
    $documentModel = new DocumentModel($db);
    $document = $documentModel->getDocumentById($docID);

    $modal = "
    <div class=\"modal editDocument{$docID}\">
        <div class=\"modal_content\">
            <div class=\"modal_header\">
                <h2>Actualizar documento</h2>
                <button onclick=\"closeModal('editDocument')\">Cerrar</button>
            </div>
            <div class=\"modal_body\">
                <form action=\"admin_home.php?page=dashboard&action=editDocument\" method=\"POST\">
                <div class=\"input_group\">
                <label>Adjuntar documento</label>
                    <input type=\"file\" name=\"documento\">
                </div>
                
                    <input type=\"hidden\" name=\"docID\" value=\"{$document['documento_id']}\">
                    <p>ID del documento: {$document['documento_id']}</p>
                    <p>ID que llega de parametro: {$docID}</p>


                    <div class=\"input_group\">
                        <label>Estatus</label>
                        <div class=\"select_menu\" id=\"updateStatus\">
                            <div class=\"select_btn\">
                                    <span class=\"sBtn_text\">" . $document['documento_estatus'] . "</span>
                                <i class=\"fa-solid fa-chevron-down\"></i>
                            </div>
                            
                            <ul class=\"options\">
                                <li class=\"option\" data-value=\"Entregado\">
                                    <span>Entregado</span>
                                </li>
                                <li class=\"option\" data-value=\"Pendiente\">
                                    <span>Pendiente</span>
                                </li>
                                <li class=\"option\" data-value=\"Sin Entregar\">
                                    <span>Sin Entregar</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <input type=\"hidden\" name=\"documentoEstatus\" id=\"estatus\" value=\"{$document['documento_estatus']}\">

                    <button type=\"submit\">Actualizar documento</button>
                </form>
            </div>
        </div>
    </div>
";

    return $modal;
   

}