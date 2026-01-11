<?php

require_once MODEL_PATH . 'DocumentModel.php';
require_once MODEL_PATH . 'UserModel.php';
require_once EMAIL_PATH . 'Exception.php';
require_once EMAIL_PATH . 'PHPMailer.php';
//require_once EMAIL_PATH . 'SMTP.PHP';
require_once PDF_PATH . 'library/fpdf.php';
require_once UTIL_PATH . 'Session.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class DocumentController
{
    private $db; // Agregar la propiedad $db
    private $documentModel;

    public function __construct($db)
    {
        $this->db = $db; // Inicializar $db
        $this->documentModel = new DocumentModel($db);
    }

    public function showAllDocuments($role, $userID)
    {
        // Obtener documentos del usuario
        $documents = $this->documentModel->getAllDocuments($role, $userID);

        // Contar días económicos, días de cumpleaños y reportes de incidencia
        $diasEconomicos = $this->documentModel->getTotalDiasEconomicosByUser($userID);
        $diaCumple = $this->documentModel->countDiaCumple($userID)['diaCumple'];
        $reportesIncidencia = $this->documentModel->countReportesIncidencia($userID)['reportesIncidencia'];

        // Obtener el máximo de días económicos permitido desde la columna 'dias_economicos'
        $userModel = new UserModel($this->db);
        $userInfo = $userModel->getUserById($userID);
        $maxDiasEconomicos = $userInfo['dias_economicos']; // Nueva columna

        // Verificar si el usuario tiene más de 6 meses en la empresa
        $fechaIngreso = $userInfo['usuario_fechaIngreso']; // Usa el nombre correcto

        $fechaIngresoDate = new DateTime($fechaIngreso);
        $hoy = new DateTime();
        $intervalo = $fechaIngresoDate->diff($hoy);
        $puedeDiaEconomico = ($intervalo->m + ($intervalo->y * 12)) >= 6;

        // Pasa $puedeDiaEconomico a la vista
        require VIEW_PATH . 'document/list.php';
    }




    public function downloadDocument($docID)
    {
        $document = $this->documentModel->getDocumentById($docID);

        if ($document) {
            $file_path = $document['documento_file'];
            if (file_exists($file_path)) {
                header('Content-Type: application/octet-stream');
                header("Content-Transfer-Encoding: Binary");
                header("Content-disposition: attachment; filename=" . basename($file_path));
                readfile($file_path);
                exit;
            } else {
                Session::set('download_error', 'Documento no encontrado.');
            }
        } else {
            Session::set('download_error', 'Documento no encontrado.');
        }

        header('Location: admin_home.php?page=dashboard');
        exit;
    }

    public function generateDiaEconomico($db, $userID, $startDate, $endDate, $permiso, $diasEconomicos)
    {
        $actualDate = date("Y-m-d");
        $userModel = new UserModel($db);
        $userInfo = $userModel->getUserById($userID);
        $directorName = $userModel->getDirectorName();

        $pdf = new PDF();
        $pdf->AliasNbPages();
        $pdf->setHeaderTitle("FORMATO DE SOLICITUD DE DIA ECONÓMICO");
        $pdf->AddPage();
        $pdf->generateDiaEconomico(
            $userInfo['usuario_nombre'], 
            $userInfo['puesto_nombre'], 
            $userInfo['usuario_nomina'], 
            $userInfo['sindicato_id'], 
            $startDate, 
            $endDate, 
            $permiso, 
            $userInfo['sindicato_jefe'],
            $userInfo['jefeInmediato_nombre'], 
            $directorName['usuario_nombre']
        );

        $pathPDF = PDF_PATH . 'docs/' . $userInfo['usuario_nombre'] . ' Dia Economico ' . $actualDate . ' ' . time() . '.pdf';
        $pdf->Output('F', $pathPDF, true);

        // Insertar sin validación de máximo
        $response = $this->documentModel->insertDocument(
            $userID,
            'Dia economico',
            $pathPDF,
            $actualDate,
            $diasEconomicos,
            'Pendiente'
        );

        Session::set(($response) ? 'document_success' : 'document_error', ($response) ? 'Dia economico generado con exito.' : 'No se pudo generar el dia economico.');
        echo "<script>$(location).attr('href', 'admin_home.php?page=dashboard');</script>";
    }

    public function generateDiaCumple($db, $userID,$dayOption)
    {
        $result = $this->documentModel->countDiaCumple($userID);

        if ($result['diaCumple'] >= 1) {
            Session::set('document_warning', 'Solo puedes generar un dia de cumpleaños.');
            echo "<script>$(location).attr('href', 'admin_home.php?page=dashboard');</script>";
        } else {

            //$actualDate = sustituir por la variable birthday
            $actualDate = date("Y-m-d");
            $userModel = new UserModel($db);
            $userInfo = $userModel->getUserById($userID);
            $directorName = $userModel->getDirectorName();

            $pdf = new PDF();
            $pdf->AliasNbPages();
            $pdf->setHeaderTitle("FORMATO DE SOLICITUD DE DIA DE CUMPLEAÑOS");
            $pdf->AddPage();
            $pdf->generateDiaCumple(
                $userInfo['usuario_nombre'],
                $userInfo['puesto_nombre'],
                $userInfo['usuario_nomina'],
                $userInfo['sindicato_id'],
                $userInfo['usuario_fechaCumpleaños'],
                $userInfo['sindicato_jefe'],
                $userInfo['jefeInmediato_nombre'],
                $directorName['usuario_nombre'],
                $userInfo['usuario_fechaIngreso'],
                $dayOption
            );


            $pathPDF = PDF_PATH . 'docs/' . str_replace(' ', '', $userInfo['usuario_nombre']) . '_dia_de_cumpleaños_' . $actualDate . ' ' . time() . '.pdf';
            $pdf->Output('F', $pathPDF, true);

            $response = $this->documentModel->insertDocument($userID, 'Dia De Cumpleaños', $pathPDF, $actualDate, $dayOption,'Pendiente');

            Session::set(($response) ? 'document_success' : 'document_error', ($response) ? 'Dia de cumpleaños generado con exito.' : 'No se pudo generar el dia de cumpleaños.');
            echo "<script>$(location).attr('href', 'admin_home.php?page=dashboard');</script>";
        }
    }

    public function generateReporteIncidencia($db, $userID, $incidencia, $date, $motivo)
    {
        $actualDate = date("Y-m-d");
        $userModel = new UserModel($db);
        $userInfo = $userModel->getUserById($userID);
        $directorName = $userModel->getDirectorName();

        $pdf = new PDF();
        $pdf->AliasNbPages();
        $pdf->setHeaderTitle("JUSTIFICACIÓN DE INCIDENCIA");
        $pdf->AddPage();
        $pdf->generateReporteIncidencia(
            $userInfo['usuario_nombre'],
            $userInfo['usuario_nomina'],
            $userInfo['areaAdscripcion_nombre'],
            $userInfo['jefeInmediato_nombre'],
            $directorName['usuario_nombre'],
            $incidencia,
            $date,
            $motivo // <-- Nuevo parámetro
        );

        $pathPDF = PDF_PATH . 'docs/' . $userInfo['usuario_nombre'] . ' Reporte De Incidencia ' . $actualDate . ' ' . time() . '.pdf';
        $pdf->Output('F', $pathPDF, true);

        $response = $this->documentModel->insertDocument($userID, 'Reporte de incidencia', $pathPDF, $actualDate, '', 'Pendiente');

        Session::set(($response) ? 'document_success' : 'document_error', ($response) ? 'Reporte de incidencia generado con exito.' : 'No se pudo generar el reporte de incidencia.');
        echo "<script>$(location).attr('href', 'admin_home.php?page=dashboard');</script>";
    }

    public function addDocument($user, $documentType, $date, $status)
    {
        if (!isset($_FILES['documento']) && $_FILES['documento']['error'] == 0) {
            Session::set('document_error', 'No se seleccionó ningún documento.');
        }

        $file = $_FILES['documento'];
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];

        $fileDestination = PDF_PATH . 'docs/' . basename($fileName);

        if (move_uploaded_file($fileTmpName, $fileDestination)) {
            $response = $this->documentModel->insertDocument($user, $documentType, $fileDestination, $date, '',$status);
            Session::set(($response) ? 'document_success' : 'document_error', ($response) ? 'Documento subido con éxito.' : 'No se pudo guardar el archivo (DB).');
        } else {
            Session::set('document_error', 'Error al subir el documento.');
        }

        echo "<script>$(location).attr('href', 'admin_home.php?page=dashboard');</script>";
    }

    public function deleteDocument($db, $docID)
    {
        $userModel = new UserModel($db);
        $document = $this->documentModel->getDocumentById($docID);

        if ($document) {
            $file_path = $document['documento_file'];
            if (file_exists($file_path)) {
                unlink($file_path); // Eliminar el archivo físico
            }

            $response = $this->documentModel->deleteDocument($docID);

            if ($response) {
                // Configuración del correo
                $subject = "Eliminación del documento";
                $docUserInfo = $userModel->getUserById($document['usuario_id']);
                $userEmail = $docUserInfo['usuario_email'];
                $userName = $docUserInfo['usuario_nombre'];

                $mail = new PHPMailer(true);

                try {
                    // Configuración del servidor SMTP
                    $mail->SMTPDebug = 0;                               // Desactivar salida de depuración
                    $mail->isSMTP();                                    // Usar SMTP
                    $mail->Host       = 'smtp.gmail.com';               // Servidor SMTP
                    $mail->SMTPAuth   = true;                           // Habilitar autenticación SMTP
                    $mail->Username   = 'axelsolorzano53@gmail.com';    // Correo del remitente
                    $mail->Password   = 'ztdc pnmu rtan vvic';          // Contraseña del remitente
                    $mail->SMTPSecure = 'tls';                          // Cifrado TLS
                    $mail->Port       = 587;                            // Puerto SMTP

                    // Destinatarios
                    $mail->setFrom('axelsolorzano53@gmail.com', 'Luis Antonio Muñoz Gonzáles');
                    // $mail->addAddress($userEmail);     // Destinatario

                    // Contenido del correo
                    $mail->isHTML(true);                                  // Formato HTML
                    $mail->Subject = $subject;

                    $mail->Body = "
                    <!DOCTYPE html>
                    <html lang='en'>
                    <head>
                        <meta charset='UTF-8'>
                        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                        <title>SGDRH</title>
                        <style>
                            @import url('https://fonts.googleapis.com/css2?family=Oswald:wght@200;300;400;500;600;700&display=swap');
                            * {
                                box-sizing: border-box;
                                font-family: 'Oswald', sans-serif;
                                margin: 0;
                                padding: 0;
                            }
                            .body_container {
                                background: #00A29A;
                                padding: 20px;
                                width: 100%;
                            }
                            .body_container .title_container h1 {
                                color: #FFFFFF;
                                font-size: 2.5rem;
                            }
                            .body_container .body h3 {
                                color: #FFFFFF;
                                font-size: 1.5rem;
                                padding: 10px;
                            }
                            .body_container .body p {
                                color: #FFFFFF;
                                font-size: 1.2rem;
                                padding: 10px;
                            }
                            .body_container .body .legend {
                                align-items: center;
                                color: #FFFFFF;
                                display: flex;
                                gap: 10px;
                                justify-content: center;
                                padding: 10px;
                            }
                        </style>
                    </head>
                    <body>
                        <div class='body_container'>
                            <div class='title_container'>
                                <h1>Eliminación de documento</h1>
                            </div>
                            <div class='body'>
                                <h3>Estimado " . $userName . "</h3>
                                <p>Queremos informarte que tu documento ha sido eliminado.</p>
                                <span>Si tienes alguna pregunta o necesitas asistencia adicional, no dudes en contactarme personalmente o
                                    por medio de este correo.</span>
                                <div class='legend'>
                                    <span class='asterisk'>*******************</span>
                                    <span class='asterisk' style='font-weight: bold;'>ESTE CORREO ES SOLO INFORMATIVO</span>
                                    <span class='asterisk'>*******************</span>
                                </div>
                            </div>
                        </div>
                    </body>
                    </html>";

                $mail->send();

                // Establecer mensaje de éxito
                Session::set('document_success', 'Documento eliminado con éxito.');
            } catch (Exception $e) {
                // Establecer mensaje de error si el correo no se envía
                Session::set('document_error', 'Documento eliminado pero no se pudo enviar el correo. Detalles: ' . $mail->ErrorInfo);
            }
        } else {
            // Establecer mensaje de error si no se pudo eliminar el documento
            Session::set('document_error', 'No se pudo eliminar el documento.');
        }

    } else {
        // Establecer mensaje de error si el documento no existe
        Session::set('document_error', 'Documento no encontrado.');
    }

    // Redirigir al dashboard
    header("Location: http://localhost/SGD/public/admin_home.php?page=dashboard");
    exit;
    }

    public function updateDocument($docID, $status)
    {
        $document = $this->documentModel->getDocumentById($docID);

        if ($document) {
            if ($this->documentModel->updateDocument($docID, $status)) {
                Session::set('document_success', 'Documento actualizado con éxito.');
            } else {
                Session::set('document_error', 'No se pudo actualizar el documento.');
            }
        } else {
            Session::set('document_error', 'Documento no encontrado.');
        }

        echo "<script>$(location).attr('href', 'admin_home.php?page=dashboard');</script>";
    }

    public function sendEmail($db, $userID, $docID, $emailType, $subject, $docType, $docStatus)
    {

        $mail = new PHPMailer(true);

        $userModel = new UserModel($db);
        $userInfo = $userModel->getUserById($userID);
        $docInfo = $this->documentModel->getDocumentById($docID);

        $userEmail = $userInfo['usuario_email'];
        $userName = $userInfo['usuario_nombre'];
        $type = $docInfo['documento_tipo'];
        $docUserInfo = $userModel->getUserById($docInfo['usuario_id']);
        $user = $docUserInfo['usuario_nombre'];


        try {
            //Server settings
            $mail->SMTPDebug = 2;                               //Enable verbose debug output
            $mail->isSMTP();                                    //Send using SMTP
            $mail->Host       = 'smtp.gmail.com';               //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                           //Enable SMTP authentication
            $mail->Username   = 'axelsolorzano53@gmail.com';    //SMTP username ( Correo De Antonio )
            $mail->Password   = 'ztdc pnmu rtan vvic';          //SMTP password
            $mail->SMTPSecure = 'tls';                          //Enable implicit TLS encryption
            $mail->Port       = 587;                            //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom('axelsolorzano53@gmail.com', 'Luis Antonio Muñoz Gonzales');
        //    $mail->addAddress($userEmail);     //Add a recipient

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = $subject;

            $mail->addEmbeddedImage('header.jpg', 'header_cid', 'header.jpg', 'base64', 'header/jpg');

            switch ($emailType) {
                case "created":
                    $mail->Body = "<!DOCTYPE html>
                    <html lang='en'>

                    <head>
                        <meta charset='UTF-8'>
                        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                        <title>SGDRH</title>
                        <style>
                            @import url('https://fonts.googleapis.com/css2?family=Oswald:wght@200;300;400;500;600;700&display=swap');

                            * {
                                box-sizing: border-box;
                                font-family: 'Oswald', sans-serif;
                                margin: 0;
                                padding: 0;
                            }

                            html {
                                display: flex;
                                flex-direction: column;
                                height: 100vh;
                                width: 100vw;
                            }

                            .body_container {
                                background: #00A29A;
                                padding: 20px;
                                width: 1200px;
                            }

                            .body_container .title_container {
                                padding: 10px;
                            }

                            .body_container .title_container h1 {
                                color: #FFFFFF;
                                font-size: 2.5rem;
                            }

                            .body_container .body h3 {
                                color: #FFFFFF;
                                font-size: 1.5rem;
                                padding: 10px;
                            }

                            .body_container .body p {
                                color: #FFFFFF;
                                font-size: 1.2rem;
                                padding: 10px;
                            }

                            .body_container .body span {
                                color: #FFFFFF;
                                font-size: 1.2rem;
                                padding: 10px;
                            }

                            .body_container .body .legend {
                                align-items: center;
                                color: #FFFFFF;
                                display: flex;
                                gap: 10px;
                                justify-content: center;
                                padding: 10px;
                            }

                            .body_container .image_container img {
                                background-size: cover;
                                height: 100%;
                                width: 100%;
                            }
                        </style>
                    </head>

                    <body>
                        <div class='body_container'>
                            <div class='title_container'>
                                <h1>Creacion de documento</h1>
                            </div>
                            <div class='body'>
                                <h3>Estimado " . $userName . "</h3>
                                <p>Documento creado exitosamente".$docInfo['documento_tipo'].".</p>

                                <span>Si tienes alguna pregunta o necesitas asistencia adicional, no dudes en contactarme personalmente o
                                    por medio de este correo.</span>

                                <div class='legend'>
                                    <span class='asterisk'>*******************</span>
                                    <span class='asterisk' style='font-weight: bold;'>ESTE CORREO ES SOLO INFORMATIVO</span>
                                    <span class='asterisk'>*******************</span>
                                </div>
                            </div>
                            <div class='image_container'>
                                <img src='cid:header_cid'>
                            </div>
                        </div>
                    </body>

                    </html>";
                    break;
                case "updated":
                    $mail->Body = "
                    <!DOCTYPE html>
                    <html lang='en'>

                    <head>
                        <meta charset='UTF-8'>
                        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                        <title>SGDRH</title>
                        <style>
                            @import url('https://fonts.googleapis.com/css2?family=Oswald:wght@200;300;400;500;600;700&display=swap');

                            * {
                                box-sizing: border-box;
                                font-family: 'Oswald', sans-serif;
                                margin: 0;
                                padding: 0;
                            }

                            html {
                                display: flex;
                                flex-direction: column;
                                height: 100vh;
                                width: 100vw;
                            }

                            .body_container {
                                background: #00A29A;
                                padding: 20px;
                                width: 1200px;
                            }

                            .body_container .title_container {
                                padding: 10px;
                            }

                            .body_container .title_container h1 {
                                color: #FFFFFF;
                                font-size: 2.5rem;
                            }

                            .body_container .body h3 {
                                color: #FFFFFF;
                                font-size: 1.5rem;
                                padding: 10px;
                            }

                            .body_container .body p {
                                color: #FFFFFF;
                                font-size: 1.2rem;
                                padding: 10px;
                            }

                            .body_container .body span {
                                color: #FFFFFF;
                                font-size: 1.2rem;
                                padding: 10px;
                            }

                            .body_container .body .legend {
                                align-items: center;
                                color: #FFFFFF;
                                display: flex;
                                gap: 10px;
                                justify-content: center;
                                padding: 10px;
                            }

                            .body_container .image_container img {
                                background-size: cover;
                                height: 100%;
                                width: 100%;
                            }
                        </style>
                    </head>

                    <body>
                        <div class='body_container'>
                            <div class='title_container'>
                                <h1>Cambio de estatus</h1>
                            </div>
                            <div class='body'>
                                <h3>Estimado " . $user . "</h3>
                                <p>Te informamos que el estatus de tu " . $type . " ha sido actualizado a " . $docStatus . ". Por favor, verifica el cambio del mismo en la plataforma.</p>

                                <span>Si tienes alguna pregunta o necesitas asistencia adicional, no dudes en contactarme personalmente o
                                    por medio de este correo.</span>

                                <div class='legend'>
                                    <span class='asterisk'>*******************</span>
                                    <span class='asterisk' style='font-weight: bold;'>ESTE CORREO ES SOLO INFORMATIVO</span>
                                    <span class='asterisk'>*******************</span>
                                </div>
                            </div>
                            <div class='image_container'>
                                <img src='cid:header_cid'>
                            </div>
                        </div>
                    </body>

                    </html>";
                    break;
                default:
                    break;
            }
            $mail->send();
            echo 'Message has been sent';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            header("Location: admin_home.php?page=dashboard&status=error");
        }
    }

    public function getTotalDiasEconomicosByUser($userID) {
        return $this->documentModel->getTotalDiasEconomicosByUser($userID);
    }
}

