<?php

require_once MODEL_PATH . "DocumentModel.php";
require_once SERVER_PATH . "DB.php";

if (isset($_GET['action']) && $_GET['action'] === 'editDocument') {
    if (isset($_POST['docID'])) {
        $docID = $_POST['docID'];
        // Forzar el estatus a "Entregado"
        $estatus = "Entregado";

        // Manejar la subida del archivo
        $documentoFile = null;
        if (isset($_FILES['documento']) && $_FILES['documento']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/documents/'; // Directorio donde se guardarán los archivos
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true); // Crear el directorio si no existe
            }

            $fileName = basename($_FILES['documento']['name']);
            $filePath = $uploadDir . $fileName;

            // Mover el archivo subido al directorio de destino
            if (move_uploaded_file($_FILES['documento']['tmp_name'], $filePath)) {
                $documentoFile = $filePath; // Ruta del archivo guardado
            }
        }

        // Conexión a la base de datos
        require_once MODEL_PATH . "DocumentModel.php";
        require_once SERVER_PATH . "DB.php";

        $db = new DB();
        $documentModel = new DocumentModel($db);

        // Actualizar el estatus y el archivo en la base de datos
        $query = "UPDATE documento SET documento_estatus = :estatus, documento_file = :documentoFile WHERE documento_id = :docID";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':estatus', $estatus, PDO::PARAM_STR);
        $stmt->bindParam(':documentoFile', $documentoFile, PDO::PARAM_STR);
        $stmt->bindParam(':docID', $docID, PDO::PARAM_INT);

        if ($stmt->execute()) {
            // Redirigir con un mensaje de éxito
            header("Location: admin_home.php?page=dashboard&status=success");
        } else {
            // Redirigir with un mensaje de error
            header("Location: admin_home.php?page=dashboard&status=error");
        }
        exit();
    }
}

function generateModalEditDocument($docID)
{
    $db = new DB();
    $documentModel = new DocumentModel($db);
    $document = $documentModel->getDocumentById($docID);

    $modal = "
        <div class=\"modal editDocument{$docID}\" >
        <div class=\"modal_content\">
            <div class=\"modal_header\">
                <h2>Actualizar documento</h2>
                <button onclick=\"closeModal('editDocument{$document['documento_id']}')\">
                Cerrar<i class=\"fa-solid fa-xmark\"></i>
                </button>
            </div>
            <div class=\"modal_body\">
                <form action=\"admin_home.php?page=dashboard&action=editDocument\" method=\"POST\" enctype=\"multipart/form-data\">
                    <div class=\"input_group\">
                        <label>Adjuntar documento</label>
                        <input type=\"file\" accept=\".pdf\"  name=\"documento\" required>
                    </div>
                    <input type=\"hidden\" name=\"docID\" value=\"{$document['documento_id']}\" >
                    <input type=\"hidden\" name=\"documentoEstatus\" value=\"Entregado\">
                    <button type=\"submit\">Actualizar documento</button>
                </form>
            </div>
        </div>
    </div>
    <style>
    /* Estilo para el <select> */
    select#documentoEstatus {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        background-color:rgba(0, 162, 154, 0.19);
        font-size: 16px;
        color: #333;
        cursor: pointer;
    }

    /* Estilo para las opciones */
    select#documentoEstatus option {
        padding: 10px;
        background-color:rgba(254, 254, 254, 0.65);
        color: #333;
    }

    /* Cambiar el color de fondo al pasar el mouse */
    select#documentoEstatus option:hover {
        background-color: #f0f0f0;
        
    }

    /* Estilo para el span que muestra el estatus */
    span.sBtn_text {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 14px;
        font-weight: bold;
        color: #fff;
        background-color:rgb(4, 99, 95);
        text-align: center;
        width: 50%; /* Asegura un ancho mínimo */
        margin: 0 auto; /* Centra horizontalmente */

    }

  
    </style>
";

    return $modal;
}
