<?php
include_once UTIL_PATH . 'ModalEditDocumento.php';

 if ($_SESSION['user_role'] == 1) : 
 ?>

    <script>
        function confirmDelete(docID, docTipo, userName) {
            openModal('deleteDocument');
            var message = `¿Estás seguro de que deseas eliminar el documento de tipo "${docTipo}" de "${userName}"?`;
            var modal = $(".deleteDocument");
            modal.find(".modal_body").html(`<span>${message}</span><button onclick="deleteDocument(${docID})">Eliminar</button>`);
        }

        function deleteDocument(docID) {
            window.location.href = `deleteDocument.php?docID=${docID}`;
        }
    </script>

<?php endif; ?>



<?php if (!empty($documents)) : ?>
    

    <div class="card_table">
        <div class="card_table_header">
            <h2><?php echo ($_SESSION['user_role'] == 3) ? "Mis documentos" : "Documentos"; ?></h2>
            <div class="card_header_actions">
                <div class="dias_economicos">
                    
                    <?php if ($puedeDiaEconomico): ?>
                        <span><?= $diasEconomicos; ?> / <?= $maxDiasEconomicos; ?></span>
                        <i class="fa-solid fa-file-lines" title="Dia economico"></i>
                    <?php endif; ?>
                    <span><?= $diaCumple; ?> / 1</span>
                    <i class="fa-solid fa-birthday-cake" title="Dia de cumpleaños"></i>
                    <span><?= $reportesIncidencia; ?></span>
                    <i class="fa-solid fa-file-circle-xmark" title="Reporte de incidencia"></i>
                </div>
                <?php if ($_SESSION['user_role'] == 1) : ?>
                
                    <!-- <button class="btn_documento" onclick="generarPDF(1)">PDf prueba</button> -->
                <?php endif; ?>
            </div>
        </div>
        <div class="card_table_body">
            <div class="search_input" id="searchForm">
                <input type="text" id="searchInput" placeholder="<?php echo ($_SESSION['user_role'] == 3) ? "Buscar Documento por Tipo - Fecha - Estatus" : "Buscar Documento por Empleado - Tipo - Fecha - Estatus" ?>">
                <i class="fa-solid fa-xmark" id="clear_input"></i>
            </div>
            <div class="table_header">
                <span class="header_pdf"></span>
                <?php if ($_SESSION['user_role'] != 3) : ?>
                    <span class="header_empleado">Empleado</span>
                <?php endif; ?>
                <span class="header_tipo">Tipo</span>
                <span class="header_fecha">Fecha de registro</span>
                <span class="header_estatus">Estatus</span>
                <?php if ($_SESSION['user_role'] == 1) : ?>
                    <span class="header_actions">Acciones</span>
                <?php endif; ?>
            </div>
            <div class="table_body" id="tableContainer">

                <?php foreach ($documents as $document) : ?>
                    <?php   echo generateModalEditDocument($document["documento_id"]); ?>
                    <div class="table_body_item">
                        <span class="row_pdf" title="Descargar <?php echo $document['documento_tipo']; ?>">
                            <a target="_blank"  href="download.php?docID=<?php echo $document['documento_id']; ?>"><i class="fa-solid fa-file-pdf"></i></a>
                        </span>
                        <?php if ($_SESSION['user_role'] != 3) : ?>
                            <div class="row_user_info">
                                <?php if ($document['usuario_genero'] === 'H') {
                                    echo '<img src="assets/images/hombre.png">';
                                } else {
                                    echo '<img src="assets/images/mujer.png">';
                                } ?>
                                <div class="info">
                                    <span class="user_name"><?php echo $document["usuario_nombre"]; ?></span>
                                    <span><?php echo $document["usuario_email"] ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                        <span class="row_tipo"><?php echo $document["documento_tipo"] ?></span>
                        <span class="row_fecha"><?php echo $document["documento_fechaCreacion"] ?></span>
                        <?php
                        $estatusClass = '';
                        switch ($document['documento_estatus']) {
                            case "Entregado":
                                $estatusClass = 'success';
                                break;
                            case "Pendiente":
                                $estatusClass = 'warning';
                                break;
                            case "Sin Entregar":
                                $estatusClass = 'danger';
                                break;
                        }
                        echo "<span class=\"row_estatus {$estatusClass}\">{$document['documento_estatus']}</span>"; ?>
                        <?php if ($_SESSION['user_role'] == 1) : ?>
                            <div class="row_actions">
                                <i class="fa-solid fa-pen-to-square" title="Modificar <?= $document["documento_tipo"]; ?> de <?= $document["usuario_nombre"]; ?>" data-id="<?php echo $document['documento_id']; ?>" onclick="openModal('editDocument<?php echo $document['documento_id']; ?>')"></i>
                                <i class="fa-solid fa-trash-can" title="Eliminar <?= $document["documento_tipo"]; ?> de <?= $document["usuario_nombre"]; ?>" onclick="confirmDelete(<?= $document['documento_id']; ?>, '<?= $document['documento_tipo']; ?>', '<?= $document['usuario_nombre']; ?>')"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <?php echo generateModal('deleteDocument', 'Eliminar documento', true); ?>
            </div>
            <div class="no_result_message" id="noResultsMessage" style="display: none;">
                <span>No se encontraron coincidencias.</span>
                <i class="fa-solid fa-magnifying-glass"></i>
            </div>
        </div>
    </div>
    <script src="assets/js/search_document.js"></script>