class PDF extends FPDF
{

    private $headerTitle = '';

    public function setHeaderTitle($title)
    {
        $this->headerTitle = $title;
    }

    function Header()
    {

        $this->SetXY(2, 0);
        $this->SetfillColor(96, 131, 203);
        $this->Cell(206.5, 10, "", 0, 0, '', true);

        $this->image(PDF_PATH . "images/header.jpg", 0, 10.3, 210);
        $this->SetXY(110, 16);

        $this->SetFont('Arial', 'B', 11);
        $this->Cell(0, 10, utf8_decode($this->headerTitle));

        $this->Ln(50);
    }

    function Footer()
    {
        $this->image(PDF_PATH . "images/footer_1.jpg", 40, 275, 18);
        $this->image(PDF_PATH . "images/footer_3.jpg", 55, 282, 100);
        $this->image(PDF_PATH . "images/footer_2.jpg", 155, 277, 18);
    }

    public function generateDiaEconomico($user_name, $user_puesto, $user_nomina, $user_sindicato, $startDate, $endDate, $permiso, $sindicato_gestor, $jefe_name, $director_name)
    {
        $zapopan = array("Zapopan", date('d'), date('m'), date('Y'));
        $fecha_ingreso = date('d \d\e\l m \d\e\l Y');

        if ($user_sindicato != 1) {

            $this->SetFont('Arial', '', 9);
            $this->SetXY(117, 50);
            $this->Cell(0, 10, utf8_decode($zapopan[0]), 0);
            $this->SetXY(153, 50);
            $this->Cell(0, 10, utf8_decode($zapopan[1]), 0);
            $this->SetXY(167, 50);
            $this->Cell(0, 10, utf8_decode($zapopan[2]), 0);
            $this->SetXY(183, 50);
            $this->Cell(0, 10, utf8_decode($zapopan[3]), 0);
            $this->SetXY(110, 50);
            $this->Cell(0, 10, utf8_decode('_______________, Jalisco a ____ de ______ del _______.'), 0, 1);
            $this->Ln(10);
            $this->SetFont("Arial", "B", 9);
            $this->Cell(0, 10, utf8_decode("DIRECTOR DE UNIDAD ACADÉMICA"));
            $this->SetFont("Arial", "B", 9);
            $this->SetXY(70, 76);
            $this->Cell(0, 10, utf8_decode($user_name));
            $this->SetXY(148, 76);
            $this->Cell(0, 10, utf8_decode($user_puesto));
            $this->SetXY(28, 82);
            $this->Cell(0, 10, utf8_decode($user_nomina));
            $this->SetXY(87, 82);
            $this->Cell(0, 10, utf8_decode("Zapopan"));
            $this->SetXY(37, 94);
            $this->Cell(0, 10, utf8_decode($startDate));
            $this->SetXY(78, 94);
            $this->Cell(0, 10, utf8_decode($endDate));
            $this->SetY(78);
            $this->SetFont("Arial", "", 9);
            $this->MultiCell(0, 6, utf8_decode("Por este medio  el (la) que suscribe____________________________________ con puesto de _____________________  y número de nómina __________ en la Unidad Académica ________________, me permito solicitar  DÍA ECONÓMICO   de conformidad a la Cláusula 65, de las Condiciones Generales de Trabajo, del Instituto Tecnológico José Mario Molina Pasquel y Henríquez, para ausentarme del _______________ hasta el ______________."));
            $this->Ln(10);

            if ($permiso == "permiso-programado") {
                $this->Line(57, 121, 63, 115);
                $this->Line(57, 115, 63, 121);
            } else {
                $this->Line(57, 133, 63, 127);
                $this->Line(57, 127, 63, 133);
            }

            $this->Cell(0, 10, utf8_decode("PERMISO PROGRAMADO"), 0);
            $this->SetXY(55, 113);
            $this->Cell(10, 10, "", 1, 1);
            $this->Ln(3);
            $this->Cell(0, 10, utf8_decode("PERMISO FORTUITO"), 0);
            $this->SetXY(55, 125);
            $this->Cell(10, 10, "", 1);
            $this->SetX(70);
            $this->SetFontSize(8);
            $this->Cell(0, 10, utf8_decode("(Adjuntar evidencia de correo electrónico enviado, para autorizar su regularización.)"), 0, 1);
            $this->SetFontSize(9);
            $this->SetXY(55, 138);
            $this->Cell(0, 10, utf8_decode($fecha_ingreso));
            $this->SetXY(63, 150);
            $this->Cell(0, 10, utf8_decode($user_puesto));
            $this->SetXY(35, 136.5);
            $this->Ln(2);
            $this->Cell(0, 10, utf8_decode("Fecha de ingreso:"));
            $this->Line(45, 145, 115, 145);
            $this->Ln(13);
            $this->Cell(0, 10, utf8_decode("Área actual de desempeño:"));
            $this->Line(55, 157, 125, 157);
            $this->Ln(13);
            $this->Cell(0, 10, utf8_decode("Sin otro particular, me reitero a sus órdenes."), 0, 1);
            $this->SetXY(80, 178);
            $this->Cell(0, 10, utf8_decode($user_name));
            $this->SetXY(80, 198);
            $this->Cell(0, 10, utf8_decode($sindicato_gestor));
            $this->SetXY(80, 218);
            $this->Cell(0, 10, utf8_decode($jefe_name));
            $this->SetXY(80, 240);
            $this->Cell(0, 10, utf8_decode($director_name));
            $this->Line(70, 185, 140, 185);
            $this->SetXY(90, 185);
            $this->MultiCell(30, 4, utf8_decode("Nombre y Firma\nSolicitante"), 0, 'C');
            $this->Line(70, 205, 140, 205);
            $this->SetXY(90, 205);
            $this->MultiCell(30, 4, utf8_decode("Nombre y Firma\nSindicato Gestor"), 0, 'C');
            $this->Line(70, 225, 140, 225);
            $this->SetXY(84.5, 225);
            $this->MultiCell(40, 4, utf8_decode("Nombre y firma\nVo.Bo. Jefe (a) Inmediato"), 0, 'C');
            $this->Line(70, 247, 140, 247);
            $this->SetXY(60, 247);
            $this->Cell(95, 4, utf8_decode("Autorizó: Nombre y Firma del Director (a) de Unidad Académica"), 0, 'C');
        } else {

            $this->SetFont('Arial', '', 9);
            $this->SetXY(117, 50);
            $this->Cell(0, 10, utf8_decode($zapopan[0]), 0);
            $this->SetXY(153, 50);
            $this->Cell(0, 10, utf8_decode($zapopan[1]), 0);
            $this->SetXY(167, 50);
            $this->Cell(0, 10, utf8_decode($zapopan[2]), 0);
            $this->SetXY(183, 50);
            $this->Cell(0, 10, utf8_decode($zapopan[3]), 0);
            $this->SetXY(110, 50);
            $this->Cell(0, 10, utf8_decode('_______________, Jalisco a ____ de ______ del _______.'), 0, 1);
            $this->Ln(10);
            $this->SetFont("Arial", "B", 9);
            $this->Cell(0, 10, utf8_decode("DIRECTOR DE UNIDAD ACADÉMICA"));
            $this->SetFont("Arial", "B", 9);
            $this->SetXY(70, 76);
            $this->Cell(0, 10, utf8_decode($user_name));
            $this->SetXY(148, 76);
            $this->Cell(0, 10, utf8_decode($user_puesto));
            $this->SetXY(28, 82);
            $this->Cell(0, 10, utf8_decode($user_nomina));
            $this->SetXY(87, 82);
            $this->Cell(0, 10, utf8_decode("Zapopan"));
            $this->SetXY(37, 94);
            $this->Cell(0, 10, utf8_decode($startDate));
            $this->SetXY(78, 94);
            $this->Cell(0, 10, utf8_decode($endDate));
            $this->SetY(78);
            $this->SetFont("Arial", "", 9);
            $this->MultiCell(0, 6, utf8_decode("Por este medio  el (la) que suscribe____________________________________ con puesto de _____________________  y número de nómina __________ en la Unidad Académica ________________, me permito solicitar  DÍA ECONÓMICO   de conformidad a la Cláusula 65, de las Condiciones Generales de Trabajo, del Instituto Tecnológico José Mario Molina Pasquel y Henríquez, para ausentarme del ______________ hasta el ______________."));
            $this->Ln(10);

            if ($permiso == "permiso-programado") {
                $this->Line(57, 121, 63, 115);
                $this->Line(57, 115, 63, 121);
            } else {
                $this->Line(57, 133, 63, 127);
                $this->Line(57, 127, 63, 133);
            }

            $this->Cell(0, 10, utf8_decode("PERMISO PROGRAMADO"), 0);
            $this->SetXY(55, 113);
            $this->Cell(10, 10, "", 1, 1);
            $this->Ln(3);
            $this->Cell(0, 10, utf8_decode("PERMISO FORTUITO"), 0);
            $this->SetXY(55, 125);
            $this->Cell(10, 10, "", 1);
            $this->SetX(70);
            $this->SetFontSize(8);
            $this->Cell(0, 10, utf8_decode("(Adjuntar evidencia de correo electrónico enviado, para autorizar su regularización.)"), 0, 1);
            $this->SetFontSize(9);
            $this->SetXY(55, 148);
            $this->Cell(0, 10, utf8_decode($fecha_ingreso));
            $this->SetXY(63, 160);
            $this->Cell(0, 10, utf8_decode($user_puesto));
            $this->SetXY(35, 146.5);
            $this->Ln(2);
            $this->Cell(0, 10, utf8_decode("Fecha de ingreso:"));
            $this->Line(45, 145, 115, 145);
            $this->Ln(13);
            $this->Cell(0, 10, utf8_decode("Área actual de desempeño:"));
            $this->Line(55, 157, 125, 157);
            $this->Ln(13);
            $this->Cell(0, 10, utf8_decode("Sin otro particular, me reitero a sus órdenes."), 0, 1);
            $this->SetXY(80, 183);
            $this->Cell(0, 10, utf8_decode($user_name));
            $this->SetXY(80, 208);
            $this->Cell(0, 10, utf8_decode($jefe_name));
            $this->SetXY(80, 235);
            $this->Cell(0, 10, utf8_decode($director_name));
            $this->Line(70, 190, 140, 190);
            $this->SetXY(90, 190);
            $this->MultiCell(30, 4, utf8_decode("Nombre y Firma\nSolicitante"), 0, 'C');
            $this->Line(70, 215, 140, 215);
            $this->SetXY(84.5, 215);
            $this->MultiCell(40, 4, utf8_decode("Nombre y firma\nVo.Bo. Jefe (a) Inmediato"), 0, 'C');
            $this->Line(70, 242, 140, 242);
            $this->SetXY(60, 242);
            $this->MultiCell(95, 4, utf8_decode("Nombre y Firma del Director (a) de Unidad Académica"), 0, 'C');
        }
    }

