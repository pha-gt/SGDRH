<?php 
function generateModalDeleteTimeByTime($id,$folio,$usuario_nombre) {
    return "
    <div class=\"modal timebytimeDeleteFile{$id}\">
        <div class=\"modal_content\">
            <div class=\"modal_header\">
                <h2>Eliminar archivo</h2>
                <button onclick=\"closeModal('timebytimeDeleteFile{$id}')\">Cerrar</button>
            </div>
            <div class=\"modal_body\">
                <h3>¿Estás seguro de que deseas eliminar el archivo de $usuario_nombre Folio: $folio ?</h3>
                <form action=\"admin_home.php?page=TimeByTime&action=timebytimeDeleteFile\" method=\"POST\" id=\"deleteForm{$id}\">
                    <input type=\"hidden\" name=\"id\" value=\"$id\" readonly>
                    <button type=\"button\" onclick=\"deleteAlert('deleteForm{$id}', '{$usuario_nombre}', '{$folio}')\">Eliminar</button>
                </form>
                <button onclick=\"closeModal('timebytimeDeleteFile{$id}')\">Cancelar</button>
            </div>
        </div>
    </div>";

}
?>
