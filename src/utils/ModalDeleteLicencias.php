<?php

function generateModalDeleteLicencias($licenciaId)
{
    return "
    <div class=\"modal ModalDeleteLicencias{$licenciaId}\"> 
        <div class=\"modal_content\">
            <div class=\"modal_header\">
                <h2>Eliminar Licencia</h2>
                <button onclick=\"closeModal('ModalDeleteLicencias{$licenciaId}')\">Cerrar</button>
            </div>
            <div class=\"modal_body\">
                <p>¿Estás seguro de que deseas eliminar esta licencia?</p>
                <form action=\"admin_home.php?page=licencias&action=deleteLicencia\" method=\"POST\">
                    <input type=\"hidden\" name=\"id\" value=\"{$licenciaId}\">
                    <button type=\"submit\" class=\"btn_delete\">Eliminar</button>
                    <button type=\"button\" onclick=\"closeModal('ModalDeleteLicencias{$licenciaId}')\">Cancelar</button>
                </form>
            </div>
        </div>
    </div>
    ";
}
?>