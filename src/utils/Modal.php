<?php

function generateModal($class, $title, $showClose)
{
    $modal = "<div class=\"modal $class\">
                    <div class=\"modal_content\">
                        <div class=\"modal_header\">
                            <h2>$title</h2>";
    if ($showClose === true) {

        $modal .= "<button onclick=\"closeModal('$class')\">Cerrar</button>";
    }
    $modal .= "</div>
                        <div class=\"modal_body\"></div>
                    </div>
                  </div>";
    return $modal;
}
