<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="icon" type="image/x-icon" href="assets/images/SGDRH.ico">
<script src="assets/js/jquery.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<?php

$page_title = "";
$css_file = [];
$js_file = [];

switch ($page) {
    case 'dashboard':
        $page_title = "Inicio";
        $css_file[] = "components/dropdown.css";
        $css_file[] = "components/table.css";
        $css_file[] = "components/modal.css";
        $css_file[] = "admin/dashboard.css";
        break;
    case 'manage_users':
        $page_title = "Administrar usuarios";
        $css_file[] = "components/dropdown.css";
        $css_file[] = "components/modal.css";
        $css_file[] = "components/table.css";
        $css_file[] = "admin/manage_users.css";
        break;
    case 'my_profile':
        $page_title = "Mi perfil";
        $css_file[] = "components/modal.css";
        $css_file[] = "my_profile.css";
        $js_file[] = "password.js";
        break;
    case 'see_user':
        $page_title = "Detalles del usuario";
        $css_file[] = "components/dropdown.css";
        $css_file[] = "components/modal.css";
        $css_file[] = "admin/see_user.css";
        break;
    case 'TimeByTime':
            $page_title = "TimeByTime";
            $css_file[] = "components/dropdown.css";
            $css_file[] = "components/table.css";
            $css_file[] = "components/modal.css";
            $css_file[] = "admin/dashboard.css";
            break;
    case 'configs':
        $page_title = "Configuraciónes";
        break;
    case 'commissions':
        $page_title = "Comisiones";
        $css_file[] = "components/dropdown.css";
        $css_file[] = "components/table.css";
        $css_file[] = "components/modal.css";
        $css_file[] = "admin/dashboard.css";
        break;  
    case 'licencias':
        $page_title = "Comisiones";
        $css_file[] = "components/dropdown.css";
        $css_file[] = "components/table.css";
        $css_file[] = "components/modal.css";
        $css_file[] = "admin/dashboard.css";
        break;             
    default:
        $page_title = "Error 404";
        break;
}

echo "<title>" . APP_NAME . " - " . $page_title . "</title>";

if (!empty($css_file)) {
    foreach ($css_file as $file) :
        echo "<link rel=\"stylesheet\" href=\"assets/css/" . $file . "?v=" . rand() . "\">";
    endforeach;
}
if (!empty($js_file)) {
    foreach ($js_file as $file_js) {
        echo "<script src=\"assets/js/" . $file_js . "\"></script>";
    }
}
?>

<link rel="stylesheet" href="assets/css/page.css?v=<?php echo (rand()); ?>">