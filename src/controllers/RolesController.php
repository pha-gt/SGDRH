<?php

require_once MODEL_PATH . 'RolesModel.php';
require_once MODEL_PATH . 'UserModel.php';
require_once EMAIL_PATH . 'Exception.php';
require_once EMAIL_PATH . 'PHPMailer.php';
//require_once EMAIL_PATH . 'SMTP.PHP';
require_once PDF_PATH . 'library/fpdf.php';
require_once UTIL_PATH . 'Session.php';

class RolesController
{
    private $rolModel;

    public function __construct($db)
    {
        $this->rolModel = new RolesModel($db);
    }
    public function showRoles($role, $userID)
    {
        $roles = $this->rolModel->getAllRoles($role, $userID);
        require VIEW_PATH . 'document/roles_list.php';
    }

    public function addRole($rolNombre)
    {
        if ($this->rolModel->insertRol($rolNombre)) {
            Session::set('rol_success', 'Rol agregado exitosamente.');
        } else {
            Session::set('rol_error', 'Error al agregar el rol.');
        }
        echo "<script>$(location).attr('href', 'admin_home.php?page=roles');</script>";
    }

    public function deleteRole($rolId)
    {
        $result = $this->rolModel->deleteRol($rolId);
        if ($result === true) {
            Session::set('rol_success', 'Rol eliminado exitosamente.');
        } elseif ($result === 'constraint') {
            Session::set('rol_error', 'No se puede eliminar el rol porque está asignado a uno o más usuarios.');
        } else {
            Session::set('rol_error', 'Error al eliminar el rol.');
        }
        echo "<script>$(location).attr('href', 'admin_home.php?page=roles');</script>";
    }

    public function updateRole($rolId, $rolNombre)
    {
        if ($this->rolModel->updateRol($rolId, $rolNombre)) {
            Session::set('rol_success', 'Rol actualizado exitosamente.');
        } else {
            Session::set('rol_error', 'Error al actualizar el rol.');
        }
        echo "<script>$(location).attr('href', 'admin_home.php?page=roles');</script>";
    }
}