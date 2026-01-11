<?php

function showAlert($type, $message)
{

    if (!empty($message)) {
        $alertClass = '';

        switch ($type) {
            case 'success':
                $alertClass = 'success';
                break;
            case 'error':
                $alertClass = 'error';
                break;
            case 'warning':
                $alertClass = 'warning';
                break;
            default:
                $alertClass = 'info'; // Puedes añadir un caso por defecto si lo necesitas
        }
    }

    echo "<div class=\"alerts\">
            <div class=\"card_alert $alertClass\" id=\"$alertClass\">
                <div class=\"card_header\">
                    <div class=\"title\">
                        <span class=\"icon $alertClass\"><i class=\"fa-solid fa-square\"></i></span>
                        <span class=\"sub-title\">SGDRH</span>
                    </div>
                </div>
                <div class=\"card_body\">
                    <p class=\"message\">$message</p>
                </div>
            </div>
        </div>";
}
