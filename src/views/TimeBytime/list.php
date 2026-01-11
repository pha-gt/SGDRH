<?php if (!empty($registros)) : ?>

    <div class="card_table">
        <div class="card_table_header">
            <h2><?php echo ($_SESSION['user_role'] == 3) ? "Mis registros" : "Tiempo x Tiempo"; ?></h2>
            <div class="card_header_actions">
                <?php if ($_SESSION['user_role'] == 1 || $_SESSION['user_role'] == 2 || $_SESSION['user_role'] == 4) : ?>
                    <button class="btn_documento" onclick="openModal('timebytime')">Generar Registro</button>
                <?php endif; ?>
                <button class="btn_filter" data-filter="pendiente">Pendientes</button>
                <button class="btn_filter" data-filter="entregado">Entregados</button>
                <button class="btn_filter" data-filter="incidencias">Incidencias</button>
            </div>  
        </div>
        <div class="card_table_body">
            <div class="search_input" id="searchForm">
                <input type="text" id="searchInput" placeholder="<?php echo ($_SESSION['user_role'] == 3) ? "Buscar por Folio o Fecha de registro" : "Buscar por nombre de empleado - Folio - Fecha de Registro" ?>">
                <i class="fa-solid fa-xmark" id="clear_input"></i>
            </div>
            <div class="table_header">
                <span class="header_pdf"></span>
                <?php if ($_SESSION['user_role'] != 3) : ?>
                    <span class="header_empleado">Empleado</span>
                <?php endif; ?>
                <span class="header_folio">Folio</span>
                <span class="header_fecha">Fecha de registro</span>
                <span class="header_estatus">Estatus</span>
                <?php if ($_SESSION['user_role'] == 1) : ?>
                    <span class="header_actions">Acciones</span>
                <?php endif; ?>
            </div>
            <div class="table_body" id="tableContainer">
                <?php foreach ($registros as $registro) : ?>
                    <?php if ($registro['estatus'] === 'cancelado') continue; ?>
                    <?php $rowClass = ($registro['incidencia'] > 0) ? 'row_pendiente' : ''; ?>
                    <div class="table_body_item <?php echo $rowClass;?>">
                        <span class="row_pdf">
                            <?php if ($registro['estatus'] === 'pendiente') : ?>
                                <a href="admin_home.php?registro_id=<?php echo $registro['id']; ?>&action=timebytimeGenerarPdf&page=TimeByTime" target="_blank" title="Generar PDF de <?= $registro["usuario_nombre"];?> Folio: <?= $registro["folio"]; ?>"><i class="fa-solid fa-file-pdf"></i></a>
                            <?php elseif ($registro['estatus'] === 'entregado') : ?>
                                <a href="download.php?docID_timebytime=<?php echo $registro['id']; ?>?>" target="_blank" title="Descargar PDF de <?= $registro["usuario_nombre"];?> Folio: <?= $registro["folio"]; ?>"><i class="fa-solid fa-file-arrow-down"></i></a>
                            <?php endif; ?>
                        </span>
                        <?php if ($_SESSION['user_role'] != 3) : ?>
                            <div class="row_user_info">
                                <?php if ($registro['usuario_genero'] === 'H') {
                                    echo '<img src="assets/images/hombre.png">';
                                } else {
                                    echo '<img src="assets/images/mujer.png">';
                                } ?>
                                <div class="info">
                                    <span class="user_name"><?php echo $registro["usuario_nombre"]; ?></span>
                                    <span><?php echo $registro["usuario_email"] ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                        <span class="row_folio"><?php echo $registro["folio"] ?></span>
                        <span class="row_fecha"><?php echo $registro["fechaR"] ?></span>
                        <?php 
                            $estatusClass = '';
                            switch ($registro['estatus']) {
                                case "entregado":
                                    $estatusClass = 'success';
                                    break;
                                case "pendiente":
                                    $estatusClass = 'warning';
                                    break;
                                case "cancelado":
                                    $estatusClass = 'danger';
                                    break;
                            }
                            echo "<span class=\"row_estatus {$estatusClass}\">{$registro['estatus']}</span>"; 
                        ?>
                        <?php if ($_SESSION['user_role'] == 1) : ?>
                            <div class="row_actions">
                                <i class="fa-solid fa-pen-to-square" title="Modificar de <?= $registro["usuario_nombre"]; ?>" data-id="<?php echo $registro['id']; ?>" onclick="openModal('timebytimeEdit<?php echo $registro['id']; ?>')"></i>
                                <?php echo generateModalEditTimeByTime($registro["id"], $registro["folio"], $registro["estatus"], $registro["usuario_id"]);?>  
                                <i class="fa-solid fa-upload" title="Subir archivo de <?= $registro["usuario_nombre"]; ?>" onclick="openModal('timebytimeUploadFile<?php echo $registro['id']; ?>')"></i>
                                <?php echo generateModalUploadFile($registro['id'], $registro['folio'], $registro['usuario_nombre']); ?>
                                <i class="fa-solid fa-trash-can" title="Eliminar archivo de <?= $registro["usuario_nombre"]; ?>" onclick="openModal('timebytimeDeleteFile<?php echo $registro['id']; ?>')"></i>
                                <?php echo generateModalDeleteTimeByTime($registro['id'], $registro['folio'], $registro['usuario_nombre']); ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($_SESSION['user_role'] == 4) : ?>
                            <div class="row_actions">
                                <i class="fa-solid fa-pen-to-square" title="Modificar de <?= $registro["usuario_nombre"]; ?>" data-id="<?php echo $registro['id']; ?>" onclick="openModal('timebytimeEdit<?php echo $registro['id']; ?>')"></i>
                                <?php echo generateModalEditTimeByTime($registro["id"], $registro["folio"], $registro["estatus"], $registro["usuario_id"]);?>  
                            </div>
                        <?php endif; ?>
                    </div>
                    
                <?php endforeach; ?>
            </div>
            <div class="no_result_message" id="noResultsMessage" style="display: none;">
                <span>No se encontraron coincidencias.</span>
                <i class="fa-solid fa-magnifying-glass"></i>
            </div>
        </div>
    </div>

