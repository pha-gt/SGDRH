<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Formato Licencia Sin Goce de Sueldo</title>
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
            border: 2px;
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
        .table td {
            padding: 6px;
            vertical-align: top;
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
        .input-line {
            border-bottom: 1px solid black;
            display: inline-block;
            width: 235px;
        }
        .small-input-line {
            border-bottom: 1px solid black;
            display: inline-block;
            width: 150px;
        }
        .cuadro{
            border: 1px solid black;
            padding: 10px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<?php
$db = new DB();
$userModel = new UserModel($db);
$LicenciasModel = new LicenciasModel($db); 
$licencia = $LicenciasModel->getLicenciasById($Licencias['id']);
$usuario = $userModel->getUserById($Licencias['usuario_id']);
$director = $userModel->getDirectorName2();
$jefeinmediato = $userModel->getJefeInmediato3($usuario['areaAdscripcion_id']);

$meses = [
    1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril', 5 => 'mayo', 6 => 'junio',
    7 => 'julio', 8 => 'agosto', 9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
];
?>

<div class="header">
    <img src="/images/head.jpg" width="100%" height="70px">
</div>

<div class="container">
    <div style="text-align: right;">
        <?php
        if (!empty($licencia['fecha_elaboracion'])) {
            $fecha = new DateTime($licencia['fecha_elaboracion']);
            $dia = $fecha->format('d');
            $mes = $meses[(int)$fecha->format('m')];
            $anio = $fecha->format('Y');
            echo "Jalisco, a {$dia} de {$mes} del año {$anio}.";
        } else {
            echo "Jalisco, a ____ de __________ del año 2024.";
        }
        ?>
    </div>

    <p>
        ILIANA JANETT HERNÁNDEZ PARTIDA<br>
        DIRECTORA GENERAL DEL INSTITUTO TECNOLÓGICO <br>JOSÉ MARIO MOLINA PASQUEL Y HENRÍQUEZ<br>
        PRESENTE:
    </p>

    <div class="cuadro">
        <p style="text-align: justify;">
            Con fundamento en lo dispuesto en las cláusulas 64 establecidas en las Condiciones Generales de Trabajo pactadas entre el Instituto Tecnológico José Mario Molina Pasquel y Henríquez y el Sindicato Titular, documento que fue depositado en el Tribunal de Arbitraje y Escalafón el pasado 15 de enero del año 2021, se pactó que para efecto de que fueran concedidos estos, dependerán de una manifestación por parte del Director General, por lo que la presente solicitud no implicará autorización por su sola presentación. Por lo que para tal efecto se solicita <b>LICENCIA SIN GOCE DE SUELDO</b> para el (la) siguiente trabajador(a):
        </p>
    </div>

    <br>

    <table class="table">
        <tr>
            <td><b>Nombre del Colaborador(a):</b></td>
            <td class="input-line"><br><?=htmlspecialchars($usuario['usuario_nombre'] ?? '');?></td>
        </tr>
        <tr>
            <td><b>Número de Empleado(a):</b></td>
            <td class="input-line"><br><?=htmlspecialchars($usuario['usuario_nomina'] ?? '');?></td>
            <td><b>Puesto:</b></td>
            <td class="input-line"><?=htmlspecialchars($usuario['puesto_nombre'] ?? '');?></td>
        </tr>
        <tr>
            <td><b>Unidad Académica:</b></td>
            <td class="input-line"><br>Zapopan del ITJMMPyH</td>
            <td><b>Área:</b></td>
            <td class="input-line"><?=htmlspecialchars($usuario['areaAdscripcion_nombre'] ?? '');?></td>
        </tr>
        <tr>
            <td><b>Fecha de Ingreso:</b></td>
            <td class="input-line"><br><?=htmlspecialchars($usuario['usuario_fechaIngreso'] ?? '');?></td>
        </tr>
    </table>

    <br><br><br>

    <?php
        $fechaSalida = isset($licencia['fecha_salida']) ? explode('-', $licencia['fecha_salida']) : ['', '', ''];
        $fechaRegreso = isset($licencia['fecha_regreso']) ? explode('-', $licencia['fecha_regreso']) : ['', '', ''];
    ?>

    <table class="table">
        <tr>
            <td><b>Para ausentarse del día:</b></td>
            <td class="small-input-line"><br><?= htmlspecialchars($fechaSalida[2]) ?></td>
            <td><b>Del mes:</b></td>
            <td class="small-input-line"><br><?= isset($meses[(int)$fechaSalida[1]]) ? $meses[(int)$fechaSalida[1]] : '' ?></td>
            <td><b>Del año:</b></td>
            <td class="small-input-line"><br><?= htmlspecialchars($fechaSalida[0]) ?></td>
        </tr>
        <tr>
            <td><b><br><br>Al día:</b></td>
            <td class="small-input-line"><br><br><?= htmlspecialchars($fechaRegreso[2]) ?></td>
            <td><b><br>Del mes:</b></td>
            <td class="small-input-line"><br><br><?= isset($meses[(int)$fechaRegreso[1]]) ? $meses[(int)$fechaRegreso[1]] : '' ?></td>
            <td><b><br>Del año:</b></td>
            <td class="small-input-line"><br><br><?= htmlspecialchars($fechaRegreso[0]) ?></td>
        </tr>
    </table>

    <br><br>

    <table class="table" style="text-align: center;">  
        <tr>
            <th style="width: 40%;"><br>Nombre y Firma del Solicitante:</th>
            <td style="height: 40px; border-bottom: 1px solid black;"></td>
        </tr>
        <tr>
            <th><br>Nombre y Firma del Jefe Inmediato:</th>
            <td style="height: 40px; border-bottom: 1px solid black;"></td>
        </tr>
        <tr>
            <th><br><br>Vo. Bo. Nombre y Firma del Director(a) de Unidad Académica:</th>
            <td style="height: 40px; border-bottom: 1px solid black;"></td>
        </tr>
    </table>

    <br><br><br>

    <div style="font-size:10px; text-align: left;">
        C.c.p. Capital Humano Dirección General<br>
        C.c.p. Capital Humano Unidad Académica
    </div>

</div>

<div class="footer">
    <img src="/images/footer.jpg" width="100%" height="70px">
</div>

</body>
</html>
