<?php

function generateModalDeleteCommissions($licenciaId)
{
    return "
    <div class=\"modal ModalDeleteCommissions{$licenciaId}\"> 
        <div class=\"modal_content\">
            <div class=\"modal_header\">
                <h2>Eliminar Comision</h2>
                <button onclick=\"closeModal('ModalDeleteCommissions{$licenciaId}')\">Cerrar</button>
            </div>
            <div class=\"modal_body\">
                <p>¿Estás seguro de que deseas eliminar esta comision?</p>
                <form action=\"admin_home.php?page=commissions&action=deleteCommissions\" method=\"POST\">
                    <input type=\"hidden\" name=\"id\" value=\"{$licenciaId}\">
                    <button type=\"submit\" class=\"btn_delete\">Eliminar</button>
                    <button type=\"button\" onclick=\"closeModal('ModalDeleteCommissions{$licenciaId}')\">Cancelar</button>
                </form>
            </div>
        </div>
    </div>
    ";
}
?>