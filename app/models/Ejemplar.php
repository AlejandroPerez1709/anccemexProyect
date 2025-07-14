<?php
// app/models/Ejemplar.php
require_once __DIR__ . '/../../config/config.php';

class Ejemplar {

    /**
     * Guarda un nuevo ejemplar.
     * @param array $data Datos del ejemplar.
     * @return int|false ID del nuevo ejemplar o false si falla.
     */
    public static function store($data) {
        $conn = dbConnect();
        if (!$conn) {
            $_SESSION['error_details'] = 'Error de conexión a la base de datos al intentar guardar el ejemplar.';
            return false;
        }
        // SQL sin numeroRegistro
        // Columnas: nombre, raza, fechaNacimiento, socio_id, sexo, codigo_ejemplar, capa, numero_microchip, numero_certificado, estado, id_usuario
        $sql = "INSERT INTO ejemplares (nombre, raza, fechaNacimiento, socio_id, sexo, codigo_ejemplar, capa, numero_microchip, numero_certificado, estado, id_usuario) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"; // 11 placeholders
        $stmt = $conn->prepare($sql);
        if (!$stmt) { 
            error_log("Prepare failed (Ejemplar store): " . $conn->error); 
            $_SESSION['error_details'] = 'Error interno al preparar la inserción del ejemplar.'; 
            $conn->close(); 
            return false;
        }

        // Variables
        $nombre = $data['nombre'];
        $raza = $data['raza'] ?: null; 
        $fechaNacimiento = $data['fechaNacimiento'] ?: null; 
        $socio_id = $data['socio_id'];
        $sexo = $data['sexo']; 
        $codigo_ejemplar = $data['codigo_ejemplar'] ?: null; 
        $capa = $data['capa'] ?: null; 
        $numero_microchip = $data['numero_microchip'] ?: null;
        $numero_certificado = $data['numero_certificado'] ?: null; 
        $estado = $data['estado']; 
        $id_usuario = $data['id_usuario'];

        $types = "sssissssssi"; // 11 tipos
        $stmt->bind_param($types, $nombre, $raza, $fechaNacimiento, $socio_id, $sexo, $codigo_ejemplar, $capa, $numero_microchip, $numero_certificado, $estado, $id_usuario);
        $result = false; 
        $newId = false;
        try {
            $result = $stmt->execute();
            if ($result) { 
                $newId = $conn->insert_id; 
                if ($newId == 0) { 
                    $result=false; 
                    $_SESSION['error_details'] = 'Error al obtener el ID del ejemplar insertado.';
                } 
            } else { 
                error_log("Execute failed (Ejemplar store): " . $stmt->error);
                if ($conn->errno == 1062) { // Error de duplicado (si codigo_ejemplar o numero_microchip fueran UNIQUE)
                    $_SESSION['error_details'] = 'Ya existe un ejemplar con el mismo Código de Ejemplar o Número de Microchip.';
                } else {
                    $_SESSION['error_details'] = 'Error de base de datos al guardar el ejemplar: ' . $stmt->error;
                }
            }
        } catch (mysqli_sql_exception $e) { 
            error_log("Exception (Ejemplar store): " . $e->getMessage());
            $_SESSION['error_details'] = 'Error de base de datos al guardar el ejemplar (' . $e->getCode() . '): ' . $e->getMessage(); 
            $result = false;
        }
        $result = (bool) $result; 
        if ($stmt) $stmt->close(); 
        $conn->close();
        return ($result && $newId) ? $newId : false;
    }

    /**
     * Obtiene todos los ejemplares con información del socio.
     * @return array Array de ejemplares.
     */
    public static function getAll() {
        $conn = dbConnect();
        $ejemplares = []; 
        if (!$conn) {
            $_SESSION['error_details'] = 'Error de conexión a la base de datos al obtener ejemplares.';
            return $ejemplares;
        }
        // CORREGIDO: Ordenar por id_ejemplar ASC para reflejar el orden de captura (número)
        $query = "SELECT e.*, CONCAT(s.nombre, ' ', s.apellido_paterno, ' ', s.apellido_materno) as nombre_socio, s.codigoGanadero as socio_codigo_ganadero
                  FROM ejemplares e 
                  LEFT JOIN socios s ON e.socio_id = s.id_socio 
                  ORDER BY e.id_ejemplar ASC"; 
        $result = $conn->query($query);
        if($result) { 
            while($row = $result->fetch_assoc()){ 
                $ejemplares[] = $row; 
            } 
            $result->free();
        } else { 
            error_log("Error query (Ejemplar getAll): " . $conn->error); 
            $_SESSION['error_details'] = 'Error de base de datos al obtener ejemplares: ' . $conn->error;
        }
        $conn->close(); 
        return $ejemplares;
    }

