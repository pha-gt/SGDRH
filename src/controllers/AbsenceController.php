<?php

require_once MODEL_PATH . 'RolesModel.php';
require_once MODEL_PATH . 'absenceModel.php';
require_once MODEL_PATH . 'UserModel.php';
require_once EMAIL_PATH . 'Exception.php';
require_once EMAIL_PATH . 'PHPMailer.php';
require_once EMAIL_PATH . 'SMTP.PHP';
require_once PDF_PATH . 'library/fpdf.php';
require_once UTIL_PATH . 'Session.php';

class AbsenceController
{
    private $absenceModel;
    private $userModel;

    public function __construct($db)
    {
        $this->absenceModel = new absenceModel($db);
        $this->userModel = new UserModel($db);
    }
    public function show()
    {
        $return_data = $this->absenceModel->getAll();
        $total_days = $this->absenceModel->getAllWithTotals();
        //$dias_user = $this->absenceModel->getTotalDaysIncludingChildren();


        $users = $this->userModel->getAll();

        require VIEW_PATH . 'document/absence_list.php';
    }

    public function remove($id)
    {
        if ($this->absenceModel->delete($id)) {
            Session::set('document_success', 'Incapacidad eliminada exitosamente.');
            header('Location: admin_home.php?page=absences');
        } else {
            Session::set('document_error', 'Error al eliminar la incapacidad.');
        }
    }

    public function toggle($id)
    {
        if ($this->absenceModel->toggle($id)) {
            Session::set('document_success', 'Incapacidad activada exitosamente.');
            header('Location: admin_home.php?page=absences');
        } else {
            Session::set('document_error', 'Error al activar la incapacidad.');

        }
    }




    public function save($data){
        if ($this->absenceModel->save($data)) {
            Session::set('document_success', 'Incapacidad guardada exitosamente.');
            header('Location: admin_home.php?page=absences');
        } else {
            Session::set('document_error', 'Error al guardar la incapacidad.');
        }
    }

    public function update($absenceId, $data){
        if ($this->absenceModel->update($absenceId, $data)) {
            Session::set('document_success', 'Incapacidad guardada exitosamente.');
            header('Location: admin_home.php?page=absences');
        } else {
            Session::set('document_error', 'Error al guardar la incapacidad.');
        }
    }

    public function viewChain($absenceId)
    {
        $chain = $this->absenceModel->getAbsenceChain($absenceId);
        $totalDays = array_sum(array_column($chain, 'total_days'));

        $daysAById = $this->absenceModel->getDays($absenceId);

        if (is_array($daysAById) && isset($daysAById['total_days'])) {
            $daysAById = $daysAById['total_days'];
        }

// Asegurarse de que sea numérico
        $total = $totalDays + (is_numeric($daysAById) ? $daysAById : 0);

        echo "<p><strong>Total de días de incapacidad: {$total}</strong></p>";
        if (!empty($chain)) {
            echo "<table border='1' cellpadding='5' cellspacing='0'>";
            echo "<thead><tr>
        <th>Nombre</th>
        <th>Folio</th>
        <th>Inicio</th>
        <th>Fin</th>
        <th>Dias</th>
        <th>Estado</th>
        <th>Documento</th>
    </tr></thead>";
            echo "<tbody>";
            foreach (array_reverse($chain) as $item) {
                echo "<tr style='text-align: center;'>
            <td>" . htmlspecialchars($item['usuario_nombre']) . "</td>
            <td>" . htmlspecialchars($item['folio_number']) . "</td>
            <td>{$item['start_date']}</td>
            <td>{$item['end_date']}</td>
            <td>{$item['total_days']}</td>
            <td>" . ($item['is_open'] === '1' ? 'Abierto' : 'Cerrado') . "</td>
            <td>";
                if (!empty($item['document'])) {
                    echo '<a href="' . htmlspecialchars($item['document']) . '" target="_blank" title="Ver documento">
                <i class="fa-solid fa-file-pdf"
                  title="Ver documento"
                  ></i>
                
            </a>';
                } else {
                    echo 'Sin documento';
                }
                echo "</td>
           
        </tr>";
            }
            echo "</tbody></table>";
        } else {
            echo "<p>No se encontró la cadena de ausencias</p>";
        }
    }

}
?>   