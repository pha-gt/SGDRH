<?php

class RolesModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getAllRoles($role, $userID)
    {

        $query = "SELECT * FROM rol";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRolById($rolID)
    {
        $query = "SELECT * FROM rol WHERE rol = :rolID";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':rolID', $rolID, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function insertRol($rolNombre)
    {
        $query = "INSERT INTO rol (rol_nombre) VALUES (:rolNombre)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':rolNombre', $rolNombre, PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function updateRol($rolID, $rolNombre)
    {
        $query = "UPDATE rol SET rol_nombre = :rolNombre WHERE rol_id = :rolID";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':rolID', $rolID, PDO::PARAM_INT);
        $stmt->bindParam(':rolNombre', $rolNombre, PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function deleteRol($rolID)
    {
        try {
            $query = "DELETE FROM rol WHERE rol_id = :rolID";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':rolID', $rolID, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                // Foreign key constraint violation
                return 'constraint';
            }
            return false;
        }
    }

}
