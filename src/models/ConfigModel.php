<?php

class ConfigModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getRoles()
    {
        $query = "SELECT * FROM rol ORDER BY rol_nombre ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPuestos()
    {
        $query = "SELECT * FROM puesto ORDER BY puesto_nombre ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getJefes()
    {
        $query = "SELECT jefeInmediato.jefeInmediato_id, jefeInmediato.jefeInmediato_nombre, areaAdscripcion.areaAdscripcion_nombre 
                  FROM jefeInmediato 
                  LEFT JOIN areaAdscripcion ON jefeInmediato.areaAdscripcion_id = areaAdscripcion.areaAdscripcion_id
                  ORDER BY jefeInmediato.jefeInmediato_nombre ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAreas()
    {
        $query = "SELECT * FROM areaAdscripcion ORDER BY areaAdscripcion_nombre ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSindicatos()
    {
        $query = "SELECT * FROM sindicato ORDER BY sindicato_nombre ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
