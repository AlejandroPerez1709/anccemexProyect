<?php
// app/models/Ejemplar.php
require_once __DIR__ . '/../../config/config.php';

class Ejemplar {

    // ... (El método store se mantiene igual) ...
    public static function store($data) {
        $conn = dbConnect();
        if (!$conn) {
            $_SESSION['error_details'] = 'Error de conexión a la base de datos al intentar guardar el ejemplar.';
            return false;
        }
        
        $sql = "INSERT INTO ejemplares (nombre, raza, fechaNacimiento, socio_id, sexo, codigo_ejemplar, capa, numero_microchip, numero_certificado, estado, id_usuario) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) { 
            error_log("Prepare failed (Ejemplar store): " . $conn->error);
            $_SESSION['error_details'] = 'Error interno al preparar la inserción del ejemplar.'; 
            $conn->close(); 
            return false;
        }

        $stmt->bind_param("sssissssssi", 
            $data['nombre'], $data['raza'], $data['fechaNacimiento'], $data['socio_id'], $data['sexo'], 
            $data['codigo_ejemplar'], $data['capa'], $data['numero_microchip'], $data['numero_certificado'], 
            $data['estado'], $data['id_usuario']
        );
        
        $newId = false;
        try {
            if ($stmt->execute()) {
                $newId = $conn->insert_id;
            } else {
                error_log("Execute failed (Ejemplar store): " . $stmt->error);
            }
        } catch (mysqli_sql_exception $e) { 
            error_log("Exception (Ejemplar store): " . $e->getMessage());
            $_SESSION['error_details'] = 'Error de base de datos al guardar el ejemplar.';
        }

        $stmt->close(); 
        $conn->close();
        return $newId;
    }

    /**
     * Cuenta el total de ejemplares, opcionalmente filtrados por un término de búsqueda.
     * @param string $searchTerm Término para buscar.
     * @return int Total de ejemplares.
     */
    public static function countAll($searchTerm = '') {
        $conn = dbConnect();
        if (!$conn) return 0;

        $query = "SELECT COUNT(e.id_ejemplar) as total 
                  FROM ejemplares e 
                  LEFT JOIN socios s ON e.socio_id = s.id_socio";
        $params = [];
        $types = '';

        if (!empty($searchTerm)) {
            $query .= " WHERE e.nombre LIKE ? OR e.codigo_ejemplar LIKE ? OR s.codigoGanadero LIKE ?";
            $searchTermWildcard = "%" . $searchTerm . "%";
            $params = [$searchTermWildcard, $searchTermWildcard, $searchTermWildcard];
            $types = 'sss';
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
     * Obtiene una lista paginada de ejemplares.
     * @param string $searchTerm Término para buscar.
     * @param int $limit Número de registros por página.
     * @param int $offset Número de registros a saltar.
     * @return array Lista de ejemplares.
     */
    public static function getAll($searchTerm = '', $limit = 15, $offset = 0) {
        $conn = dbConnect();
        $ejemplares = []; 
        if (!$conn) return $ejemplares;
        
        $query = "SELECT e.*, CONCAT(s.nombre, ' ', s.apellido_paterno) as nombre_socio, s.codigoGanadero as socio_codigo_ganadero
                  FROM ejemplares e 
                  LEFT JOIN socios s ON e.socio_id = s.id_socio";
        
        $params = [];
        $types = '';

        if (!empty($searchTerm)) {
            $query .= " WHERE e.nombre LIKE ? OR e.codigo_ejemplar LIKE ? OR s.codigoGanadero LIKE ?";
            $searchTermWildcard = "%" . $searchTerm . "%";
            $params = [$searchTermWildcard, $searchTermWildcard, $searchTermWildcard];
            $types = 'sss';
        }
        
        $query .= " ORDER BY e.id_ejemplar ASC LIMIT ? OFFSET ?";
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
                    $ejemplares[] = $row;
                }
                $result->free();
            }
            $stmt->close();
        } else {
            error_log("Error query (Ejemplar getAll): " . $conn->error);
        }

        $conn->close(); 
        return $ejemplares;
    }

    public static function getById($id) {
        $conn = dbConnect();
        if (!$conn) return null;

        $query = "SELECT e.*, CONCAT(s.nombre, ' ', s.apellido_paterno) as nombre_socio, s.codigoGanadero as socio_codigo_ganadero
                  FROM ejemplares e 
                  LEFT JOIN socios s ON e.socio_id = s.id_socio 
                  WHERE e.id_ejemplar = ? LIMIT 1";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            $conn->close();
            return null;
        }
        
        $stmt->bind_param("i", $id); 
        $stmt->execute(); 
        $result = $stmt->get_result();
        $ejemplar = $result ? $result->fetch_assoc() : null;
        
        $stmt->close(); 
        $conn->close(); 
        return $ejemplar;
    }

    public static function update($id, $data) {
        $conn = dbConnect();
        if (!$conn) return false;

        $sql = "UPDATE ejemplares SET
                    nombre = ?, raza = ?, fechaNacimiento = ?, socio_id = ?,
                    sexo = ?, codigo_ejemplar = ?, capa = ?, numero_microchip = ?, numero_certificado = ?,
                    estado = ?, id_usuario = ?";
        
        if (isset($data['estado']) && $data['estado'] == 'activo') {
            $sql .= ", razon_desactivacion = NULL";
        }

        $sql .= " WHERE id_ejemplar = ?";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param("sssissssssii",
            $data['nombre'], $data['raza'], $data['fechaNacimiento'], $data['socio_id'], $data['sexo'],
            $data['codigo_ejemplar'], $data['capa'], $data['numero_microchip'], $data['numero_certificado'], 
            $data['estado'], $data['id_usuario'], $id
        );

        $result = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $result;
    }

    public static function delete($id, $razon) {
        $conn = dbConnect();
        if (!$conn) return false;

        $sql = "UPDATE ejemplares SET estado = 'inactivo', razon_desactivacion = ? WHERE id_ejemplar = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) return false;
        
        $stmt->bind_param("si", $razon, $id);
        $result = $stmt->execute();
        
        if (!$result && $conn->errno == 1451) {
            $_SESSION['error_details'] = 'No se puede desactivar el ejemplar porque tiene servicios asociados.';
        }
        
        $stmt->close(); 
        $conn->close();
        return $result;
    }
}