<?php else : ?>
    <div class="card_table">
        <div class="card_table_header">
            <h2><?php echo ($_SESSION['user_role'] == 3) ? "Mis documentos" : "Documentos"; ?></h2>
            <div class="card_header_actions">
                <div class="dias_economicos">
                    
                    <?php if ($puedeDiaEconomico): ?>
                        <span><?= $diasEconomicos; ?> / <?= $maxDiasEconomicos; ?></span>
                        <i class="fa-solid fa-file-lines" title="Dia economico"></i>
                    <?php endif; ?>
                    <span><?= $diaCumple; ?> / 1</span>
                    <i class="fa-solid fa-birthday-cake" title="Dia de cumpleaños"></i>
                    <span><?= $reportesIncidencia; ?></span>
                    <i class="fa-solid fa-file-circle-xmark" title="Reporte de incidencia"></i>
                </div>
             
            </div>
        </div>
        <div class="card_table_body">
            <div class="card_table_message">
                <div class="no_result_message">
                    <span>Aun no hay documentos por mostrar</span>
                    <i class="fa-regular fa-folder-open"></i>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script src="assets/js/alert.js"></script>
<script src="assets/js/modal.js"></script>

<script type="module">
    import {
        addDiaEconomico
    } from './assets/js/documents/diaEconomico.js';
    import {
        addDiaCumple
    } from './assets/js/documents/diaCumple.js';
    import {
        addReporteIncidencia
    } from './assets/js/documents/reporteIncidencia.js';

    const btnDiaEconomico = document.querySelector('.fa-file-lines');
    if (btnDiaEconomico) {
        btnDiaEconomico.addEventListener('click', function() {
            addDiaEconomico();
        });
    }

    document.querySelector('.fa-birthday-cake').addEventListener('click', function() {
        addDiaCumple();
    });
    document.querySelector('.fa-file-circle-xmark').addEventListener('click', function() {
        addReporteIncidencia();
    });
</script>

<?php

if ($_SESSION['user_role'] == 1) {
    echo generateModalDocument();
}

echo generateModal('addDiaEconomico', 'Generar dia economico', true);
echo generateModal('addDiaCumple', 'Generar dia de cumpleaños', true);
echo generateModal('addIncidencia', 'Generar reporte de incidencia', true);

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
<script>
    window.maxDiasEconomicos = <?= isset($maxDiasEconomicos) ? intval($maxDiasEconomicos) : 0 ?>;
    window.diasEconomicosActuales = <?= isset($diasEconomicos) ? intval($diasEconomicos) : 0 ?>;
</script>