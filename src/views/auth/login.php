<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Inicio de sesión</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="stylesheet" href="assets/css/components/alerts.css">
    <link rel="icon" type="image/x-icon" href="assets/images/SGDRH.ico">
    <script src="assets/js/jquery.js"></script>
    <script src="assets/js/alert.js"></script>
    <script src="assets/js/auth/password.js"></script>

</head>

<body>
    <div id="alert">
        <?php
        Session::start();
        if (Session::exists('login_error')) {
            echo showAlert('error', Session::get('login_error'));
            echo "<script>hideAlert('error');</script>";
            Session::delete('login_error');
        } else if (Session::exists('login_warning')) {
            echo showAlert('warning', Session::get('login_warning'));
            echo "<script>hideAlert('warning');</script>";
            Session::delete('login_warning');
        }
        ?>
    </div>
    <form class="loginForm" action="login.php" method="post" autocomplete="none">
        <h1>Sistema Gestor De Documentos Recursos Humanos</h1>
        <div class="input_group">
            <input type="text" name="email" id="email" placeholder="Ingresa tu correo">
        </div>
        <div class="input_group password">
            <input type="password" name="password" id="password" placeholder="Ingresa tu contraseña">
            <i class="fa-solid fa-eye togglePassword"></i>
        </div>
        <button type="submit">Iniciar sesión</button>
    </form>
</body>

</html>