<?php

class absenceModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getAll()
    {
        $query = "SELECT
                    absences.*,
                    usuario.usuario_nombre AS full_name
                FROM absences
                LEFT JOIN usuario ON usuario.usuario_id = absences.user_id
                WHERE absences.is_deleted = '0'
               -- AND absences.is_open = '1'
                ORDER BY absence_id DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllWithTotals(): array
    {
        $query = "SELECT
                absences.absence_id,
                absences.total_days,
                absences.parent_id,
                absences.user_id,
                usuario.usuario_nombre AS full_name
              FROM absences
              LEFT JOIN usuario ON usuario.usuario_id = absences.user_id
              WHERE absences.is_deleted = '0'";

        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Agrupar por usuario
        $users = [];

        foreach ($results as $row) {
            $userId = $row['user_id'];

            if (!isset($users[$userId])) {
                $users[$userId] = [
                    'user_id' => $userId,
                    'full_name' => $row['full_name'],
                    'absences' => [],
                    'map' => [],
                    'roots' => [],
                    'total_chain_days' => 0
                ];
            }

            // Guardar la ausencia
            $absenceId = $row['absence_id'];
            $users[$userId]['map'][$absenceId] = $row;
            $users[$userId]['map'][$absenceId]['children'] = [];
        }

        // Construir jerarquía por usuario
        foreach ($users as &$user) {
            foreach ($user['map'] as $id => &$item) {
                if (!empty($item['parent_id']) && isset($user['map'][$item['parent_id']])) {
                    $user['map'][$item['parent_id']]['children'][] = &$item;
                } else {
                    $user['roots'][] = &$item;
                }
            }

            // Sumar días por cada árbol raíz
            $sumDays = function($node) use (&$sumDays) {
                $total = (int) $node['total_days'];
                foreach ($node['children'] as $child) {
                    $total += $sumDays($child);
                }
                return $total;
            };

            foreach ($user['roots'] as $root) {
                $user['total_chain_days'] += $sumDays($root);
            }
        }

        return array_values($users); // Para que no tenga claves numéricas raras
    }

    public function getTotalDaysIncludingChildren(): array
    {
        $query = "SELECT absence_id, user_id, parent_id, total_days
              FROM absences
              WHERE is_deleted = 0";

        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Agrupar por user_id
        $byUser = [];

        foreach ($rows as $row) {
            $userId = $row['user_id'];
            if (!isset($byUser[$userId])) {
                $byUser[$userId] = [
                    'user_id' => $userId,
                    'total_days' => 0,
                    'map' => [],
                ];
            }

            // Guardar ausencia y preparar hijos
            $absenceId = $row['absence_id'];
            $byUser[$userId]['map'][$absenceId] = $row;
            $byUser[$userId]['map'][$absenceId]['children'] = [];
        }

        // Construir jerarquía por usuario
        foreach ($byUser as &$userData) {
            foreach ($userData['map'] as $id => &$item) {
                $parentId = $item['parent_id'];
                if (!empty($parentId) && isset($userData['map'][$parentId])) {
                    $userData['map'][$parentId]['children'][] = &$item;
                }
            }

            // Detectar raíces
            $roots = array_filter($userData['map'], fn($item) => empty($item['parent_id']));

            // Sumar días desde cada raíz
            $sumDays = function($node) use (&$sumDays) {
                $total = (int) $node['total_days'];
                foreach ($node['children'] as $child) {
                    $total += $sumDays($child);
                }
                return $total;
            };

            foreach ($roots as $root) {
                $userData['total_days'] += $sumDays($root);
            }

            // Limpiar la estructura interna
            unset($userData['map']);
        }

        return array_values($byUser); // Resultado limpio
    }



    public function get($id)
    {
        $query = "SELECT * FROM absences WHERE absence_id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function save($data)
    {
        $query = "INSERT INTO absences (
        user_id,
            parent_id,
            folio_number,
            document,
            total_days,
            start_date,
            end_date,
            is_open,
            created_at,
            updated_at,
            deleted_at,
            is_deleted
        ) VALUES (
            :user_id,
            :parent_id,
            :folio_number,
            :document,
            :total_days,
            :start_date,
            :end_date,
            :is_open,
            NOW(),
            NOW(),
            NULL,
            '0'
        )";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $data['user_id'], PDO::PARAM_INT);
        $stmt->bindParam(':parent_id', $data['parent_id'], PDO::PARAM_INT);
        $stmt->bindParam(':folio_number', $data['folio_number'], PDO::PARAM_STR);
        $stmt->bindParam(':document', $data['document'], PDO::PARAM_LOB); // NULL si no hay
        $stmt->bindParam(':total_days', $data['total_days'], PDO::PARAM_INT);
        $stmt->bindParam(':start_date', $data['start_date']);
        $stmt->bindParam(':end_date', $data['end_date']);
        $stmt->bindParam(':is_open', $data['is_open']); // '1' o '0'

        return $stmt->execute();
    }

    public function update($absenceId, $data)
    {
        // 1. Cerrar el registro actual
        $closeQuery = "UPDATE absences SET
        is_open = '0',
        updated_at = NOW()
    WHERE absence_id = :absence_id";

        $stmtClose = $this->db->prepare($closeQuery);
        $stmtClose->bindParam(':absence_id', $absenceId, PDO::PARAM_INT);
        $stmtClose->execute();

        // 2. Crear un nuevo registro con parent_id apuntando al anterior
        $data['parent_id'] = $absenceId; // Aseguramos que el parent_id sea el anterior
        $data['is_open'] = '1'; // El nuevo registro estará abierto

        return $this->save($data); // Reutilizamos el método save que ya tienes
    }


    public function delete($absenceId)
    {
        // 1. Obtener el path del documento y el parent_id
        $query = "SELECT document, parent_id FROM absences WHERE absence_id = :absence_id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':absence_id', $absenceId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $document = $row['document'] ?? null;
        $parentId = $row['parent_id'] ?? null;

        // 2. Eliminar físicamente el archivo si existe
        if ($document && file_exists($document)) {
            unlink($document);
        }

        // 3. Actualizar la base de datos (marcar como eliminado)
        $query = "UPDATE absences 
              SET is_deleted = '1', is_open = '0', deleted_at = NOW() 
              WHERE absence_id = :absence_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':absence_id', $absenceId, PDO::PARAM_INT);
        $stmt->execute();

        // 4. Si hay parent_id, actualizarlo a is_open = 1
        if ($parentId) {
            $query = "UPDATE absences SET is_open = '1' WHERE absence_id = :parent_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':parent_id', $parentId, PDO::PARAM_INT);
            $stmt->execute();
        }

        return true;
    }

    public function toggle($absenceId)
    {
        $query = "UPDATE absences SET is_open = '1' WHERE absence_id = :absence_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':absence_id', $absenceId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function getAbsenceChain($absenceId)
    {
        $chain = [];

        while ($absenceId !== null) {
            $query = "SELECT absences.*, usuario.usuario_nombre FROM absences 
         LEFT JOIN usuario on usuario.usuario_id = absences.user_id
         WHERE absence_id = :id ";



            $stmt = $this->db->prepare($query);
            $stmt->execute(['id' => $absenceId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) break;

            $chain[] = $row;
            $absenceId = $row['parent_id']; // continuar hacia arriba
        }

        return $chain;
    }


    public function getDays($absenceId)
    {
        $query = "SELECT total_days FROM absences WHERE parent_id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['id' => $absenceId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $row['total_days'] : null;
    }

}
