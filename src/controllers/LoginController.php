<?php

require_once SERVER_PATH . 'DB.php';
require_once UTIL_PATH . 'Session.php';

class LoginController
{
    private $db;

    public function __construct()
    {
        $this->db = new DB();
    }

    public function showLoginForm()
    {
        require VIEW_PATH . 'auth/login.php';
    }

    public function login()
    {
        Session::start();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $email = $_POST['email'];
            $password = $_POST['password'];

            if (empty($email) || empty($password)) {
                Session::set('login_warning', 'Tienes que ingresar tu correo y tu contraseña.');
                header('Location: login.php');
                exit();
            }

            $query = "SELECT * FROM usuario WHERE usuario_email = :email";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {

                // Verify if the user has a Baja status
                if ($user['estatus'] == 'Baja') {
                    Session::set('login_error', 'Tu cuenta ha sido desactivada.');
                    header('Location: login.php');
                    exit();
                }



                // Validate password
              //  if ($user['usuario_password'] === $password) {
                if (password_verify($password, $user['usuario_password'])) {
                    // Logging the user in
                    $this->initializeSession($user);

                    // Redirect according to user role
                    if ($user['rol_id'] == 3) {
                        header('Location: client_home.php?page=dashboard');
                    } else {
                        header('Location: admin_home.php?page=dashboard');
                    }
                    exit;
                } else {
                    Session::set('login_error', 'La contraseña no coincide con los registros de la base de datos.');
                }
            } else {
                Session::set('login_error', 'El correo que ingresaste no existe.');
            }

            header('Location: login.php');
            exit;
        }
    }

    private function initializeSession($user)
    {
        Session::set('user_id', $user['usuario_id']);
        Session::set('user_name', $user['usuario_nombre']);
        Session::set('user_role', $user['rol_id']);
        Session::set('user_genre', $user['usuario_genero']);
        Session::set('user_avatar', $user['usuario_foto']);

        if ($user['rol_id'] != 3) {
            Session::set('user_area', $user['areaAdscripcion_id']);
            if ($user['sindicato_id'] != "No Sindicalizado") {
                Session::set('user_union', $user['sindicato_id']);
            }
        }
    }

    public function logout()
    {
        Session::start();
        Session::destroy();
        header('Location: login.php');
        exit;
    }
}
