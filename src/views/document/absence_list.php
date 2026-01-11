<link rel="stylesheet" href="assets/css/components/table.css">
<link rel="stylesheet" href="assets/css/components/modal.css">
<link rel="stylesheet" href="assets/css/components/dropdown.css">
<link rel="stylesheet" href="assets/css/components/chips.css">
<link rel="stylesheet" href="assets/css/components/alerts.css">
<link rel="stylesheet" href="assets/css/admin/dashboard.css">
<link rel="stylesheet" href="assets/css/admin/see_user.css">
<link rel="stylesheet" href="assets/css/admin/manage_users.css">

<style>
    .form_row {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1rem;
    }
    .form_group {
        flex: 1 1 30%; /* Ocupa 1/3 del espacio */
        min-width: 200px;
    }
    </style>

    <script>
    function confirmDelete(id, data) {
        const modalContent = `
        <div class="modal confirmDelete">
            <div class="modal_content">
               <form method="post" action="admin_home.php?page=absences&action=remove">
                <input type="hidden" name="absence_id" value="${id}">
                 <div class="modal_header">
                    <h2>Confirmar Eliminación</h2>
                        <button type="button" onclick="closeModal('confirmDelete')">Cerrar</button>
                    </div>
                    <div class="modal_body">
                        <p>¿Estás seguro de que deseas eliminar el registro de: <strong>${data}</strong>?</p>
                        <div class="modal_actions">
                            <button type="submit" class="btn_confirm">Eliminar</button>
                            <button type="button" onclick="closeModal('confirmDelete')" class="btn_cancel">Cancelar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    `;
        document.body.insertAdjacentHTML('beforeend', modalContent);
        openModal('confirmDelete');
    }

    function calculateDays() {
        const startInput = document.getElementById('start_date');
        const endInput = document.getElementById('end_date');
        const totalDaysInput = document.getElementById('total_days');

        if (startInput.value && endInput.value) {
            // Normalizamos a medianoche UTC
            const startDate = new Date(startInput.value + 'T00:00:00Z');
            const endDate = new Date(endInput.value + 'T00:00:00Z');

            const timeDiff = endDate - startDate;
            const dayDiff = Math.floor(timeDiff / (1000 * 60 * 60 * 24)) + 1;

            totalDaysInput.value = dayDiff > 0 ? dayDiff : 0;
        } else {
            totalDaysInput.value = '';
        }
    }


    function addabsence(users) {
        console.log("usuarios", users);
        const existing = document.querySelector('.modal.addAbsence');
        if (existing) existing.remove();
        const options = users.map(user => `<option value="${user.usuario_id}">${user.usuario_nombre}</option>`).join('');

        const modalContent = `
            <div class="modal addAbsence">
                <div class="modal_content">
                    <div class="modal_header">
                        <h2>Agregar Ausencia</h2>
                        <button onclick="closeModal('addAbsence')">Cerrar</button>
                    </div>
                <div class="modal_body">
                    <form action="admin_home.php?page=absences&action=save" method="POST" id="addAbsenceForm" enctype="multipart/form-data">
                        <div class="form_row">
                            <div class="form_group">
                                <label for="folio_number">Folio</label>
                                <div class="input_group">
                                    <input class="search_input" type="text" name="folio_number" id="folio_number" required>
                                </div>
                            </div>
                            <div class="form_group">
                                <label for="start_date">Fecha de Inicio</label>
                                <div class="input_group">
                                    <input class="search_input" type="date" name="start_date" id="start_date" required onchange="calculateDays()">
                                </div>
                            </div>
                            <div class="form_group">
                                <label for="total_days">Días</label>
                               <div class="input_group">
                                    <input class="search_input" type="number" name="total_days" id="total_days" min="1" oninput="calculateEndDateFromDays()">
                                </div>
                            </div>

                        </div>

                        <div class="form_row">
                            <div class="form_group">
                                <label for="user_select">Usuario</label>
                                <div class="input_group">
                                    <select class="selection" name="user_id" id="user_select" required>
                                        ${options}
                                    </select>
                                </div>
                            </div>
                           <div class="form_group">
                                <label for="end_date">Fecha Final</label>
                                <div class="input_group">
                                    <input class="search_input" type="date" name="end_date" id="end_date" required onchange="calculateDays()">
                                </div>
                            </div>
                        </div>
                        <div class="form_group">
                            <label for="file">Documento</label>
                            <div class="input_group">
                                <input class="search_input" type="file" name="document" id="file" required>
                            </div>
                        </div>

                        <div class="modal_actions">
                            <button type="submit" class="btn_confirm">Agregar</button>
                            <button type="button" onclick="closeModal('addAbsence')" class="btn_cancel">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalContent);
        openModal('addAbsence');
    }



    function editAbsence(absenceId, folioNumber, startDate, endDate, isOpen) {
        const modalContent = `
        <div class="modal editAbsence">
            <div class="modal_content">
                <div class="modal_header">
                    <h2>Editar Ausencia</h2>
                    <button type="button" onclick="closeModal('editAbsence')">Cerrar</button>
                </div>
                <div class="modal_body">
                    <form action="index.php?page=absences&action=edit" method="POST" id="editAbsenceForm" enctype="multipart/form-data">
                        <input type="hidden" name="absence_id" value="${absenceId}">
                        <div class="form_row">
                            <div class="form_group">
                                <label for="folio_number">Folio</label>
                                <div class="input_group">
                                    <input class="search_input" type="text" name="folio_number" id="folio_number" value="${folioNumber}" required>
                                </div>
                            </div>
                            <div class="form_group">
                                <label for="start_date">Fecha de Inicio</label>
                                <div class="input_group">
                                    <input class="search_input" type="date" name="start_date" id="start_date" value="${startDate}" required onchange="calculateDays()">
                                </div>
                            </div>
                            <div class="form_group">
                                <label for="end_date">Fecha Final</label>
                                <div class="input_group">
                                    <input class="search_input" type="date" name="end_date" id="end_date" value="${endDate}" required onchange="calculateDays()">
                                </div>
                            </div>
                        </div>

                        <div class="form_row">
                            <div class="form_group">
                                <label for="total_days">Días</label>
                                <div class="input_group">
                                    <input class="search_input" type="number" name="total_days" id="total_days" min="1" oninput="calculateEndDateFromDays()">
                                </div>
                            </div>
                        </div>

                        <div class="form_group">
                            <label for="file">Documento</label>
                            <div class="input_group">
                                <input class="search_input" type="file" name="document" id="file">
                            </div>
                        </div>

                        <div class="modal_actions">
                            <button type="submit" class="btn_confirm">Guardar cambios</button>
                            <button type="button" onclick="closeModal('editAbsence')" class="btn_cancel">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;

        document.body.appendChild(modal);
        openModal('viewAbsence'); // <- Aquí estaba el error de nombre
}

