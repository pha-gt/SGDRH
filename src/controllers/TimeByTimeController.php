<?php

require_once UTIL_PATH . 'Session.php';
require_once MODEL_PATH . "TimebyTimeModel.php";
class TimeByTimeController
{
    private $TimeByTimeModel;

    public function __construct($db)
    {
        $this->TimeByTimeModel = new TimeByTimeModel($db);
    }

    public function showTimeByTime($role, $userID)
    {
        $registros = $this->TimeByTimeModel->getAllRegistros($role, $userID);
        require VIEW_PATH . 'TimeByTime/list.php';
    }

    public function lastRegistro()
    {   
        $registros = $this->TimeByTimeModel->getLastRegistro();
        $lastFolio = isset($registros['folio']) ? (int)$registros['folio'] : 0;
        $lastFolio++; // Incrementar el folio para el nuevo registro
        return $lastFolio;
    }

    public function generarRegistro($data)
    {
        $user_ID = isset($data["usuario_id"]) && !empty($data["usuario_id"]) && is_numeric($data["usuario_id"]) ? intval($data["usuario_id"]) : null;
        $num_registros = isset($data["num_registros"]) && !empty($data["num_registros"]) && is_numeric($data["num_registros"]) ? intval($data["num_registros"]) : null;
        $folio = isset($data["folio"]) && !empty($data["folio"]) && is_string($data["folio"]) ? trim($data["folio"]) : null;
        $fechaR = isset($data["fechaR"]) && !empty($data["fechaR"]) && is_string($data["fechaR"]) ? trim($data["fechaR"]) : null;

        // Arrays - verifica que sean arrays y no estén vacíos
        $fechasF = isset($data["fechaF"]) && !empty($data["fechaF"]) && is_array($data["fechaF"]) ? $data["fechaF"] : null;
        $horasF = isset($data["horasF"]) && !empty($data["horasF"]) && is_array($data["horasF"]) ? $data["horasF"] : null;
        $fechasP = isset($data["fechaP"]) && !empty($data["fechaP"]) && is_array($data["fechaP"]) ? $data["fechaP"] : null;
        $horasP = isset($data["horasP"]) && !empty($data["horasP"]) && is_array($data["horasP"]) ? $data["horasP"] : null;

        if ($user_ID === null ) {
            Session::set('document_warning', "Error al generar el registro. el campo usuario es obligatorio");
            echo "<script>$(location).attr('href', 'admin_home.php?page=TimeByTime');</script>";
            exit;
        }else if ($num_registros === null || $folio === null || $fechaR === null){
            Session::set('document_warning', "Error al generar el registro. Los campos folio, fecha y numero de registros son obligatorios.");
            echo "<script>$(location).attr('href', 'admin_home.php?page=TimeByTime');</script>";
            exit;
        }
        foreach (['fechas_de_falta' => $fechasF, 'fechas_de_pago' => $fechasP] as $nombreCampo => $arrayFechas) {
            if ($arrayFechas !== null) {
                foreach ($arrayFechas as $fecha) {
                    if (!DateTime::createFromFormat('Y-m-d', $fecha)) {
                        Session::set('document_warning', "Fecha inválida en {$nombreCampo}: {$fecha}");
                        echo "<script>$(location).attr('href', 'admin_home.php?page=TimeByTime');</script>";
                        exit;
                    }
                }
                
                // Validación adicional para fechas duplicadas (opcional)
                if (count($arrayFechas) !== count(array_unique($arrayFechas))) {
                    Session::set('document_warning', "El campo {$nombreCampo} contiene fechas duplicadas");
                    echo "<script>$(location).attr('href', 'admin_home.php?page=TimeByTime');</script>";
                    exit;
                }
            }
        }

        foreach (['horas_de_falta' => $horasF, 'horas_de_pago' => $horasP] as $nombreCampo => $arrayHoras) {
            if ($arrayHoras !== null) {
                foreach ($arrayHoras as $hora) {
                    if (!is_numeric($hora)) {
                        Session::set('document_warning', "Hora inválida en {$nombreCampo}: {$hora}");
                        echo "<script>$(location).attr('href', 'admin_home.php?page=TimeByTime');</script>";
                        exit;
                    }
                }
            }
        }
        
        
        if ($this->TimeByTimeModel->getValidationFolio($folio)) {
            Session::set('document_warning', 'El folio ingresado ya existe en la base de datos.');
            echo "<script>$(location).attr('href', 'admin_home.php?page=TimeByTime');</script>";
            exit;
        }
        // Validación: la suma de horas finales debe coincidir con la suma de horas programadas
        $sumaHorasF = array_sum($horasF);
        $sumaHorasP = array_sum($horasP);

        if ($sumaHorasF !== $sumaHorasP) {
            Session::set('document_warning', "La suma de las horas de asusencia {$sumaHorasF} debe coincidir con la suma de las horas a pagar {$sumaHorasP}.");
            echo "<script>$(location).attr('href', 'admin_home.php?page=TimeByTime');</script>";
            exit;
        }
        $allFechas = array_merge($fechasF, $fechasP);
        $fechasDatabase = $this->TimeByTimeModel->getAllFechasUsuario($user_ID);
        $compruebaFechas = array_intersect($allFechas, $fechasDatabase);
        //print_r($compruebaFechas);exit;
        if (!empty($compruebaFechas)) {
            Session::set('document_warning', 'Error: las fechas ya existen en la base de datos.');
            echo "<script>$(location).attr('href', 'admin_home.php?page=TimeByTime');</script>";
            exit;
        }
        if ($this->TimeByTimeModel->createRegistro(
            $user_ID, $folio, $fechaR, $num_registros, $fechasF, $horasF, $fechasP, $horasP)) {
            Session::set('document_success', 'Registro generado correctamente.');
        } else {
            Session::set('document_warning', 'Error al generar el registro, por favor intente nuevamente.');
        }

        echo "<script>$(location).attr('href', 'admin_home.php?page=TimeByTime');</script>";
    }

