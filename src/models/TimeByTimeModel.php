<?php

class TimeByTimeModel
{
    private $db;

    public function __construct($db)
    { 
        $this->db = $db;
    }

    public function getAllRegistros($role, $userID)
    {
        try {
            $query = "SELECT 
                        timebytime.*, 
                        usuario.*,
                        (SELECT COUNT(*) FROM timebytimepagos WHERE timebytimepagos.timebytime_id = timebytime.id AND estatusP = 0) AS incidencia
                      FROM timebytime 
                      LEFT JOIN usuario ON usuario.usuario_id = timebytime.usuario_id
            ";
        
            if ($role == 3) {
                $query .= " WHERE timebytime.usuario_id = :userID";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
            } else if ($role == 4) {
                $area_adscripcion = $_SESSION['user_area'];
                $query .= " WHERE usuario.areaAdscripcion_id = :areaAdscripcion";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':areaAdscripcion', $area_adscripcion, PDO::PARAM_INT);
            } else if ($role == 5) {
                $user_sindicato = $_SESSION['user_union'];
                $query .= " WHERE usuario.sindicato_id = :userSindicato";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':userSindicato', $user_sindicato, PDO::PARAM_STR);
            } else {
                $stmt = $this->db->prepare($query);
            }
            $query .= " ORDER BY timebytime.folio ASC";
        