</script>

<script>
    function viewAbsence(absenceId) {
        fetch(`admin_home.php?page=absences&action=view_chain&id=${absenceId}`)
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const content = doc.querySelector('.content');

                if (!content) {
                    alert('No se encontró contenido para mostrar');
                    return;
                }

                const modal = document.createElement('div');
                modal.className = 'modal viewAbsence';
                modal.innerHTML = `
                <div class="modal_content">
                    <div class="modal_header">
                        <h2>Detalle de Incapacidad </h2>
                        <button onclick="closeModal('viewAbsence')">Cerrar</button>
                    </div>
                    <div class="modal_body">${content.innerHTML}</div>
                </div>
            `;

                document.body.appendChild(modal);
                openModal('viewAbsence');
            })
            .catch(error => {
                alert('Error al cargar el detalle');
                console.error(error);
            });
    }

    function addAbsence(users, absence_id = null, selectedUserId = null, end_date = null) {
        let nextStartDate = '';
        if (end_date) {
            const date = new Date(end_date);
            date.setDate(date.getDate() + 1);
            // Format as YYYY-MM-DD
            nextStartDate = date.toISOString().split('T')[0];
        }

        const options = users.map(user => {
            const selected = selectedUserId !== null && user.usuario_id === selectedUserId ? 'selected' : '';
            return `<option value="${user.usuario_id}" ${selected}>${user.usuario_nombre}</option>`;
        }).join('');

        const parentIdInput = absence_id !== null
            ? `<input type="hidden" name="absence_id" value="${absence_id}">`
            : '';

        const modalContent = `
        <div class="modal addAbsence">
            <div class="modal_content">
                <div class="modal_header">
                    <h2>Agregar Ausencia</h2>
                    <button onclick="closeModal('addAbsence')">Cerrar</button>
                </div>
                <div class="modal_body">
                    <input type="hidden" name="user_id" value="${selectedUserId}">
                    <form action="admin_home.php?page=absences&action=save" method="POST" id="addAbsenceForm" enctype="multipart/form-data">
                        <input type="hidden" name="user_id" value="${selectedUserId}">
                        ${parentIdInput}
                        <div class="form_row">
                            <div class="form_group">
                                <label for="folio_number">Folio</label>
                                <div class="input_group">
                                    <input class="search_input" type="text" name="folio_number" id="folio_number" required>
                                </div>
                            </div>
                            <div class="form_group">
                                <label for="start_date">Fecha de Inicio</label>
                                <div class="input_group">
                                    <input class="search_input" type="date" name="start_date" id="start_date" required onchange="calculateDays()" value="${nextStartDate}" readonly>
                                </div>
                            </div>
                            <div class="form_group">
                                <label for="total_days">Días</label>
                                <div class="input_group">
                                    <input class="search_input" type="number" name="total_days" id="total_days" min="1" oninput="calculateEndDateFromDays()">
                                </div>
                            </div>

                        </div>

                        <div class="form_row">
                            <div class="form_group">
                                <label for="user_select">Usuario</label>
                                <div class="input_group">
                                    <select class="selection" id="user_select" disabled>
                                        ${options}
                                    </select>
                                </div>
                            </div>
                           <div class="form_group">
                                <label for="end_date">Fecha Final</label>
                                <div class="input_group">
                                    <input class="search_input" type="date" name="end_date" id="end_date" required onchange="calculateDays()">
                                </div>
                            </div>
                        </div>

                        <div class="form_group">
                            <label for="file">Documento</label>
                            <div class="input_group">
                                <input class="search_input" type="file" name="document" id="file" required>
                            </div>
                        </div>

                        <div class="modal_actions">
                            <button type="submit" class="btn_confirm">Agregar</button>
                            <button type="button" onclick="closeModal('addAbsence')" class="btn_cancel">Cancelar</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    `;
        document.body.insertAdjacentHTML('beforeend', modalContent);
        openModal('addAbsence');
    }


    function calculateEndDateFromDays() {
        const startInput = document.getElementById('start_date');
        const totalDaysInput = document.getElementById('total_days');
        const endInput = document.getElementById('end_date');

        const startDate = new Date(startInput.value + 'T00:00:00Z');
        const days = parseInt(totalDaysInput.value, 10);

        if (!isNaN(startDate.getTime()) && !isNaN(days) && days > 0) {
            const endDate = new Date(startDate);
            endDate.setDate(endDate.getDate() + days - 1); // se resta 1 porque el inicio cuenta como un día

            endInput.value = endDate.toISOString().split('T')[0];
        } else {
            endInput.value = '';
        }
    }


