<?php

require_once MODEL_PATH . "UserModel.php";
require_once SERVER_PATH . "DB.php";

function generateModalRol()
{
    $modal = "
    <div class=\"modal documento\">
        <div class=\"modal_content\">
            <div class=\"modal_header\">
                <h2>Agregar Rol</h2>
                <button onclick=\"closeModal('documento')\">Cerrar</button>
            </div>
            <div class=\"modal_body\">
                <form action=\"admin_home.php?page=dashboard&action=addRol\" method=\"POST\" enctype=\"multipart/form-data\">
                    <div class=\"input_group\">
                        <label for=\"rol_nombre\">Nombre del Rol</label>
                        <input type=\"text\" id=\"rol_nombre\" name=\"rol_nombre\" required>
                    </div>
                    <button type=\"submit\" class=\"insert_documento_btn\">Guardar Rol</button>
                </form>
            </div>
        </div>
    </div>";

    return $modal;
}
