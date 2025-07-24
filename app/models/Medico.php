<?php
// app/models/Medico.php
require_once __DIR__ . '/../../config/config.php';

class Medico {

    public static function store($data) {
        $conn = dbConnect();
        if (!$conn) return false;

        $sql = "INSERT INTO medicos (nombre, apellido_paterno, apellido_materno, especialidad, telefono, email,
                numero_cedula_profesional, entidad_residencia, numero_certificacion_ancce, estado, id_usuario)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param("ssssssssssi",
            $data['nombre'], $data['apellido_paterno'], $data['apellido_materno'], $data['especialidad'], 
            $data['telefono'], $data['email'], $data['numero_cedula_profesional'], $data['entidad_residencia'], 
            $data['numero_certificacion_ancce'], $data['estado'], $data['id_usuario']
        );
        
        $newId = false;
        try {
            if ($stmt->execute()) {
                $newId = $conn->insert_id;
            } else if ($conn->errno == 1062) {
                $_SESSION['error_details'] = 'Ya existe un médico con el Email o Cédula Profesional proporcionados.';
            }
        } catch (mysqli_sql_exception $e) {
            error_log("Error al insertar médico: " . $e->getMessage());
        }

        $stmt->close();
        $conn->close();
        return $newId;
    }

    /**
     * Cuenta el total de médicos, opcionalmente filtrados por un término de búsqueda.
     * @param string $searchTerm Término para buscar.
     * @return int Total de médicos.
     */
    public static function countAll($searchTerm = '') {
        $conn = dbConnect();
        if (!$conn) return 0;

        $query = "SELECT COUNT(id_medico) as total FROM medicos";
        $params = [];
        $types = '';

        if (!empty($searchTerm)) {
            $query .= " WHERE nombre LIKE ? OR apellido_paterno LIKE ? OR apellido_materno LIKE ? OR email LIKE ? OR numero_cedula_profesional LIKE ?";
            $searchTermWildcard = "%" . $searchTerm . "%";
            $params = [$searchTermWildcard, $searchTermWildcard, $searchTermWildcard, $searchTermWildcard, $searchTermWildcard];
            $types = 'sssss';
        }
        
        $stmt = $conn->prepare($query);
        if ($stmt) {
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            $total = $result->fetch_assoc()['total'];
            $stmt->close();
        } else {
            $total = 0;
        }
        
        $conn->close();
        return $total;
    }

    /**
     * Obtiene una lista paginada de médicos.
     * @param string $searchTerm Término para buscar.
     * @param int $limit Número de registros por página.
     * @param int $offset Número de registros a saltar.
     * @return array Lista de médicos.
     */
    public static function getAll($searchTerm = '', $limit = 15, $offset = 0) {
        $conn = dbConnect();
        $medicos = [];
        if (!$conn) return $medicos;

        $query = "SELECT * FROM medicos";
        $params = [];
        $types = '';

        if (!empty($searchTerm)) {
            $query .= " WHERE nombre LIKE ? OR apellido_paterno LIKE ? OR apellido_materno LIKE ? OR email LIKE ? OR numero_cedula_profesional LIKE ?";
            $searchTermWildcard = "%" . $searchTerm . "%";
            $params = [$searchTermWildcard, $searchTermWildcard, $searchTermWildcard, $searchTermWildcard, $searchTermWildcard];
            $types = 'sssss';
        }
        
        $query .= " ORDER BY id_medico ASC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= 'ii';
        
        $stmt = $conn->prepare($query);
        if ($stmt) {
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            if($result) {
                while($row = $result->fetch_assoc()){
                    $medicos[] = $row;
                }
                $result->free();
            }
            $stmt->close();
        }

        $conn->close();
        return $medicos;
    }

    public static function getById($id) {
        $conn = dbConnect();
        if (!$conn) return null;

        $stmt = $conn->prepare("SELECT * FROM medicos WHERE id_medico = ? LIMIT 1");
        if (!$stmt) {
             $conn->close();
             return null;
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $medico = $result ? $result->fetch_assoc() : null;
        
        $stmt->close();
        $conn->close();
        return $medico;
    }

    public static function update($id, $data) {
        $conn = dbConnect();
        if (!$conn) return false;

        $sql = "UPDATE medicos SET
                nombre = ?, apellido_paterno = ?, apellido_materno = ?, especialidad = ?, telefono = ?, email = ?,
                numero_cedula_profesional = ?, entidad_residencia = ?, numero_certificacion_ancce = ?, estado = ?, id_usuario = ?";
        
        if (isset($data['estado']) && $data['estado'] == 'activo') {
            $sql .= ", razon_desactivacion = NULL";
        }

        $sql .= " WHERE id_medico = ?";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param("ssssssssssii",
            $data['nombre'], $data['apellido_paterno'], $data['apellido_materno'], $data['especialidad'], 
            $data['telefono'], $data['email'], $data['numero_cedula_profesional'], $data['entidad_residencia'], 
            $data['numero_certificacion_ancce'], $data['estado'], $data['id_usuario'], $id
        );

        $result = $stmt->execute();
        if (!$result && $conn->errno == 1062) {
            $_SESSION['error_details'] = 'Ya existe otro médico con el Email o Cédula Profesional proporcionados.';
        }
        
        $stmt->close();
        $conn->close();
        return $result;
    }

    public static function delete($id, $razon) {
        $conn = dbConnect();
        if (!$conn) return false;

        $sql = "UPDATE medicos SET estado = 'inactivo', razon_desactivacion = ? WHERE id_medico = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param("si", $razon, $id);
        $result = $stmt->execute();

        if (!$result && $conn->errno == 1451) {
            $_SESSION['error_details'] = 'No se puede desactivar al médico, tiene servicios asociados.';
        }
        
        $stmt->close();
        $conn->close();
        return $result;
    }

    public static function getActiveMedicosForSelect() {
        $conn = dbConnect();
        $medicosList = [];
        if (!$conn) return $medicosList;

        $query = "SELECT id_medico, nombre, apellido_paterno, apellido_materno, especialidad
                  FROM medicos
                  WHERE estado = 'activo'
                  ORDER BY apellido_paterno, apellido_materno, nombre";
        $result = $conn->query($query);
        if($result) {
            while($row = $result->fetch_assoc()){
                $displayText = $row['apellido_paterno'] . ' ' . $row['apellido_materno'] . ', ' . $row['nombre'];
                if (!empty($row['especialidad'])) {
                    $displayText .= ' (' . $row['especialidad'] . ')';
                }
                $medicosList[$row['id_medico']] = $displayText;
            }
            $result->free();
        }
        $conn->close();
        return $medicosList;
    }
}