<?php

require_once MODEL_PATH . "ConfigModel.php";
require_once MODEL_PATH . "UserModel.php";
require_once SERVER_PATH . "DB.php";
require_once UTIL_PATH . "Modaldiasexcel.php";
require_once UTIL_PATH . 'Session.php';


function generateModalexceldiaseco()
{
    $db = new DB();
    $configModel = new ConfigModel($db);

    $modal = "
    <div class=\"modal exceldiaseco\">
        <div class=\"modal_content\">
            <div class=\"modal_header\">
                <h2>Cargar Días Económicos Masivo</h2>
                <button onclick=\"closeModal('exceldiaseco')\">Cerrar</button>
            </div>
            <div class=\"modal_body\">
                <form id=\"uploadCSVForm\" action=\"http://localhost/SGD/public/admin_home.php\" method=\"POST\" enctype=\"multipart/form-data\">
                    
                    <!-- Botón para descargar plantilla de CSV -->
                    <div class=\"input_group\">
                        <label>Descargar plantilla</label>
                        <a href=\"data:text/csv;charset=utf-8,Nombre,Numero%20Nomina,Dias%20Economicos\" 
                           class=\"btn_download\" 
                           download=\"plantilla_dias_economicos.csv\">
                           Descargar plantilla
                        </a>
                    </div>

                    <!-- Campo para adjuntar archivo CSV -->
                    <div class=\"input_group\">
                        <label for=\"archivo_csv\">Selecciona un archivo CSV</label>
                        <input type=\"file\" name=\"archivo_csv\" id=\"archivo_csv\" accept=\".csv\" required>
                    </div>

                    <div class=\"modal_actions\">
                        <button type=\"submit\" class=\"btn_confirm\">Cargar</button>
                        <button type=\"button\" onclick=\"closeModal('exceldiaseco')\" class=\"btn_cancel\">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // JavaScript para manejar el modal
        $(document).ready(function () {
            $('#uploadCSVForm').on('submit', function(event) {
                const fileInput = $('#archivo_csv').val().trim();
                if (!fileInput) {
                    event.preventDefault();
                    alert('Por favor, selecciona un archivo CSV antes de enviar.');
                }
            });
        });
    </script>
    ";

    return $modal;
}

function procesarArchivoCSV($db)
{
    // Iniciar la sesión si no está iniciada
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    try {
        if (!isset($_FILES['archivo_csv']) || $_FILES['archivo_csv']['error'] != 0) {
            throw new Exception('Error al subir el archivo.');
        }

        $archivoTmp = $_FILES['archivo_csv']['tmp_name'];

        if (($handle = fopen($archivoTmp, "r")) === false) {
            throw new Exception('No se pudo abrir el archivo.');
        }

        // Leer y descartar la primera fila (encabezados)
        $header = fgetcsv($handle);
        if ($header === false || count($header) < 3) {
            throw new Exception('El archivo CSV no tiene el formato esperado. Asegúrate de usar la plantilla.');
        }

        $success = true;
        $totalFilas = 0;
        $filasExitosas = 0;
        $filasFallidas = 0;

        // Leer cada fila del archivo CSV
        while (($datos = fgetcsv($handle, 1000, ",")) !== false) {
            if (count($datos) < 3) {
                $success = false;
                $filasFallidas++;
                $_SESSION['user_error'] = 'Error: El archivo CSV no tiene el formato esperado. Asegúrate de usar la plantilla.';
                continue;
            }

            $numeroNomina = trim($datos[1]);
            $diasEconomicos = trim($datos[2]);
            $totalFilas++;

            if (!empty($numeroNomina) && is_numeric($diasEconomicos)) {
                $query = "UPDATE usuario 
                          SET dias_economicos = :diasEconomicos 
                          WHERE usuario_nomina = :numeroNomina";

                $stmt = $db->prepare($query);
                $stmt->bindParam(':diasEconomicos', $diasEconomicos, PDO::PARAM_INT);
                $stmt->bindParam(':numeroNomina', $numeroNomina, PDO::PARAM_STR);

                if ($stmt->execute() && $stmt->rowCount() > 0) {
                    $filasExitosas++;
                } else {
                    $success = false;
                    $filasFallidas++;
                    $_SESSION['user_error'] = "Error al actualizar el registro con Numero Nomina $numeroNomina.";
                }
            } else {
                $success = false;
                $filasFallidas++;
                $_SESSION['user_error'] = 'Datos inválidos en el archivo CSV: Numero Nomina o Días Económicos vacíos o incorrectos.';
            }
        }

        fclose($handle);

        // Mensaje resumen
        if ($filasExitosas === $totalFilas && $totalFilas > 0) {
            $_SESSION['user_success'] = "CSV procesado correctamente. Filas procesadas: $totalFilas.";
        } else {
            $_SESSION['user_error'] = "CSV procesado con errores. Filas exitosas: $filasExitosas, filas fallidas: $filasFallidas, de un total de $totalFilas.";
        }
    } catch (Exception $e) {
        $_SESSION['user_error'] = 'Error: ' . $e->getMessage();
    }

    // Redirigir al usuario con PHP
    header("Location: http://localhost/SGD/public/admin_home.php?page=manage_users");
    exit;
}

$db = new DB(); // Crear la conexión a la base de datos

// Procesar el archivo CSV si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo_csv'])) {
    procesarArchivoCSV($db);
}