    /**
     * Obtiene un ejemplar por ID con información del socio.
     * @param int $id ID del ejemplar.
     * @return array|null Array con datos o null.
     */
    public static function getById($id) {
        $conn = dbConnect();
        if (!$conn) {
            $_SESSION['error_details'] = 'Error de conexión a la base de datos al obtener ejemplar por ID.';
            return null;
        }
        $query = "SELECT e.*, CONCAT(s.nombre, ' ', s.apellido_paterno, ' ', s.apellido_materno) as nombre_socio, s.codigoGanadero as socio_codigo_ganadero
                  FROM ejemplares e 
                  LEFT JOIN socios s ON e.socio_id = s.id_socio 
                  WHERE e.id_ejemplar = ? LIMIT 1";
        $stmt = $conn->prepare($query);
        if (!$stmt) { 
            error_log("Prepare failed (Ejemplar getById): ".$conn->error); 
            $_SESSION['error_details'] = 'Error interno al preparar la obtención del ejemplar.';
            $conn->close(); 
            return null;
        }
        $stmt->bind_param("i", $id); 
        $executeResult = $stmt->execute(); 
        $ejemplar = null;
        if ($executeResult) { 
            $result = $stmt->get_result(); 
            if($result && $result->num_rows == 1){ 
                $ejemplar = $result->fetch_assoc(); 
            } 
            if($result) $result->free();
        } else { 
            error_log("Execute failed (Ejemplar getById): ".$stmt->error);
            $_SESSION['error_details'] = 'Error de base de datos al obtener el ejemplar: ' . $stmt->error;
        }
        if ($stmt) $stmt->close(); 
        $conn->close(); 
        return $ejemplar;
    }

    /**
     * Actualiza un ejemplar existente.
     * @param int $id ID del ejemplar.
     * @param array $data Nuevos datos.
     * @return bool Retorna true si éxito, false si falla.
     */
    public static function update($id, $data) {
        $conn = dbConnect();
        if (!$conn) {
            $_SESSION['error_details'] = 'Error de conexión a la base de datos al intentar actualizar el ejemplar.';
            return false;
        }
        // SQL sin numeroRegistro en SET
        $sql = "UPDATE ejemplares SET
                    nombre = ?, raza = ?, fechaNacimiento = ?, socio_id = ?,
                    sexo = ?, codigo_ejemplar = ?, capa = ?, numero_microchip = ?, numero_certificado = ?,
                    estado = ?, id_usuario = ?
                WHERE id_ejemplar = ?"; // 11 placeholders + ID
        $stmt = $conn->prepare($sql);
        if (!$stmt) { 
            error_log("Prepare failed (Ejemplar update): " . $conn->error); 
            $_SESSION['error_details'] = 'Error interno al preparar la actualización del ejemplar.'; 
            $conn->close(); 
            return false;
        }

        // Variables
        $nombre = $data['nombre'];
        $raza = $data['raza'] ?: null; 
        $fechaNacimiento = $data['fechaNacimiento'] ?: null; 
        $socio_id = $data['socio_id'];
        $sexo = $data['sexo']; 
        $codigo_ejemplar = $data['codigo_ejemplar'] ?: null; 
        $capa = $data['capa'] ?: null; 
        $numero_microchip = $data['numero_microchip'] ?: null;
        $numero_certificado = $data['numero_certificado'] ?: null; 
        $estado = $data['estado']; 
        $id_usuario = $data['id_usuario']; 
        $ejemplarId = $id;

        $types = "sssissssssii"; // 12 tipos (11 datos + ID)
        $stmt->bind_param($types,
            $nombre, $raza, $fechaNacimiento, $socio_id, $sexo,
            $codigo_ejemplar, $capa, $numero_microchip, $numero_certificado, $estado, $id_usuario,
            $ejemplarId
        );
        $result = false;
        try {
            $result = $stmt->execute();
            if (!$result) { 
                error_log("Execute failed (Ejemplar update): " . $stmt->error); 
                if ($conn->errno == 1062) { // Error de duplicado
                    $_SESSION['error_details'] = 'Ya existe otro ejemplar con el mismo Código de Ejemplar o Número de Microchip.';
                } else {
                    $_SESSION['error_details'] = 'Error de base de datos al actualizar el ejemplar: ' . $stmt->error;
                }
            }
        } catch (mysqli_sql_exception $e) { 
            error_log("Exception (Ejemplar update): " . $e->getMessage());
            $_SESSION['error_details'] = 'Error de base de datos al actualizar el ejemplar (' . $e->getCode() . '): ' . $e->getMessage(); 
            $result = false;
        }
        $result = (bool) $result; 
        if ($stmt) $stmt->close(); 
        $conn->close();
        return $result;
    }

    /**
     * Elimina un ejemplar por su ID.
     * @return bool
     */
    public static function delete($id) {
        $conn = dbConnect();
        if (!$conn) {
            $_SESSION['error_details'] = 'Error de conexión a la base de datos al intentar eliminar el ejemplar.';
            return false;
        }
        $sql = "DELETE FROM ejemplares WHERE id_ejemplar = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) { 
            error_log("Prepare failed (Ejemplar delete): " . $conn->error); 
            $_SESSION['error_details'] = 'Error interno al preparar la eliminación del ejemplar.'; 
            $conn->close(); 
            return false;
        } 
        $stmt->bind_param("i", $id);

        $result = false;
        try {
            $result = $stmt->execute();
            if (!$result) { 
                error_log("Execute failed (Ejemplar delete): " . $stmt->error);
                if ($conn->errno == 1451) { // Código de error para Foreign Key Constraint
                    $_SESSION['error_details'] = 'No se puede eliminar el ejemplar porque tiene servicios asociados u otros registros dependientes.';
                } else { 
                    $_SESSION['error_details'] = 'Error de base de datos al eliminar el ejemplar: ' . $stmt->error; 
                } 
            }
        } catch (mysqli_sql_exception $e) { 
            error_log("Exception (Ejemplar delete): " . $e->getMessage());
            $_SESSION['error_details'] = 'Error de base de datos al eliminar el ejemplar (' . $e->getCode() . '): ' . $e->getMessage(); 
            $result = false; 
        }

        $result = (bool) $result; 
        if ($stmt) $stmt->close(); 
        $conn->close();
        return $result;
    }

}