    public function generateDiaCumple($user_name, $user_puesto, $user_nomina, $user_sindicato, $user_cumple, $sindicato_gestor, $jefe_name, $director_name, $fecha_ingreso, $dayOption)
    {

        $zapopan = array("Zapopan", date('d'), date('m'), date('Y'));

        if ($user_sindicato != 1) {
            $this->SetFont('Arial', '', 9);
            $this->SetXY(117, 50);
            $this->Cell(0, 10, utf8_decode($zapopan[0]), 0);
            $this->SetXY(153, 50);
            $this->Cell(0, 10, utf8_decode($zapopan[1]), 0);
            $this->SetXY(167, 50);
            $this->Cell(0, 10, utf8_decode($zapopan[2]), 0);
            $this->SetXY(183, 50);
            $this->Cell(0, 10, utf8_decode($zapopan[3]), 0);
            $this->SetXY(110, 50);
            $this->Cell(0, 10, utf8_decode('_______________, Jalisco a ____ de ______ del _______.'), 0, 1);
            $this->Ln(10);
            $this->SetFont("Arial", "B", 9);
            $this->Cell(0, 10, utf8_decode("DIRECTOR(A) DE UNIDAD ACADÉMICA"));
            $this->SetFont("Arial", "B", 9);
            $this->SetXY(135, 76);
            $this->Cell(0, 10, utf8_decode($user_name));
            $this->SetXY(35, 82);
            $this->Cell(0, 10, utf8_decode($user_puesto));
            $this->SetXY(103, 82);
            $this->Cell(0, 10, utf8_decode($user_nomina));
            $this->SetXY(165, 82);
            $this->Cell(0, 10, utf8_decode("Zapopan"));
            $this->SetY(78);
            $this->SetFont("Arial", "", 9);
            $this->MultiCell(0, 6, utf8_decode("Por este medio y de la mejor manera el (la) que suscribe trabajador (a) solicitante       ____________________________________ con puesto de ______________________ y número de nómina ___________, de la Unidad Académica _____________________, solicito DÍA DE CUMPLEAÑOS de conformidad a la Cláusula 54, párrafo primero de las Condiciones Generales de Trabajo del Instituto Tecnológico José Mario Molina Pasquel y Henríquez"), 0, "L");
            $this->Ln(10);
            $this->SetXY(55, 111);
            $this->Cell(0, 10, utf8_decode($fecha_ingreso));
            $this->SetXY(58, 124);


            $fechaActual = date('Y'); // Año actual
            $fechaOriginal = date_create($user_cumple); // Convierte string a objeto DateTime
            $fechaNueva = $fechaActual . date('-m-d', strtotime($user_cumple)); // Reemplaza solo el año

            $this->Cell(0, 10, utf8_decode($fechaNueva));
            $this->SetXY(0, 111);



            //  $this->Cell(0, 10, utf8_decode($user_cumple));
          //  $this->SetXY(0, 111);



            $this->Ln(1);
            $this->SetFontSize(10);
            $this->Cell(0, 10, utf8_decode("Fecha de ingreso:"));
            $this->Line(45, 118, 115, 118);
            $this->Ln(13);
            $this->Cell(0, 10, utf8_decode("Fecha de Cumpleaños:"));
            $this->Line(50, 131, 120, 131);
            $this->Ln(13);
            $this->SetXY(10, 140);
            $this->MultiCell(0, 8, utf8_decode("OBSERVACIÓN: La fecha de cumpleaños será día inhábil, motivo por el cual el día se cambia al día hábil\n siguiente" . (($dayOption === "after") ? '____X____' : '_________')  .  " día hábil anterior " . (($dayOption === "before") ? '____X____' : '_________')), 1);
            $this->Ln(1);
            $this->Ln(1);
            $this->SetFontSize(9);
            $this->Cell(0, 5, utf8_decode("Sin otro particular, me reitero a sus órdenes."));
            $this->SetXY(80, 183);
            $this->Cell(0, 10, utf8_decode($user_name));
            $this->SetXY(80, 208);
            $this->Cell(0, 10, utf8_decode($jefe_name));
            $this->SetXY(80, 235);
            $this->Cell(0, 10, utf8_decode($director_name));
            $this->Line(70, 190, 140, 190);
            $this->SetXY(90, 190);
            $this->MultiCell(30, 4, utf8_decode("Nombre y Firma\nSolicitante"), 0, 'C');
            $this->Line(70, 215, 140, 215);
            $this->SetXY(84.5, 215);
            $this->MultiCell(40, 4, utf8_decode("Nombre y firma\nVo.Bo. Jefe (a) Inmediato"), 0, 'C');
            $this->Line(70, 242, 140, 242);
            $this->SetXY(60, 242);
            $this->MultiCell(95, 4, utf8_decode("Nombre y Firma \nAutorizó: Director (a) de Unidad Académica"), 0, 'C');
            $this->Ln(5);
            $this->SetFontSize(6);
            $this->Cell(0, 4, utf8_decode("Original, Capital Humano UA"), 0, 'L');
        } else {
            $this->SetFont('Arial', '', 9);
            $this->SetXY(117, 50);
            $this->Cell(0, 10, utf8_decode($zapopan[0]), 0);
            $this->SetXY(153, 50);
            $this->Cell(0, 10, utf8_decode($zapopan[1]), 0);
            $this->SetXY(167, 50);
            $this->Cell(0, 10, utf8_decode($zapopan[2]), 0);
            $this->SetXY(183, 50);
            $this->Cell(0, 10, utf8_decode($zapopan[3]), 0);
            $this->SetXY(110, 50);
            $this->Cell(0, 10, utf8_decode('_______________, Jalisco a ____ de ______ del _______.'), 0, 1);
            $this->Ln(10);
            $this->SetFont("Arial", "B", 9);
            $this->Cell(0, 10, utf8_decode("DIRECTOR(A) DE UNIDAD ACADÉMICA"));
            $this->SetFont("Arial", "B", 9);
            $this->SetXY(135, 76);
            $this->Cell(0, 10, utf8_decode($user_name));
            $this->SetXY(35, 82);
            $this->Cell(0, 10, utf8_decode($user_puesto));
            $this->SetXY(103, 82);
            $this->Cell(0, 10, utf8_decode($user_nomina));
            $this->SetXY(165, 82);
            $this->Cell(0, 10, utf8_decode("Zapopan"));
            $this->SetY(78);
            $this->SetFont("Arial", "", 9);
            $this->MultiCell(0, 6, utf8_decode("Por este medio y de la mejor manera el (la) que suscribe trabajador (a) solicitante       ____________________________________ con puesto de ______________________ y número de nómina ___________, de la Unidad Académica _____________________, solicito DÍA DE CUMPLEAÑOS de conformidad a la Cláusula 54, párrafo primero de las Condiciones Generales de Trabajo del Instituto Tecnológico José Mario Molina Pasquel y Henríquez"), 0, "L");
            $this->Ln(10);
            $this->SetXY(55, 111);
            $this->Cell(0, 10, utf8_decode($fecha_ingreso));
            $this->SetXY(58, 124);
            $this->Cell(0, 10, utf8_decode($user_cumple));
            $this->SetXY(0, 111);
            $this->Ln(1);
            $this->SetFontSize(10);
            $this->Cell(0, 10, utf8_decode("Fecha de ingreso:"));
            $this->Line(45, 118, 115, 118);
            $this->Ln(13);
            $this->Cell(0, 10, utf8_decode("Fecha de Cumpleaños:"));
            $this->Line(50, 131, 120, 131);
            $this->Ln(13);
            $this->SetXY(10, 140);
            $this->MultiCell(0, 8, utf8_decode("OBSERVACIÓN: La fecha de cumpleaños será día inhábil, motivo por el cual el día se cambia al día hábil\n siguiente" . (($dayOption === "after") ? '____X____' : '_________')  .  " día hábil anterior " . (($dayOption === "before") ? '____X____' : '_________')), 1);
            $this->Ln(1);
            $this->Ln(1);
            $this->SetFontSize(9);
            $this->Cell(0, 5, utf8_decode("Sin otro particular, me reitero a sus órdenes."));
            $this->SetXY(80, 183);
            $this->Cell(0, 10, utf8_decode($user_name));
            $this->SetXY(80, 208);
            $this->Cell(0, 10, utf8_decode($jefe_name));
            $this->SetXY(80, 235);
            $this->Cell(0, 10, utf8_decode($director_name));
            $this->Line(70, 190, 140, 190);
            $this->SetXY(90, 190);
            $this->MultiCell(30, 4, utf8_decode("Nombre y Firma\nSolicitante"), 0, 'C');
            $this->Line(70, 215, 140, 215);
            $this->SetXY(84.5, 215);
            $this->MultiCell(40, 4, utf8_decode("Nombre y firma\nVo.Bo. Jefe (a) Inmediato"), 0, 'C');
            $this->Line(70, 242, 140, 242);
            $this->SetXY(60, 242);
            $this->MultiCell(95, 4, utf8_decode("Nombre y Firma \nAutorizó: Director (a) de Unidad Académica"), 0, 'C');
            $this->Ln(5);
            $this->SetFontSize(6);
            $this->Cell(0, 4, utf8_decode("Original, Capital Humano UA"), 0, 'L');
        }
    }