    public function updateTimebyTimePagos($data)
    {
        $oldFechasFalta = [];
        $oldHorasFalta = [];
        $oldFechasPago = [];
        $oldHorasPago = [];
        $oldEstatusPago = [];

        $docID = isset($data['docID']) && !empty($data['docID']) ? intval($data['docID']) : null;
        $userID = isset($data['userID']) && !empty($data['userID']) ? intval($data['userID']) : null;
        $newFechasFalta = isset($data["fechasF"]) && !empty($data["fechasF"]) ? $data["fechasF"] : null;
        $newHorasFalta = isset($data["horasF"]) && !empty($data["horasF"]) ? $data["horasF"] : null;
        $newFechasPago = isset($data["fechasP"]) && !empty($data["fechasP"]) ? $data["fechasP"] : null;
        $newHorasPago = isset($data["horasP"]) && !empty($data["horasP"]) ? $data["horasP"] : null;
        
        if ($docID === null) {
            Session::set('document_warning', 'Error al modificar el registro. No se ha encontrado el registro.');
            echo "<script>$(location).attr('href', 'admin_home.php?page=TimeByTime');</script>";
            exit;
        }
        
        foreach ($data as $key => $value) {
            
            if (strpos($key, 'fechaF_') === 0) {
                $pagoID = str_replace('fechaF_', '', $key);
                $oldFechasFalta[$pagoID] = $value;
            }
            if (strpos($key, 'horasF_') === 0) {
                $fechaID = str_replace('horasF_', '', $key);
                $oldHorasFalta[$fechaID] = intval($value);
            }
            
            if (strpos($key, 'fechaP_') === 0) {
                $fechaID = str_replace('fechaP_', '', $key);
                $oldFechasPago[$fechaID] = $value;
            }
            if (strpos($key, 'horaP_') === 0) {
                $pagoID = str_replace('horaP_', '', $key);
                $oldHorasPago[$pagoID] = intval($value);
            }

            if (strpos($key, 'estatusP_') === 0) {
                $pagoID = str_replace('estatusP_', '', $key);
                $oldEstatusPago[$pagoID] = intval($value);
            }
        }
        //validaciones cuando se agregan y  se modifican los valores
        if (is_array($oldFechasFalta) && is_array($newFechasFalta)) {
            //unir en un solo array los valores de fechasFalta y fechasPago cuando se agregan nuevos valores
            $totalFechasFalta = array_merge($oldFechasFalta, $newFechasFalta);
            $totalFechasPago = array_merge($oldFechasPago, $newFechasPago);
            $allFechas= array_merge($totalFechasFalta, $totalFechasPago);
            //comprobar si las fechas nuevas y/o modificadas ya existen en el base de datos
            $fechasDatabase = $this->TimeByTimeModel->getAllFechasUsuario($userID, $docID);
            $compruebaFechas = array_intersect($allFechas, $fechasDatabase);
            //print_r($compruebaFechas);exit;
            if (!empty($compruebaFechas)) {
                Session::set('document_warning', "Error: las fechas nuevas ingresadas o modificadas en el registro  con folio: {$docID} ya existen en la base de datos.");
                echo "<script>$(location).attr('href', 'admin_home.php?page=TimeByTime');</script>";
                exit;
            }
            //comprobar si las fechas ingresadas no sean iguales entre si mismas
            $duplicateFechasFalta = array_intersect($newFechasFalta, $oldFechasFalta);
            $duplicateFechasPago = array_intersect($newFechasPago, $oldFechasPago);
            if (!empty($duplicateFechasFalta )) {
                Session::set('document_warning', 'No puedes ingresar mas de una vez la misma fecha de falta.');
                echo "<script>$(location).attr('href', 'admin_home.php?page=TimeByTime');</script>";
                exit;
            }else if (!empty($duplicateFechasPago)) {
                Session::set('document_warning', 'No puedes ingresar mas de una vez la misma fecha de pago.');
                echo "<script>$(location).attr('href', 'admin_home.php?page=TimeByTime');</script>";
                exit;
            }   
        } 
        //validaciones cuando solo se modifican los valores
        else{
            //unir en un solo array los valores de fechasFalta y fechasPago cuando solo se mofican los valores
            $totalFechasFalta = array_merge($oldFechasFalta, (array) $newFechasFalta);
            $totalFechasPago = array_merge($oldFechasPago, (array) $newFechasPago);
            $allFechas1= array_merge($totalFechasFalta, $totalFechasPago);
            //comprobar que las fehas modificadas no existan en la base de datos
            $fechasDatabase = $this->TimeByTimeModel->getAllFechasUsuario($userID, $docID);
            $compruebaFechas = array_intersect($allFechas1, $fechasDatabase);
            if (!empty($compruebaFechas)) {
                Session::set('document_warning', "Error: las fechas modificadas en el registro: {$docID} ya existen en la base de datos. Verficar registros");
                echo "<script>$(location).attr('href', 'admin_home.php?page=TimeByTime');</script>";
                exit;
            }
            //comprobar si las fechas ingresadas no sean iguales entre si mismas
            $duplicateFechasFalta = array_intersect( (array) $newFechasFalta, $oldFechasFalta);
            $duplicateFechasPago = array_intersect( (array) $newFechasPago, $oldFechasPago);
            if (!empty($duplicateFechasFalta )) {
                Session::set('document_warning', 'No puedes ingresar mas de una vez la misma fecha de falta.');
                echo "<script>$(location).attr('href', 'admin_home.php?page=TimeByTime');</script>";
                exit;
            }else if (!empty($duplicateFechasPago)) {
                Session::set('document_warning', 'No puedes ingresar mas de una vez la misma fecha de pago.');
                echo "<script>$(location).attr('href', 'admin_home.php?page=TimeByTime');</script>";
                exit;
            }
        }

        if ($this->TimeByTimeModel->updateEstatusTimebyTimePagos($docID, 
        $newFechasFalta, $newHorasFalta, $newFechasPago, $newHorasPago, 
        $oldFechasFalta, $oldHorasFalta, $oldFechasPago, $oldHorasPago, $oldEstatusPago)) {
            Session::set('document_success', 'Registro modificado correctamente.');
        } else {
            Session::set('document_warning', 'Error al modificar el registro, por favor intente nuevamente.');
        }

        echo "<script>$(location).attr('href', 'admin_home.php?page=TimeByTime');</script>";
    }