            if (!$stmt->execute()) {
                $errorInfo = $stmt->errorInfo();
                error_log("Error al ejecutar la consulta: " . implode(", ", $errorInfo));
                return false;
            }

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error en getAllRegistros: " . $e->getMessage());
            return false;
        }       
    }

    public function getLastRegistro()
    {
        try {
            $query = "SELECT * FROM timebytime ORDER BY id DESC LIMIT 1";
            $stmt = $this->db->prepare($query);

            if (!$stmt->execute()) {
                $errorInfo = $stmt->errorInfo();
                error_log("Error al ejecutar la consulta: " . implode(", ", $errorInfo));
                return false;
            }
            
            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error en getLastRegistro: " . $e->getMessage());
            return false;
        }
    }

    public function getRegistroById($id)
    {
        try {
            $queryMain = "SELECT timebytime.*, usuario.*
                        FROM timebytime
                        LEFT JOIN usuario ON timebytime.usuario_id = usuario.usuario_id
                        WHERE timebytime.id = :id";
            $stmtMain = $this->db->prepare($queryMain);
            $stmtMain->bindParam(':id', $id, PDO::PARAM_INT);
            
            if (!$stmtMain->execute()) {
                $errorInfo = $stmtMain->errorInfo();
                error_log("Error al ejecutar la consulta: " . implode(", ", $errorInfo));
                return false;
            }
            $registro = $stmtMain->fetch(PDO::FETCH_ASSOC);
        
            $queryFaltas = "SELECT * FROM timebytimefaltas WHERE timebytime_id = :id";
            $stmtFaltas = $this->db->prepare($queryFaltas);
            $stmtFaltas->bindParam(':id', $id, PDO::PARAM_INT);
            
            if (!$stmtFaltas->execute()) {
                $errorInfo = $stmtFaltas->errorInfo();
                error_log("Error al ejecutar la consulta: " . implode(", ", $errorInfo));
                return false;
            }
            $faltas = $stmtFaltas->fetchAll(PDO::FETCH_ASSOC);
        
            $queryPagos = "SELECT * FROM timebytimepagos WHERE timebytime_id = :id";
            $stmtPagos = $this->db->prepare($queryPagos);
            $stmtPagos->bindParam(':id', $id, PDO::PARAM_INT);
            
            if (!$stmtPagos->execute()) {
                $errorInfo = $stmtPagos->errorInfo();
                error_log("Error al ejecutar la consulta: " . implode(", ", $errorInfo));
                return false;
            }

            $pagos = $stmtPagos->fetchAll(PDO::FETCH_ASSOC);
        
            $registro['faltas'] = $faltas;
            $registro['pagos'] = $pagos;

            return $registro;

        } catch (PDOException $e) {
            error_log("Error en getRegistroById: " . $e->getMessage());
            return false;
        } 
    }
    
    public function createRegistro($user_ID, $folio, $fechaR, $num_registros, $fechaF, $horasF, $fechaP, $horasP) 
    {
        try {
            $this->db->prepare("START TRANSACTION")->execute();

            // Insertar el documento en la tabla timebytime
            $query = "INSERT INTO timebytime (usuario_id, folio, fechaR) VALUES (:user_ID, :folio, :fechaR)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_ID', $user_ID, PDO::PARAM_INT);
            $stmt->bindParam(':folio', $folio, PDO::PARAM_STR);
            $stmt->bindParam(':fechaR', $fechaR, PDO::PARAM_STR);

            if (!$stmt->execute()) {
                $errorInfo = $stmt->errorInfo();
                error_log("Error al ejecutar la consulta: " . implode(", ", $errorInfo));
                $this->db->prepare("ROLLBACK")->execute();
                return false;
            }
    
            // Obtener el ID del último registro insertado
            $timeByTimeId = $this->db->lastInsertId();
    
            // Insertar las faltas
            $queryF = "INSERT INTO timebytimefaltas (timebytime_id, fechaF, horasF) VALUES (:timeByTimeId, :fechaF, :horasF)";
            $stmtF = $this->db->prepare($queryF);
            for ($i = 0; $i < $num_registros; $i++) {
                $stmtF->bindParam(':timeByTimeId', $timeByTimeId, PDO::PARAM_INT);
                $stmtF->bindParam(':fechaF', $fechaF[$i], PDO::PARAM_STR);
                $stmtF->bindParam(':horasF', $horasF[$i], PDO::PARAM_INT);
                
                if (!$stmtF->execute()) {
                    $errorInfo = $stmt->errorInfo();
                    error_log("Error al ejecutar la consulta: " . implode(", ", $errorInfo));
                    $this->db->prepare("ROLLBACK")->execute();
                    return false;
                }
            }
    
            // Insertar los pagos
            $queryP = "INSERT INTO timebytimepagos (timebytime_id, fechaP, horaP) VALUES (:timeByTimeId, :fechaP, :horaP)";
            $stmtP = $this->db->prepare($queryP);
            foreach ($fechaP as $index => $fecha) {
                $stmtP->bindParam(':timeByTimeId', $timeByTimeId, PDO::PARAM_INT);
                $stmtP->bindParam(':fechaP', $fecha, PDO::PARAM_STR);
                $stmtP->bindParam(':horaP', $horasP[$index], PDO::PARAM_INT);

                if (!$stmtP->execute()) {
                    $errorInfo = $stmt->errorInfo();
                    error_log("Error al ejecutar la consulta: " . implode(", ", $errorInfo));
                    $this->db->prepare("ROLLBACK")->execute();
                    return false;
                }
            }
        
            $this->db->prepare("COMMIT")->execute();
            return true;
    
        } catch (PDOException $e) {
            $this->db->prepare("ROLLBACK")->execute();
            error_log("Error en createRegistro: " . $e->getMessage());
            return false;
        }
    }
    
    public function getValidationFolio($folio) {
        try {
            $sql = "SELECT folio FROM timebytime WHERE folio = :folio LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':folio', $folio, PDO::PARAM_STR);
            
            if (!$stmt->execute()) {
                $errorInfo = $stmt->errorInfo(); 
                error_log("Error al ejecutar la consulta: " . implode(", ", $errorInfo));
                return false;   
            }
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? true : false;
            
        } catch (PDOException $e) {
            error_log("Error en getValidationFolio: " . $e->getMessage());
            return false;
        }
    }

    public function getValidationRegistro($docID)
    {
        try {
            $queryFaltas = "SELECT * FROM timebytimefaltas WHERE timebytime_id = :docID";
            $stmtFaltas = $this->db->prepare($queryFaltas);
            $stmtFaltas->bindParam(':docID', $docID, PDO::PARAM_INT);
            
            if (!$stmtFaltas->execute()) {
                $errorInfo = $stmtFaltas->errorInfo(); 
                error_log("Error al ejecutar la consulta: " . implode(", ", $errorInfo));
                return false;   
            }
            $faltas = $stmtFaltas->fetchAll(PDO::FETCH_ASSOC);

            $queryPagos = "SELECT * FROM timebytimepagos WHERE timebytime_id = :docID";
            $stmtPagos = $this->db->prepare($queryPagos);
            $stmtPagos->bindParam(':docID', $docID, PDO::PARAM_INT);
            
            if (!$stmtPagos->execute()) {
                $errorInfo = $stmtPagos->errorInfo(); 
                error_log("Error al ejecutar la consulta: " . implode(", ", $errorInfo));
                return false;   
            }
            $pagos = $stmtPagos->fetchAll(PDO::FETCH_ASSOC);

            $resultadoFinal = [
                'faltas' => $faltas,
                'pagos' => $pagos
            ];

            return $resultadoFinal;

        } catch (PDOException $e) {
            error_log("Error en getValidationRegistro: " . $e->getMessage());
            return false;
        }
    }

    public function getAllFechasUsuario($userID, $registro_id=null)
    {
        try {
            $query = "
                SELECT tf.fechaF AS fecha
                FROM timebytimefaltas tf
                INNER JOIN timebytime t ON tf.timebytime_id = t.id
                WHERE t.usuario_id = :userID
                AND t.estatus IN ('pendiente', 'entregado')
                 " . ($registro_id !== null ? "AND tf.timebytime_id != :registroID" : "") . "
                
                UNION
                
                SELECT tp.fechaP AS fecha
                FROM timebytimepagos tp
                INNER JOIN timebytime t ON tp.timebytime_id = t.id
                WHERE t.usuario_id = :userID
                AND t.estatus IN ('pendiente', 'entregado')
                " . ($registro_id !== null ? "AND tp.timebytime_id != :registroID" : "") . "
                ORDER BY fecha
            ";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':userID', $userID, PDO::PARAM_INT);

            if ($registro_id !== null) {
                $stmt->bindValue(':registroID', $registro_id, PDO::PARAM_INT);
            }
            if (!$stmt->execute()) {
                error_log("Error en getAllFechasUsuario: " . implode(", ", $stmt->errorInfo()));
                return false;
            }

            return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

        } catch (PDOException $e) {
            error_log("Error en getAllFechasUsuario: " . $e->getMessage());
            return false;
        }
    }
    
    public function updateEstatusTimebyTimePagos(
        $docID,
        $newFechasFalta, $newHorasFalta, 
        $newFechasPago, $newHorasPago,
        $oldFechasFalta, $oldHorasFalta,
        $oldFechasPago, $oldHorasPago, 
        $oldEstatusPago
    )
    {
        try {
            $this->db->prepare("START TRANSACTION")->execute();
    
            // 1. Actualizar registros existentes de FALTAS
            if (!empty($oldFechasFalta)) {
                $updateFaltaQuery = "UPDATE timebytimefaltas 
                                   SET fechaF = :fecha, horasF = :horas 
                                   WHERE timebytime_id = :docID AND id = :faltaID";
                $stmtFalta = $this->db->prepare($updateFaltaQuery);
    
                foreach ($oldFechasFalta as $faltaID => $fecha) {
                    $horas = $oldHorasFalta[$faltaID] ?? 0;

                    $stmtFalta->bindValue(':fecha', $fecha, PDO::PARAM_STR);
                    $stmtFalta->bindValue(':horas', $horas, PDO::PARAM_INT);
                    $stmtFalta->bindValue(':docID', $docID, PDO::PARAM_INT);
                    $stmtFalta->bindValue(':faltaID', $faltaID, PDO::PARAM_INT);
    
                    if (!$stmtFalta->execute()) {
                        $errorInfo = $stmtFalta->errorInfo(); 
                        error_log("Error al ejecutar la consulta: " . implode(", ", $errorInfo));
                        $this->db->prepare("ROLLBACK")->execute();
                        return false;   
                    }
                }
            }
    
            // 2. Actualizar registros existentes de PAGOS
            if (!empty($oldFechasPago)) {
                $updatePagoQuery = "UPDATE timebytimepagos 
                                   SET fechaP = :fecha, horaP = :horas, estatusP = :estatus 
                                   WHERE timebytime_id = :docID AND id = :pagoID";
                $stmtPago = $this->db->prepare($updatePagoQuery);
    
                foreach ($oldFechasPago as $pagoID => $fecha) {
                    $horas = $oldHorasPago[$pagoID] ?? 0;
                    $estatus = $oldEstatusPago[$pagoID] ?? 0;
                    
                    $stmtPago->bindValue(':fecha', $fecha, PDO::PARAM_STR);
                    $stmtPago->bindValue(':horas', $horas, PDO::PARAM_INT);
                    $stmtPago->bindValue(':estatus', $estatus, PDO::PARAM_INT);
                    $stmtPago->bindValue(':docID', $docID, PDO::PARAM_INT);
                    $stmtPago->bindValue(':pagoID', $pagoID, PDO::PARAM_INT);
    
                    if (!$stmtPago->execute()) {
                        $errorInfo = $stmtPago->errorInfo(); 
                        error_log("Error al ejecutar la consulta: " . implode(", ", $errorInfo));
                        $this->db->prepare("ROLLBACK")->execute();
                        return false; 
                    }
                }
            }
            
            if(!empty($oldEstatusPago)){
                $updateEstatusQuery = "UPDATE timebytimepagos SET estatusP = :newEstatus WHERE id = :id";
                $stmtEstatus = $this->db->prepare($updateEstatusQuery);
                foreach ($oldEstatusPago as $pagoID => $estatus) {
                    $stmtEstatus->bindValue(':newEstatus', $estatus, PDO::PARAM_INT);
                    $stmtEstatus->bindValue(':id', $pagoID, PDO::PARAM_INT);
    
                    if (!$stmtEstatus->execute()) {
                        $errorInfo = $stmtEstatus->errorInfo(); 
                        error_log("Error al ejecutar la consulta: " . implode(", ", $errorInfo));
                        $this->db->prepare("ROLLBACK")->execute();
                        return false; 
                    }
                }
            }
    
            // 3. Insertar nuevas FALTAS
            if (!empty($newFechasFalta)) {
                $insertFaltaQuery = "INSERT INTO timebytimefaltas 
                                   (timebytime_id, fechaF, horasF) 
                                   VALUES (:docID, :fecha, :horas)";
                $stmtNewFalta = $this->db->prepare($insertFaltaQuery);
    
                foreach ($newFechasFalta as $index => $fecha) {
                    $horas = $newHorasFalta[$index] ?? 0;
                    
                    $stmtNewFalta->bindValue(':docID', $docID, PDO::PARAM_INT);
                    $stmtNewFalta->bindValue(':fecha', $fecha, PDO::PARAM_STR);
                    $stmtNewFalta->bindValue(':horas', $horas, PDO::PARAM_INT);
    
                    if (!$stmtNewFalta->execute()) {
                        $errorInfo = $stmtNewFalta->errorInfo(); 
                        error_log("Error al ejecutar la consulta: " . implode(", ", $errorInfo));
                        $this->db->prepare("ROLLBACK")->execute();
                        return false; 
                    }
                }
            }
    
            // 4. Insertar nuevos PAGOS
            if (!empty($newFechasPago)) {
                $insertPagoQuery = "INSERT INTO timebytimepagos 
                                  (timebytime_id, fechaP, horaP) 
                                  VALUES (:docID, :fecha, :horas)";
                $stmtNewPago = $this->db->prepare($insertPagoQuery);
    
                foreach ($newFechasPago as $index => $fecha) {
                    $horas = $newHorasPago[$index] ?? 0;
                    
                    $stmtNewPago->bindValue(':docID', $docID, PDO::PARAM_INT);
                    $stmtNewPago->bindValue(':fecha', $fecha, PDO::PARAM_STR);
                    $stmtNewPago->bindValue(':horas', $horas, PDO::PARAM_INT);
    
                    if (!$stmtNewPago->execute()) {
                        $errorInfo = $stmtNewPago->errorInfo(); 
                        error_log("Error al ejecutar la consulta: " . implode(", ", $errorInfo));
                        $this->db->prepare("ROLLBACK")->execute();
                        return false; 
                    }
                }
            }
    
            $this->db->prepare("COMMIT")->execute();
            return true;
    
        } catch (PDOException $e) {
            $this->db->prepare("ROLLBACK")->execute();
            error_log("Error en updateEstatusTimebyTimePagos: " . $e->getMessage());
            return false;
        }
    }

    public function UpdateUploadFile($docID, $archivo, $estatus)
    {
        try {
            $this->db->prepare("START TRANSACTION")->execute();
    
            $query = "UPDATE timebytime SET archivo = :archivo, estatus = :estatus WHERE id = :docID";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':docID', $docID, PDO::PARAM_INT);
            $stmt->bindParam(':archivo', $archivo, PDO::PARAM_LOB);
            $stmt->bindParam(':estatus', $estatus, PDO::PARAM_STR);
    
            if (!$stmt->execute()) {
                $errorInfo = $stmt->errorInfo(); 
                error_log("Error al ejecutar la consulta: " . implode(", ", $errorInfo));
                $this->db->prepare("ROLLBACK")->execute();
                return false;   
            }

            $this->db->prepare("COMMIT")->execute();
            return true;
    
        } catch (PDOException $e) {
            $this->db->prepare("ROLLBACK")->execute();
            error_log("Error en UpdateUploadFile: " . $e->getMessage());
            return false;
        }
    }

    public function getDownloadFile($docID)
    {
        try {
            $query = "SELECT archivo, folio FROM timebytime WHERE id = :docID";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':docID', $docID, PDO::PARAM_INT);
            
            if (!$stmt->execute()) {
                $errorInfo = $stmt->errorInfo(); 
                error_log("Error al ejecutar la consulta: " . implode(", ", $errorInfo));
                return false;   
            }
            
            $archivo = $stmt->fetch(PDO::FETCH_ASSOC);
            return $archivo;

        } catch (PDOException $e) {
            error_log("Error en getDownloadFile: " . $e->getMessage());
            return false;
        }
    }

    public function updateDeleteLogical($docID)
    {
        try {
            $this->db->prepare("START TRANSACTION")->execute();
    
            $query = "UPDATE timebytime SET estatus = 'cancelado' WHERE id = :docID";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':docID', $docID, PDO::PARAM_INT);
    
            if (!$stmt->execute()) {
                $errorInfo = $stmt->errorInfo();
                error_log("Error al ejecutar la consulta: " . implode(", ", $errorInfo));
                $this->db->prepare("ROLLBACK")->execute();
                return false;   
            }

            $this->db->prepare("COMMIT")->execute();
            return true;
    
        } catch (PDOException $e) {
            $this->db->prepare("ROLLBACK")->execute();
            error_log("Error en updateDeleteLogical: " . $e->getMessage());
            return false;
        }
    }
}