    public function generateReporteIncidencia($user_name, $user_nomina, $user_adscripcion, $jefe_name, $director_name, $incidencia, $date, $motivo)
    {

        $zapopan = array("Zapopan", date('d'), date('m'), date('Y'));
        $dia = explode("-", $date);

        $año = $dia[0];
        $mes = $dia[1];
        $dia = $dia[2];

        $this->SetFont('Arial', '', 9);
        $this->SetXY(132, 50);
        $this->Cell(0, 10, utf8_decode($zapopan[1]), 0);
        $this->SetXY(160, 50);
        $this->Cell(0, 10, utf8_decode($zapopan[2]), 0);
        $this->SetXY(120, 50);
        $this->Cell(0, 10, utf8_decode('Fecha: ____ de _____________________ de ' . $zapopan[3]), 0, 1);
        $this->SetXY(70, 85);
        $this->Cell(0, 5, utf8_decode($user_name));
        $this->SetXY(55, 100);
        $this->Cell(0, 5, utf8_decode($user_nomina));
        $this->SetXY(112, 100);
        $this->Cell(0, 5, utf8_decode($user_adscripcion));
        $this->Ln(10);
        $this->SetXY(20, 65);
        $this->Cell(0, 10, utf8_decode('NOMBRE DEL RESPONSABLE DE CAPITAL HUMANO DE LA UNIDAD ACADÉMICA'), 0, 1);
        $this->Cell(0, 190, utf8_decode(''), 1);
        $this->Ln(10);
        $this->SetFont("Arial", "B", 11);
        $this->Cell(0, 5, utf8_decode('Nombre del Colaborador (a): ________________________________________________'), 0, 1);
        $this->Ln(10);
        $this->Cell(0, 5, utf8_decode('Código Empleado (a) : ___________       Adscripción: ___________________________'), 0, 1);
        $this->Ln(10);
        $this->Cell(0, 5, utf8_decode('TIPO DE INCIDENCIA'), 1, 1, "C");
        if ($incidencia == "salida-anticipada") {
            $this->Line(48, 127, 54, 133);
            $this->Line(48, 133, 54, 127);
        } elseif ($incidencia == "retardo") {
            $this->Line(84, 127, 90, 133);
            $this->Line(84, 133, 90, 127);
        } elseif ($incidencia == "omision-entrada") {
            $this->Line(134, 127, 140, 133);
            $this->Line(134, 133, 140, 127);
        } elseif ($incidencia == "omision-salida") {
            $this->Line(182, 127, 188, 133);
            $this->Line(182, 133, 188, 127);
        }
        $this->SetXY(10, 130);
        $this->Cell(0, 5, utf8_decode('Salida Anticipada'), 0);
        $this->SetXY(46, 125);
        $this->Cell(10, 10, utf8_decode(''), 1);
        $this->SetXY(64, 130);
        $this->Cell(0, 5, utf8_decode('Retardo'), 0);
        $this->SetXY(82, 125);
        $this->Cell(10, 10, utf8_decode(''), 1);
        $this->SetXY(98, 130);
        $this->Cell(0, 5, utf8_decode('Omisión Entrada'), 0);
        $this->SetXY(132, 125);
        $this->Cell(10, 10, utf8_decode(''), 1);
        $this->SetXY(150, 130);
        $this->Cell(0, 5, utf8_decode('Omisión Salida'), 0);
        $this->SetXY(180, 125);
        $this->Cell(10, 10, utf8_decode(''), 1);
        $this->SetXY(25, 145);
        $this->Cell(0, 5, utf8_decode($dia), 0, 1);
        $this->SetXY(45, 145);
        $this->Cell(0, 5, utf8_decode($mes), 0, 1);
        $this->SetXY(75, 145);
        $this->Cell(0, 5, utf8_decode($año), 0, 1);
        $this->SetXY(10, 145);
        $this->Cell(0, 5, utf8_decode('El día: _____ de _______ del año ________  '), 0, 1);
        $this->Ln(13);
        $this->Cell(0, 5, utf8_decode('Motivo: ' . $motivo), 0, 1);
        $this->SetFont("Arial", "", 11);
        $this->SetXY(15, 205);
        $this->Cell(0, 5, utf8_decode($user_name), 0, 1);
        $this->SetXY(120, 205);
        $this->Cell(0, 5, utf8_decode($jefe_name), 0, 1);
        $this->SetXY(65, 245);
        $this->Cell(0, 5, utf8_decode($director_name), 0, 1);
        $this->SetXY(30, 175);
        $this->Cell(0, 5, utf8_decode('Solicitante:'), 0, 1);
        $this->Line(15, 210, 90, 210);
        $this->SetXY(110, 175);
        $this->MultiCell(0, 5, utf8_decode("Vo.Bo.\n Jefe(a) Inmediato"), 0, "C");
        $this->Line(120, 210, 195, 210);
        $this->SetXY(90, 220);
        $this->Cell(0, 5, utf8_decode("Autorizó"), 0, "C");
        $this->Line(65, 250, 135, 250);
        $this->SetXY(63, 252);
        $this->Cell(0, 5, utf8_decode("DIRECTOR (A) DE UNIDAD ACADÉMICA"), 0, "C");
    }
}
