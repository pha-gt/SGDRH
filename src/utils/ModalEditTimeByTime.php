<?php

require_once MODEL_PATH . "TimebyTimeModel.php";
require_once SERVER_PATH . "DB.php";

function generateModalEditTimeByTime($docID, $folio, $estatusRegistro, $userID)
{
    $db = new DB();
    $TimebyTimeModel = new TimebyTimeModel($db);
    $resultados = $TimebyTimeModel->getValidationRegistro($docID);
    
    $faltas = $resultados['faltas'];
    $pagos = $resultados['pagos'];
    
    $userRole = $_SESSION['user_role']; // Asumimos que el rol del usuario está en la sesión

    $horasFaltas = array_column($faltas, 'horasF');
    $totalHorasFaltas = array_sum($horasFaltas);
    $totalHorasPagosMarcados = array_sum(array_map(function($pago) {
        return ($pago['estatusP'] == 1) ? $pago['horaP'] : 0;
    }, $pagos));

    $isEditable = ($estatusRegistro == 'pendiente'); // Solo pendiente permite edición
    $isAdmin = ($userRole == 1 || $userRole == 2);
    $isUser = ($userRole == 4);
    $buttonDisabled = ($estatusRegistro == 'entregado') ? 'disabled' : '';
    $modal = "
    <div class=\"modal timebytimeEdit{$docID}\">
        <div class=\"modal_content\">
            <div class=\"modal_header\">";
            if ($isUser) {
                $modal .= "<h2>Editar Registro Folio - {$folio}</h2>";
            } else {
                $modal .= "<h2>Validar Registro Folio - {$folio}</h2>";
            }
    $modal .= "
                <button onclick=\"closeModal('timebytimeEdit{$docID}')\">Cerrar</button>
            </div>
            <div class=\"modal_body\">
                <form action=\"admin_home.php?page=TimeByTime&action=timebytimeEdit\" method=\"POST\">
                    <input type=\"hidden\" name=\"docID\" value=\"{$docID}\" required>
                    <input type=\"hidden\" name=\"userID\" value=\"{$userID}\" required>";

    // Alerta si usuario es rol 4 y estatus entregado
    if ($estatusRegistro == 'entregado') {
        $modal .= "<div class=\"alert\">⚠️ Solo se pueden actualizar registros pendientes.</div>";
    }

    // Tabla de Faltas
    $modal .= "
                    <h3>Faltas</h3>
                    <table border=\"1\">
                        <thead>
                            <tr>
                                <th>Fecha de Falta</th>
                                <th>Horas de Falta</th>
                            </tr>
                        </thead>
                        <tbody>";

    foreach ($faltas as $index => $falta) {
        $inputDisabled = ($isUser && $estatusRegistro == 'entregado') ? 'disabled' : (($isEditable) ? '' : 'disabled');

        $modal .= "
                            <tr>
                                <td><input type=\"date\" name=\"fechaF_{$falta['id']}\" value=\"{$falta['fechaF']}\" required {$inputDisabled}></td>
                                <td><input class=\"horasFalta\" type=\"number\" step=\"1\" min=\"1\" max=\"24\" name=\"horasF_{$falta['id']}\" value=\"{$falta['horasF']}\" required {$inputDisabled} pattern=\"[0-9]+\" oninput=\"this.value = this.value.replace(/[^0-9]/g, '')\"></td>
                            </tr>";
    }

    $modal .= "
                        </tbody>
                    </table>";
                    
                    if (($isAdmin && $isEditable) || ($isUser && $isEditable)) {
                        $modal .= "<button type=\"button\" class=\"addFalta\" data-docid=\"{$docID}\" >➕ Añadir Falta</button>";
                    }
    $modal .= "
                    <label><strong>Total de horas faltadas:</strong> <span id=\"totalHorasFalta{$docID}\">{$totalHorasFaltas}</span></label>";
    // Tabla de Pagos
    $modal .= "
    <h3>Pagos</h3>
    <table border=\"1\">
        <thead>
            <tr>
                <th>Fecha de Pago</th>
                <th>Horas de Pago</th>
                <th>Validar</th>
            </tr>
        </thead>
        <tbody>";

    foreach ($pagos as $index => $pago) {
        $inputDisabled = ($isUser && $estatusRegistro == 'entregado') ? 'disabled' : (($isEditable) ? '' : 'disabled');
        $checkboxDisabled = ($estatusRegistro == 'entregado' || $isUser) ? 'disabled' : '';

        $isChecked = ($pago['estatusP'] == 1) ? 'checked' : '';

        $modal .= "
            <tr>
                <td><input type=\"date\" name=\"fechaP_{$pago['id']}\" value=\"{$pago['fechaP']}\" {$inputDisabled} required></td>
                <td><input class=\"horasPago\" type=\"number\" step=\"1\" min=\"1\" max=\"24\" name=\"horaP_{$pago['id']}\" value=\"{$pago['horaP']}\" {$inputDisabled} required pattern=\"[0-9]+\" oninput=\"this.value = this.value.replace(/[^0-9]/g, '')\"></td>
                <td>
                    <input type=\"hidden\" name=\"estatusP_{$pago['id']}\" value=\"" . ($pago['estatusP'] == 1 ? 1 : 0) . "\">
                    <input type=\"checkbox\" class=\"estatusP\" id=\"checkbox_{$docID}_{$index}\" data-horas=\"{$pago['horaP']}\" {$isChecked} {$checkboxDisabled}>
                </td>
            </tr>";
    }

    $modal .= "
                        </tbody>
                    </table>";
                    // Botón de añadir incidencia si es admin o si rol 4 con estatus pendiente
                    if (($isAdmin && $isEditable) || ($isUser && $isEditable)) {
                        $modal .= "<button type=\"button\" class=\"addPago\" data-docid=\"{$docID}\">➕ Añadir Pago</button>";
                    }
    $modal .= "
                    <label><strong>Total de horas pagadas:</strong> <span id=\"totalHorasPagos{$docID}\">{$totalHorasPagosMarcados}</span></label><br>";
    $modal .= "
                    <button type=\"submit\" {$buttonDisabled}>Actualizar documento</button>
                </form>
            </div>
        </div>
    </div>";
                    
    // Script de lógica JS
    $modal .= "
   <script>
        document.addEventListener(\"DOMContentLoaded\", function() {
            function actualizarTotalHorasFaltas(idTotalHorasF, modalSelector) {
                let totalHoras = 0;
                const valueTotalFaltas = document.getElementById(idTotalHorasF);
                if (!valueTotalFaltas) return;
                const modal = document.querySelector(modalSelector);
                if (!modal) return;
                modal.querySelectorAll(modalSelector + \" .horasFalta\").forEach(function(input) {
                    totalHoras += parseFloat(input.value) || 0;
                });
                 valueTotalFaltas.textContent = totalHoras;
            }

            function actualizarTotalHorasPagos(idTotalHoras, modalSelector) {
                let totalHoras = 0;
                const totalHorasElement = document.getElementById(idTotalHoras);
                if (!totalHorasElement) return;
                const modal = document.querySelector(modalSelector);
                if (!modal) return;
                modal.querySelectorAll(modalSelector + \" .estatusP:checked\").forEach(function(checkbox) {
                    totalHoras += parseFloat(checkbox.dataset.horas) || 0;
                });
                 totalHorasElement.textContent = totalHoras;
            }


            document.querySelectorAll(\".modal\").forEach(function(modal) {
                const timebyClass = Array.from(modal.classList).find(c => c.startsWith(\"timebytimeEdit\"));
                if (timebyClass) {
                    const docID = timebyClass.replace(\"timebytimeEdit\", \"\");
                    const modalSelector = \".timebytimeEdit\" + docID;
                    
                    modal.querySelectorAll(\".estatusP\").forEach(function(checkbox) {
                        checkbox.addEventListener(\"change\", function() {
                            actualizarTotalHorasPagos(\"totalHorasPagos\" + docID, modalSelector);
                            var hiddenInput = this.previousElementSibling;
                            hiddenInput.value = this.checked ? 1 : 0;
                        });
                    });

                    modal.querySelectorAll(\".horasFalta\").forEach(function(input) {
                        input.addEventListener(\"input\", function() {
                            actualizarTotalHorasFaltas(\"totalHorasFalta\" + docID, modalSelector);
                        });
                    });

                    modal.querySelectorAll(\"input[class^='horasPago']\").forEach(function(input) {
                        input.addEventListener(\"input\", function() {
                            const checkbox = input.closest(\"tr\").querySelector(\".estatusP\");
                            if (checkbox) {
                                checkbox.dataset.horas = input.value || 0;
                                actualizarTotalHorasPagos(\"totalHorasPagos\" + docID, \".timebytimeEdit\" + docID);
                            }
                        });
                    });
                }
            });

            function addFalta(docID) {
                const modalSelector = \".timebytimeEdit\" + docID;
                const faltasTableBody = document.querySelector(modalSelector + ' table:nth-of-type(1) tbody');
                const faltasCount = faltasTableBody.querySelectorAll('tr').length;

                const newFaltaRow = document.createElement('tr');
                const newFaltaFecha = document.createElement('td');
                const newFaltaHoras = document.createElement('td');

                const inputFechaF = document.createElement('input');
                inputFechaF.type = 'date';
                inputFechaF.name = 'fechasF[]';
                inputFechaF.required = true;

                const inputHorasF = document.createElement('input');
                inputHorasF.type = 'number';
                inputHorasF.step = '1';
                inputHorasF.min = '1';
                inputHorasF.max = '24';
                inputHorasF.name = 'horasF[]';
                inputHorasF.className = 'horasFalta';
                inputHorasF.required = true;
                inputHorasF.pattern = '[0-9]+';
                inputHorasF.oninput = function() {
                    this.value = this.value.replace(/[^0-9]/g, '');
                };

                inputHorasF.addEventListener(\"input\", function() {
                    const modal = document.querySelector(modalSelector);
                    if (!modal) return;
                    actualizarTotalHorasFaltas(\"totalHorasFalta\" + docID, modalSelector);
                });

                newFaltaFecha.appendChild(inputFechaF);
                newFaltaHoras.appendChild(inputHorasF);
                newFaltaRow.appendChild(newFaltaFecha);
                newFaltaRow.appendChild(newFaltaHoras);

                faltasTableBody.appendChild(newFaltaRow);
            }

            function addPago(docID) {
                const user_docID = " . ($isUser ? 'true' : 'false') . ";
                const admin_docID = " . ($isAdmin ? 'true' : 'false') . ";
                const modalSelector = \".timebytimeEdit\" + docID;
                const pagosTableBody = document.querySelector(modalSelector + ' table:nth-of-type(2) tbody');
                const pagosCount = pagosTableBody.querySelectorAll('tr').length;

                const newPagoRow = document.createElement('tr');
                const newPagoFecha = document.createElement('td');
                const newPagoHoras = document.createElement('td');
                const newPagoCheckboxTd = document.createElement('td');

                const inputFechaP = document.createElement('input');
                inputFechaP.type = 'date';
                inputFechaP.name = 'fechasP[]';
                inputFechaP.required = true;

                const inputHorasP = document.createElement('input');
                inputHorasP.type = 'number';
                inputHorasP.step = '1';
                inputHorasP.min = '1';
                inputHorasP.max = '24';
                inputHorasP.name = 'horasP[]';
                inputHorasP.required = true;
                inputHorasP.className = 'horasPago';
                inputHorasP.pattern = '[0-9]+';
                inputHorasP.oninput = function() {
                    this.value = this.value.replace(/[^0-9]/g, '');
                };

                const hiddenEstatusP = document.createElement('input');
                hiddenEstatusP.type = 'hidden';
                hiddenEstatusP.name = 'estatusP';
                hiddenEstatusP.value = '1';

                const inputCheckbox = document.createElement('input');
                inputCheckbox.type = 'checkbox';
                inputCheckbox.classList.add('estatusP');
                inputCheckbox.dataset.horas = '0';
                inputCheckbox.checked = true;

                if (user_docID || admin_docID) {
                    inputCheckbox.disabled = true;
                }

                inputHorasP.addEventListener(\"input\", function() {
                    inputCheckbox.dataset.horas = inputHorasP.value || 0;
                    actualizarTotalHorasPagos(\"totalHorasPagos\" + docID, modalSelector);
                });

                newPagoFecha.appendChild(inputFechaP);
                newPagoHoras.appendChild(inputHorasP);
                newPagoCheckboxTd.appendChild(hiddenEstatusP);
                newPagoCheckboxTd.appendChild(inputCheckbox);

                newPagoRow.appendChild(newPagoFecha);
                newPagoRow.appendChild(newPagoHoras);
                newPagoRow.appendChild(newPagoCheckboxTd);

                pagosTableBody.appendChild(newPagoRow);
            }

            document.querySelectorAll('.addFalta').forEach(function(button) {
                button.onclick = function() {
                    const docID = this.getAttribute('data-docid');
                    addFalta(docID);
                };
            });

            document.querySelectorAll('.addPago').forEach(function(button) {
                button.onclick = function() {
                    const docID = this.getAttribute('data-docid');
                    addPago(docID);
                };
            });
        });
    </script>";

    return $modal;
}
?>