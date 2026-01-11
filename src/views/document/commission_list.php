<?php if (!empty($documents)) : ?>
    <div class="card_table">
        <div class="card_table_header">
            <h2><?php echo ($_SESSION['user_role'] == 3) ? "Mis Comisiones" : "Comisiones"; ?></h2>
            <div class="card_header_actions">
                <button class="btn_entregadoo" data-status="Entregado" onclick="filterCommissions('Entregado')">Entregados</button>
                    <button class="btn_Pendiente" data-status="Pendiente" onclick="filterCommissions('Pendiente')">Pendientes</button>
                <?php if ($_SESSION['user_role'] == 1 || $_SESSION['user_role'] == 4 || $_SESSION['user_role'] == 2) : ?>
                    <button class="btn_documento" onclick="openModal('comision')">Crear Comisión</button>
                <?php endif; ?>
            </div>
        </div>
        <div class="card_table_body">
            <div class="search_input" id="searchForm">
                <input type="text" id="searchInput" placeholder="<?php echo ($_SESSION['user_role'] == 3) ? "Buscar comision por Fecha " : "Buscar comision por Empleado - Fecha " ?>">
                <i class="fa-solid fa-xmark" id="clear_input"></i>
            </div>
            <div class="table_header">
                <span class="header_pdf"></span>
                <?php if ($_SESSION['user_role'] != 3) : ?>
                    <span class="header_empleado">Empleado</span>
                <?php endif; ?>
                
                <span class="header_fecha">Fecha de Elaboración</span>
                <span class="header_estatus">Estatus</span>
                <?php if ($_SESSION['user_role'] == 1) : ?>
                    <span class="header_actions">Acciones</span>
                <?php endif; ?>
            </div>
            <div class="table_body" id="tableContainer">
                <?php foreach ($documents as $Commission) : ?>
                    <?php if ($Commission['status'] === 'Cancelado') continue; ?>
                    <div class="table_body_item" data-status="<?php echo $Commission['status']; ?>">
                        <span class="row_pdf" title="Descargar Comisión">
                            <?php if ($Commission['status'] === 'Entregado') : ?>
                                <a href="descargapdf.php?id=<?php echo $Commission['id']; ?>" target="_blank">
                                    <i class="fa-solid fa-file-pdf"></i>
                                </a>
                            <?php else : ?>
                                <a href="admin_home.php?registro_id=<?php echo $Commission['id']; ?>&action=generarPdfComissions&page=commissions" target="_blank" title="Generar PDF de <?= $Commission["usuario_nombre"];?>"><i class="fa-solid fa-file-pdf"></i></a>
                            <?php endif; ?>
                        </span>
                        <?php if ($_SESSION['user_role'] != 3) : ?>
                            <div class="row_user_info">
                                <?php if ($Commission['usuario_genero'] === 'H') {
                                    echo '<img src="assets/images/hombre.png">';
                                } else {
                                    echo '<img src="assets/images/mujer.png">';
                                } ?>
                                <div class="info">
                                    <span class="user_name"><?php echo $Commission["usuario_nombre"]; ?></span>
                                    <span><?php echo $Commission["usuario_email"]; ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <span class="row_fecha"><?php echo $Commission["fecha_elaboracion"]; ?></span>
                        <?php 
                        $estatusClass = '';
                        switch ($Commission['status']) {
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
                        echo "<span class=\"row_estatus {$estatusClass}\">{$Commission['status']}</span>"; ?>
                        <?php if ($_SESSION['user_role'] == 1 || $_SESSION['user_role'] == 2 && $Commission['status'] != 'Entregado') : ?>
                            <div class="row_actions">
                                
                                <i class="fa-solid fa-pen-to-square" 
                                    title="Modificar Comisión de <?= $Commission["usuario_nombre"]; ?> " 
                                    data-id="<?php echo $Commission['id']; ?>" 
                                    onclick="openModal('editCommissions<?php echo $Commission['id']; ?>')">
                                </i> 
                                <i class="fa-solid fa-trash-can" 
                                    title="Eliminar Licencia de <?= $Commission["usuario_nombre"]; ?> " 
                                    data-id="<?php echo $Commission['id']; ?>" 
                                    onclick="openModal('ModalDeleteCommissions<?php echo $Commission['id']; ?>')">
                                    
                                    
                                </i>       
                            </div>
                        <?php endif; ?>
                        <?php echo generateModalDeleteCommissions($Commission["id"]); ?>
                        <?php echo generateModalEditComision($Commission["id"]); ?>
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
            <h2><?php echo ($_SESSION['user_role'] == 3 || $_SESSION['user_role'] == 4) ? "Mis Comisiones" : "Comisiones"; ?></h2>
            <div class="card_header_actions">
                <?php if ($_SESSION['user_role'] == 1 || $_SESSION['user_role'] == 4 || $_SESSION['user_role'] == 2) : ?>
                    <button class="btn_documento" onclick="openModal('comision')">Crear Comisión</button>
                <?php endif; ?>
            </div>
        </div>
        <div class="card_table_body">
            <div class="card_table_message">
                <div class="no_result_message">
                    <span>Aún no hay comisiones por mostrar</span>
                    <i class="fa-regular fa-folder-open"></i>
                </div>
            </div>
        </div>
    </div>
    
<?php endif;  
?>
<script src="assets/js/alert.js"></script>
<script src="assets/js/modal.js"></script>
<script>
let currentStatusFilter = 'Pendiente';

function filterCommissions(status) {
    currentStatusFilter = status;
    const items = document.querySelectorAll('.table_body_item');
    items.forEach(item => {
        if (status === 'Entregado') {
            item.style.display = item.getAttribute('data-status') === 'Entregado' ? '' : 'none';
        } else if (status === 'Pendiente') {
            item.style.display = item.getAttribute('data-status') !== 'Entregado' ? '' : 'none';
        }
    });
    filterSearch();
}

function filterSearch() {
    const filter = document.getElementById('searchInput').value.toLowerCase();
    const items = document.querySelectorAll('.table_body_item');
    items.forEach(item => {
        const userName = item.querySelector('.user_name') ? item.querySelector('.user_name').textContent.toLowerCase() : '';
        const fechaElaboracion = item.querySelector('.row_fecha').textContent.toLowerCase();
        const matchesFilter = userName.includes(filter) || fechaElaboracion.includes(filter);
        const matchesStatus = currentStatusFilter === 'Entregado' ? item.getAttribute('data-status') === 'Entregado' : item.getAttribute('data-status') !== 'Entregado';
        if (matchesFilter && matchesStatus) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) { 
        searchInput.addEventListener('input', filterSearch);
    }
    filterCommissions('Pendiente');
});
</script>

<?php
if ($_SESSION['user_role'] == 1 || $_SESSION['user_role'] == 4 || $_SESSION['user_role'] == 2) {
    echo generateModalComision($_SESSION['user_area']);
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
