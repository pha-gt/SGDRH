<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Formato Comisión</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 0;
            text-align: center;
        }
        .container {
            width: 90%;
            margin: auto;
            border: 2px solid black;
            padding: 20px;
            text-align: left;
        }
        .header, .footer {
            width: 100%;
            margin-bottom: 10px;
            text-align: center;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .table th, .table td {
            border: 1px ;
            padding: 6px;
        }
        .table th {
            background-color:rgb(255, 255, 255);
            text-align: left;
        }
        .center {
            text-align: center;
            font-weight: bold;
        }
        .note {
            font-size: 10px;
            text-align: justify;
        }
        .signature {
            margin-top: 30px;
            text-align: center;
        }
        .signature p {
            margin: 0;
            font-size: 12px;
        }
        .footer img {
            width: 100%;
            height: 70px;
        }
    </style>
</head>
<body>
<?php
$db = new DB();
$userModel = new UserModel($db);
$commissionModel = new CommissionsModel($db); 
$comisions = $commissionModel->getCommissionsById($comision['id']);
$usuario = $userModel->getUserById($comision['usuario_id']);
$director = $userModel->getDirectorName2();
$jefeinmediato = $userModel->getJefeInmediato4($usuario['areaAdscripcion_id']);

?>
    <div class="header">
        <img src="/images/head.jpg" width="100%" height="70px">
    </div>

    <div class="container">
        <h2 class="center">TECNOLÓGICO SUPERIOR DE JALISCO ZAPOPAN</h2>
        <h3 class="center">COMISIÓN</h3>

        <table class="table">
            <tr>
                <th>Nombre:</th><td><?=htmlspecialchars($usuario['usuario_nombre'] ?? '');?></td>
                <th>Fecha de elaboración:</th><td><?=htmlspecialchars($comisions['fecha_elaboracion'] ?? '');?></td>
            </tr>
            <tr>
                <th>Cargo:</th><td><?=htmlspecialchars($usuario['puesto_nombre'] ?? ''); ?></td>
                <th>Folio:</th><td><span><?=htmlspecialchars($comisions['id'] ?? '');?></span></td>
            </tr>
            <tr>
                <th>Departamento:</th><td><?=htmlspecialchars($usuario['areaAdscripcion_nombre'] ?? '');?></td>
                <th>Nómina:</th><td><?=htmlspecialchars($usuario['usuario_nomina'] ?? '');?></td>
            </tr>
        </table>

        <br>
        <hr style="border: 1px solid black; margin: 0 -20px; width: calc(100% + 40px);">
        
        <table class="table">
            <tr>
                <th>LUGAR(ES):</th>
                
                
                <td colspan="3"><span><?=htmlspecialchars($comisions['lugar'] ?? '');?></td>


            </tr>
            <tr>
                <th>ASUNTO:</th>
                
                
                <td colspan="3"><span><?=htmlspecialchars($comisions['asunto'] ?? '');?></td>


            </tr>
            <tr>
                <th>Requiere Transporte:</th><td colspan="3"><span><?=htmlspecialchars($comisions['transporte'] ?? '');?></td>
            </tr>
            <tr>
                <th>Requiere viáticos:</th><td colspan="3"><span><?=htmlspecialchars($comisions['viaticos'] ?? '');?></td>
            <tr>
                <th>Especifique viáticos:</th><td colspan="3"><span><?=htmlspecialchars($comisions['especificacion_viaticos'] ?? '');?></td>
            </tr>
        </table>

        <hr style="border: 1px solid black; margin: 0 -20px; width: calc(100% + 40px);">
        
        <h3 class="center">Observaciones</h3>
        <table class="table">
            <tr>
                <td colspan="4">
                    <?php
                        $observaciones = $comisions['observaciones'] ?? '';
                        $detalles = [];

                        if (!empty($comisions['transporte_propio'])) {
                            $detalles[] = "Transporte propio: " . $comisions['transporte_propio'];
                        }
                        if (!empty($comisions['marca'])) {
                            $detalles[] = "Marca: " . $comisions['marca'];
                        }
                        if (!empty($comisions['modelo'])) {
                            $detalles[] = "Modelo: " . $comisions['modelo'];
                        }
                        if (!empty($comisions['color'])) {
                            $detalles[] = "Color: " . $comisions['color'];
                        }
                        if (!empty($comisions['placas'])) {
                            $detalles[] = "Placas: " . $comisions['placas'];
                        }
                        if (!empty($comisions['kilometraje'])) {
                            $detalles[] = "Kilometraje: " . $comisions['kilometraje'];
                        }

                        $infoExtra = implode(', ', $detalles);

                        echo htmlspecialchars(trim($observaciones . ($infoExtra ? '. ' . $infoExtra : '')));
                    ?>
                </td>
            </tr>
        </table>

        <br>
        <hr style="border: 1px solid black; margin: 0 -20px; width: calc(100% + 40px);">

        <table class="table">
            <tr>
                <th>Fecha de Salida</th><th>Hora</th>
            </tr>
            <tr>
                <td><?= htmlspecialchars(date('Y-m-d', strtotime($comisions['fecha_salida'] ?? ''))); ?></td>
                <td><?= htmlspecialchars(date('H:i', strtotime($comisions['fecha_salida'] ?? ''))); ?></td>
            </tr>

            <tr>
                <th>Fecha de Regreso</th><th>Hora</th>
            </tr>
            <tr>
                <td><?= htmlspecialchars(date('Y-m-d', strtotime($comisions['fecha_regreso'] ?? ''))); ?></td>
                <td><?= htmlspecialchars(date('H:i', strtotime($comisions['fecha_regreso'] ?? ''))); ?></td>
            </tr>
        </table>

        <table class="table">
                <th></th>
                <td><br><br><br><br><br><br><hr style="border: 1px solid black; margin: 1px; width: 70%;"></td>
                <th></th>
                <td><br><br><br><br><br><br><hr style="border: 1px solid black; margin: 1px; width: 70%;"></td>
            </tr>
            <tr>
                <th></th>
                <td><?=htmlspecialchars($usuario['jefeInmediato_nombre'] ?? '');?></td>
                <th></th>
                <td><?=htmlspecialchars($director['usuario_nombre'] ?? '');?></td>
            </tr>
            <tr>
                <th></th><td><?=htmlspecialchars($jefeinmediato['puesto_nombre'] ?? '');?></td>
                <th></th>
                <td><?=htmlspecialchars($director['puesto_nombre'] ?? '');?></td>
                
            </tr>
            <tr>
                <th></th><td><?=htmlspecialchars($jefeinmediato['areaAdscripcion_nombre'] ?? '');?><br> Unidad Académica Zapopan del ITJMMPyH</td>
                
                <th></th>
                <td> de la Unidad Académica Zapopan del ITJMMPyH</td>
            </tr>
        </table>
        <hr style="border: 1px solid black; margin: 0 -20px; width: calc(100% + 40px);">
        <p class="note">
            <strong>Nota:</strong> Se le recuerda que tiene 2 días naturales después de su regreso indicado, para entregar esta comisión<strong> SELLADA Y FIRMADA </strong>como a continuación se detalla: En el Depto. de Recursos Humanos: Comisión en original, ficha informativa y copia del reporte de incidencias.
        </p>

    </div>
    <div class="footer">
        <img src="/images/footer.jpg" width="100%" height="70px">
    </div>
</body>
</html>