    public function uploadFile($data, $dataFile)
    {
        $docID = isset($data['docID']) ? intval($data['docID']) : null; 
        $file = isset($dataFile['archivo']) && !empty($dataFile['archivo']['tmp_name']) ? $dataFile['archivo'] : null;
        $estatus = 'entregado';

        if ($docID === null || $file === null) {
            Session::set('document_warning', 'Error al subir el archivo. No se ha subido ningun archivo.');
            echo "<script>$(location).attr('href', 'admin_home.php?page=TimeByTime');</script>";
            exit;
        }


        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);  
        $mimeType = finfo_file($fileInfo, $file['tmp_name']);
        finfo_close($fileInfo);
        // Validar que sea un archivo PDF
        if ($extension !== 'pdf' || $mimeType !== 'application/pdf') {
            Session::set('document_warning', 'Error: el archivo no es un PDF válido.');
            echo "<script>$(location).attr('href', 'admin_home.php?page=TimeByTime');</script>";
            exit;
        } else{
            $fileData = file_get_contents($file['tmp_name']);
            if ($fileData === false) {
                Session::set('document_warning', 'Error al leer el archivo.');
                echo "<script>$(location).attr('href', 'admin_home.php?page=TimeByTime');</script>";
                exit;
            }
        }
        
        if ($this->TimeByTimeModel->UpdateUploadFile($docID, $fileData, $estatus)) {
            Session::set('document_success', 'Archivo subido correctamente.');
        } else {
            Session::set('document_warning', 'Error al subir el archivo, por favor intente nuevamente.');
        }

        echo "<script>$(location).attr('href', 'admin_home.php?page=TimeByTime');</script>";
    } 
    public function downloadFile($docID)
    {   
        if ($archivo = $this->TimeByTimeModel->getDownloadFile($docID)) {
            return $archivo;
        } else {
            Session::set('document_warning', 'Archivo no encontrado.');
        }
        echo "<script>$(location).attr('href', 'admin_home.php?page=TimeByTime');</script>";
    }

    public function deleteLogical($data)
    {   
        $registro = isset($data['id']) && !empty($data['id']) ? intval($data['id']) : null;
        if ($registro == null) {
            Session::set('document_warning', 'Error al eliminar el archivo. No se ha encontrado el registro.');
            echo "<script>$(location).attr('href', 'admin_home.php?page=TimeByTime');</script>";
            exit;
        }else if ($this->TimeByTimeModel->updateDeleteLogical($registro)) {
            Session::set('document_success', 'Archivo eliminado correctamente.');
        } else {
            Session::set('document_warning', 'Error al eliminar el archivo, por favor intente nuevamente.');
        }
        echo "<script>$(location).attr('href', 'admin_home.php?page=TimeByTime');</script>";
    }
}