<?php else : ?>
<div class="card_table">
    <div class="card_table_header">
        <h2><?php echo ($_SESSION['user_role'] == 3) ? "Mis registros" : "Tiempo x Tiempo"; ?></h2>
        <div class="card_header_actions">
            <?php if ($_SESSION['user_role'] == 1 || $_SESSION['user_role'] == 2 || $_SESSION['user_role'] == 4) : ?>
                <button class="btn_documento" onclick="openModal('timebytime')">Generar Registro</button>
            <?php endif; ?>
        </div>
    </div>
    <div class="card_table_body">
        <div class="card_table_message">
            <div class="no_result_message">
                <span>Aun no hay Tiempo por Tiempo por mostrar</span>
                <i class="fa-regular fa-folder-open"></i>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
    const role = <?php echo $_SESSION['user_role']; ?>;
</script>
<script src="assets/js/filtrosTimeByTime.js"></script>
<script src="assets/js/alert.js"></script>
<script src="assets/js/modal.js"></script>

<?php
//generar modal para subir documentos
if ($_SESSION['user_role'] == 1 || $_SESSION['user_role'] == 2 || $_SESSION['user_role'] == 4) {
    echo generateModalDocumentForTime();
}

if (Session::exists('document_success')) {
echo showAlert('success', Session::get('document_success'));
echo "<script>hideAlert('success');</script>";
Session::delete('document_success');
}

if (Session::exists('document_warning')) {
echo showAlert('warning', Session::get('document_warning'));
echo "<script>hideAlert('warning');</script>";
Session::delete('document_warning');
}

if (Session::exists('document_error')) {
echo showAlert('error', Session::get('document_error'));
echo "<script>hideAlert('error');</script>";
Session::delete('document_error');
}
?>