</script>




<div class="card_table">
    <div class="card_table_header">
        <h2>Incapacidades</h2>
        <div class="card_header_actions">
<!--            <button id="toggleButton" class="btn_documento" onclick="toggleAbsenceView()">Ver cerradas</button>-->
            <button class="btn_documento" onclick="addabsence(<?= htmlspecialchars(json_encode($users), ENT_QUOTES, 'UTF-8'); ?>)">Agregar</button>
        </div>
    </div>

    <div class="card_table_body">
        <div class="search_input" id="searchForm">
            <input type="text" id="searchInput" placeholder="Buscar">
            <i class="fa-solid fa-xmark" id="clear_input"></i>
        </div>

        <div class="table_header" style="text-align: center">
            <span class="header_tipo">Documento</span>
            <span class="header_fecha">Usuario</span>
            <span class="header_fecha">Folio</span>
            <span class="header_fecha">Inicio</span>
            <span class="header_fecha">Fin</span>
            <span class="header_fecha">Días de incapacidad</span>
            <span class="header_fecha">Estado</span>
            <span class="header_actions" id="actionsHeader">Acciones</span>
        </div>

        <div id="openAbsences" class="table_body">
            <?php foreach ($return_data as $absence) : ?>
                <?php if ($absence['is_open'] != '1') continue; ?>
                <div class="table_body_item" style="text-align: center">
                    <span class="row_pdf">
                        <?php if (!empty($absence['document'])): ?>
                            <a href="<?= htmlspecialchars($absence['document']); ?>" target="_blank" title="Ver documento">
                                <i class="fa-solid fa-file-pdf"></i>
                            </a>
                        <?php else: ?>
                            <span>Sin documento</span>
                        <?php endif; ?>
                    </span>
                    <span class="row_fecha"><?= htmlspecialchars($absence["full_name"]); ?></span>
                    <span class="row_fecha"><?= htmlspecialchars($absence["folio_number"]); ?></span>
                    <span class="row_fecha"><?= htmlspecialchars($absence["start_date"]); ?></span>
                    <span class="row_fecha"><?= htmlspecialchars($absence["end_date"]); ?></span>
                    <span class="row_fecha"><?= htmlspecialchars($absence["total_days"]); ?></span>
                    <span class="row_estatus success">Abierto</span>

                    <?php if ($_SESSION['user_role'] == 1) : ?>
                        <div class="row_actions" style="margin-left: 1rem">
                            <i class="fa-solid fa-plus"
                               title="Agregar relacionado"
                               onclick="addAbsence(
                               <?= htmlspecialchars(json_encode($users), ENT_QUOTES, 'UTF-8'); ?>,
                               <?= $absence['absence_id']; ?>,
                               <?= $absence['user_id']; ?>,
                                       '<?= $absence['end_date']; ?>'
                                       )">
                            </i>
                            <i class="fa-solid fa-trash-can"
                               title="Eliminar"
                               onclick="confirmDelete(<?= $absence['absence_id']; ?>, '<?= addslashes($absence['full_name']); ?>')">
                            </i>
                            <?php if ($absence['parent_id'] != null) : ?>
                                <i class="fa-solid fa-eye"
                                   title="Ver detalle"
                                   onclick="viewAbsence(<?= $absence['parent_id']; ?>)">
                                </i>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="closedAbsences" class="table_body" style="display: none;">
            <?php foreach ($return_data as $absence) : ?>
                <?php if ($absence['is_open'] != '0') continue; ?>
                <div class="table_body_item" style="text-align: center">
                    <span class="row_pdf">
                        <?php if (!empty($absence['document'])): ?>
                            <a href="<?= htmlspecialchars($absence['document']); ?>" target="_blank" title="Ver documento">
                                <i class="fa-solid fa-file-pdf"></i>
                            </a>
                        <?php else: ?>
                            <span>Sin documento</span>
                        <?php endif; ?>
                    </span>
                    <span class="row_fecha"><?= htmlspecialchars($absence["full_name"]); ?></span>
                    <span class="row_fecha"><?= htmlspecialchars($absence["folio_number"]); ?></span>
                    <span class="row_fecha"><?= htmlspecialchars($absence["start_date"]); ?></span>
                    <span class="row_fecha"><?= htmlspecialchars($absence["end_date"]); ?></span>
                    <span class="row_fecha"><?= htmlspecialchars($absence["total_days"]); ?></span>
                    <span class="row_estatus warning">Cerrado</span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="no_result_message" id="noResultsMessage" style="display: none;">
            <span>No se encontraron roles con el nombre ingresado</span>
            <i class="fa-solid fa-magnifying-glass"></i>
        </div>
    </div>
</div>

<script>
    function toggleAbsenceView() {
        const openList = document.getElementById('openAbsences');
        const closedList = document.getElementById('closedAbsences');
        const btn = document.getElementById('toggleButton');
        const actionsHeader = document.getElementById('actionsHeader'); // Obtener el encabezado de acciones

        if (openList.style.display !== 'none') {
            openList.style.display = 'none';
            closedList.style.display = 'block';
            btn.textContent = 'Ver abiertas';
            actionsHeader.style.display = 'none'; // Ocultar el encabezado de acciones
        } else {
            openList.style.display = 'block';
            closedList.style.display = 'none';
            btn.textContent = 'Ver cerradas';
            actionsHeader.style.display = 'inline-block'; // Mostrar el encabezado de acciones
        }
    }
</script>


<script src="assets/js/search_document.js"></script>
<script src="assets/js/alert.js"></script>
<script src="assets/js/modal.js"></script>


<?php

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
