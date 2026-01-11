<?php
class view_pdf
{
    private $db;

    public function __construct($db)
    { 
        $this->db = $db;
    }


    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $commissionsModel = new CommissionsModel($db);
        $commission = $commissionsModel->getCommissionsById($id);

        if ($commission && !empty($commission['pdf'])) {
            header('Content-Type: application/pdf');
            echo $commission['pdf'];
            exit;
        }
    }

    // Si el PDF no está disponible, puedes redirigir o mostrar un mensaje
    // header('Location: error_page.php');
    // o simplemente mostrar un mensaje

    echo "El PDF no está disponible.";

} 
?>
