<?php

require_once MODEL_PATH . 'UserModel.php';
require_once UTIL_PATH . 'Session.php';

class UserController
{
    private $userModel;

    public function __construct($db)
    {
        $this->userModel = new UserModel($db);
    }

    public function showProfile($userID)
    {
        $user = $this->userModel->getUserById($userID);
        require VIEW_PATH . 'content/my_profile.php';
    }

    public function showAllUsers($userRole)
    {
        $users = $this->userModel->getAllUsers($userRole);
        require VIEW_PATH . 'manage_users/list.php';
    }

    public function seeUser($userID)
    {
        $user = $this->userModel->getUserById($userID);
        require VIEW_PATH . 'manage_users/seeUser.php';
    }

    public function addUser($userNomina, $userName, $userCurp, $userRFC, $userEmail, $userGenero, $userIngreso, $userCumple, $userPuesto, $userAdscripcion, $userJefe, $userSindicato, $userRol, $userDiasEconomicos)
    {
        if ($this->userModel->addUser($userNomina, $userName, $userCurp, $userRFC, $userEmail, $userGenero, $userIngreso, $userCumple, $userPuesto, $userAdscripcion, $userJefe, $userSindicato, $userRol, $userDiasEconomicos)) {
            Session::set('user_success', 'Empleado registrado correctamente.');
        } else {
            Session::set('user_error', 'No se pudo registrar al empleado.');
        }
        echo "<script>$(location).attr('href', 'admin_home.php?page=manage_users');</script>";
    }

    public function updatePassword($userID, $newPassword)
    {
        if ($this->userModel->updatePassword($userID, $newPassword)) {
            Session::set('profile_success', 'Contraseña actualizada con éxito.');
        } else {
            Session::set('profile_error', 'No se pudo actualizar la contraseña.');
        }

        if ($_SESSION['user_role'] == 3) {
            echo "<script>$(location).attr('href', 'client_home.php?page=dashboard');</script>";
        } else {
            echo "<script>$(location).attr('href', 'admin_home.php?page=dashboard');</script>";
        }
    }

    public function ressetPassword($userID)
    {
        if ($this->userModel->ressetPassword($userID)) {
            Session::set('user_success', 'Contraseña actualizada con éxito.');
        } else {
            Session::set('user_error', 'No se pudo actualizar la contraseña.');
        }

        echo "<script>$(location).attr('href', 'admin_home.php?page=dashboard');</script>";
    }

    public function updateUser($userID, $userNomina, $userName, $userCurp, $userRFC, $userEmail, $userPuesto, $userAdscripcion, $userJefe, $userSindicato, $userRol, $userStatus, $userDiasEconomicos)
    {
        if ($this->userModel->updateUser($userID, $userNomina, $userName, $userCurp, $userRFC, $userEmail, $userPuesto, $userAdscripcion, $userJefe, $userSindicato, $userRol, $userStatus, $userDiasEconomicos)) {
            Session::set('user_success', 'Empleado actualizado correctamente.');
        } else {
            Session::set('user_error', 'No se pudo actualizar al empleado.');
        }
        echo "<script>$(location).attr('href', 'admin_home.php?page=manage_users');</script>";
    }

    public function existsEmail($email)
    {
        return $this->userModel->getUserByEmail($email) ? true : false;
    }

    public function existsNomina($nomina)
    {
        return $this->userModel->getUserByNomina($nomina) ? true : false;
    }
}
