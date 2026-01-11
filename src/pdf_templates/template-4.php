<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        * {
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: sans-serif;
        }

        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            position: relative;
        }

        .img-header {
            width: 95%;
            height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }

        .img-header img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: contain;
        }

        .container {
            width: 85%;
            margin: 0 auto;
            margin-top: 30px;
            flex: 1;
            padding-bottom: 120px; /* Espacio para el footer */
        }

        .fechaR,
        .parrafo_1,
        .section_faltas,
        .parrafo_2,
        .section_pagos,
        .parrafo_3 {
            margin-top: 5px;
            padding: 0 5px;
        }

        .titulo {
            text-align: center;
            font-size: 16pt;
            font-weight: bold;
        }

        .fechaR {
            text-align: right;
            font-size: 11pt;
        }

        .parrafo_1,
        .parrafo_2,
        .parrafo_3 {
            text-align: justify;
        }

        table {
            width: 85%;
            border-collapse: collapse;
            font-size: 12pt;
            margin-bottom: 10px;
            page-break-inside: avoid; /* Evita que las tablas se corten al imprimir */
        }
        
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #dce9f9;
            color: #2a2a2a;
        }
        .td-img{
            position: relative; /* Necesario para el posicionamiento absoluto de la imagen */
            width: 5px; /* Ancho fijo para celdas con imágenes */
            height: 5px; /* Altura fija para las celdas (ajusta según necesites) */
        }
        .td-img img {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
            display: block;
            margin: 0 auto;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            object-fit: contain;
        }

        .firma {
            text-align: center;
            margin-top: 100px;
            page-break-before: avoid; /* Evita que la firma quede sola en una página */
        }

        .linea-firma {
            border-top: 2px solid #000;
            width: 50%;
            margin: 0 auto 8px auto;
        }

        .footer {
            width: 95%;
            margin: 0 auto;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            margin-left: auto;
            margin-right: auto;
        }

        .footer img {
            width: 100%;
            height: auto;
            display: block;
            max-height: 100px;
            object-fit: contain;
        }

        /* Estilos específicos para impresión */
        @media print {
            body {
                padding: 0;
                margin: 0;
                min-height: auto;
                display: block;
            }
            
            .img-header, .main-content, .footer {
                width: 95%;
            }
            
            .container {
                width: 85%;
                margin: 0 auto;
                padding-bottom: 120px;
            }
            
            .footer {
                position: fixed;
                bottom: 0;
            }
            
            @page {
                size: A4;
                margin: 1cm;
            }
            
            h1, h2, h3, p, table {
                page-break-inside: avoid;
            }
            
            .firma {
                page-break-before: avoid;
            }
        }
    </style>

</head>
<body>
    <div class="img-header">
            <img src="/images/header.jpg" alt="Header">
     </div>
    <div class="container">
    
        <h1 class="titulo">TIEMPO POR TIEMPO</h1>

        <div class="fechaR"> 
            <p><strong>Zapopan, Jalisco <?= htmlspecialchars($registro['fechaR']) ?></strong></p>  
        </div>

        <p class="parrafo_1">
            Por medio de la presente se autoriza al C. <strong><?= htmlspecialchars($registro['usuario_nombre']) ?></strong>
            con número de nómina <strong><?= htmlspecialchars($registro['usuario_nomina']) ?></strong>
            para que <strong>reponga tiempo por tiempo</strong>, siendo su falta o incidencia:
        </p>

        <div class="section_faltas">
        <?php $mostrarColumnaMismoDia = !empty($registro['pagos_mismo_dia']); ?>
            <table>
                <thead>
                    <tr>
                        <th>Fecha de falta</th>
                        <th>Horas</th>
                        <?php if ($mostrarColumnaMismoDia): ?>
                            <th>Pago el mismo día</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registro['faltas'] as $falta): ?>
                        <tr>
                            <td><?= htmlspecialchars($falta['fechaF']) ?></td>
                            <td><?= htmlspecialchars($falta['horasF']) ?></td>
                            <?php if ($mostrarColumnaMismoDia): ?>
                                <td><?= in_array($falta['fechaF'], $registro['pagos_mismo_dia']) ? 'Sí' : '' ?></td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="parrafo_2">
            <p>Pagando con tiempo laborado la(s) siguiente(s) fecha(s):</p>
        </div>                
    
        <div class="section_pagos">
            <table>
                <thead>
                    <tr>
                        <th>Fecha de pago</th>
                        <th>Horas</th>
                        <?php if ($usuario == 1): ?>
                            <th>Validacion</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registro['pagos'] as $pago): ?>
                        <tr>
                            <td><?= htmlspecialchars($pago['fechaP']) ?></td>
                            <td><?= htmlspecialchars($pago['horaP']) ?></td>
                            <?php if ($usuario == 1): ?>
                                    <td class ="td-img">
                                        <?php if ($pago['estatusP'] == 1): ?>
                                            <img src="/images/check.jpg">
                                        <?php else: ?>
                                            <img src="/images/cross.jpg">
                                        <?php endif; ?>
                                    </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="parrafo_3">
            <p>Sin otro particular, me despido cordialmente.</p>
        </div>
        <?php if ($usuario !== 1): ?>
        <div class="firma">
            <div class="linea-firma"></div>
            <p><strong>NOMBRE Y FIRMA DEL JEFE INMEDIATO</strong></p>
        </div>
        <?php endif; ?>
    </div>
    <div class="footer">
        <img src="/images/footer_tec.jpg">
    </div>
</body>
</html>
