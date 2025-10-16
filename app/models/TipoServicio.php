<?php
// app/models/TipoServicio.php
require_once __DIR__ . '/../../config/config.php';

class TipoServicio {

    /**
     * Obtiene todos los tipos de servicio.
     * @param string $orderBy Columna por la cual ordenar.
     * @return array Lista de tipos de servicio.
     */
    public static function getAll($orderBy = 'id_tipo_servicio') {
        $conn = dbConnect();
        $tipos = [];
        if (!$conn) {
            $_SESSION['error_details'] = 'Error de conexión a la base de datos al obtener tipos de servicio.';
            return $tipos;
        }
        $allowedOrderBy = ['id_tipo_servicio', 'nombre', 'codigo_servicio', 'estado'];
        $orderBy = in_array($orderBy, $allowedOrderBy) ? $orderBy : 'id_tipo_servicio';

        $query = "SELECT id_tipo_servicio, nombre, codigo_servicio, descripcion, requiere_medico, documentos_requeridos, estado FROM tipos_servicios ORDER BY {$orderBy} ASC";
        $result = $conn->query($query);
        if($result) {
            while($row = $result->fetch_assoc()){
                $tipos[] = $row;
            }
            $result->free();
        } else {
            error_log("Error al obtener tipos de servicio: " . $conn->error);
            $_SESSION['error_details'] = 'Error de base de datos al obtener tipos de servicio: ' . $conn->error;
        }
        $conn->close();
        return $tipos;
    }

    /**
     * Obtiene un tipo de servicio específico por su ID.
     * @param int $id ID del tipo de servicio.
     * @return array|null Datos del tipo de servicio o null si no se encuentra.
     */
    public static function getById($id) {
        $conn = dbConnect();
        if (!$conn) {
            $_SESSION['error_details'] = 'Error de conexión a la base de datos al obtener tipo de servicio por ID.';
            return null;
        }
        $stmt = $conn->prepare("SELECT id_tipo_servicio, nombre, codigo_servicio, descripcion, requiere_medico, documentos_requeridos, estado FROM tipos_servicios WHERE id_tipo_servicio = ? LIMIT 1");
        if (!$stmt) {
             error_log("Error al preparar la consulta (TipoServicio getById): " . $conn->error);
             $_SESSION['error_details'] = 'Error interno al preparar la obtención del tipo de servicio.';
             $conn->close();
             return null;
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $tipo = null;
        if($result && $result->num_rows == 1){
            $tipo = $result->fetch_assoc();
        } else {
            error_log("Tipo de servicio con ID $id no encontrado.");
            $_SESSION['error_details'] = "Tipo de servicio no encontrado con el ID proporcionado.";
        }
        $stmt->close();
        $conn->close();
        return $tipo;
    }

    /**
     * Guarda un nuevo tipo de servicio.
     * @param array $data Datos del tipo de servicio.
     * @return int|false ID del nuevo tipo de servicio o false si falla.
     */
    public static function store($data) {
        $conn = dbConnect();
        if (!$conn) {
            $_SESSION['error_details'] = 'Error de conexión a la base de datos al intentar guardar el tipo de servicio.';
            return false;
        }
        $sql = "INSERT INTO tipos_servicios (nombre, codigo_servicio, descripcion, requiere_medico, documentos_requeridos, estado)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
              error_log("Error al preparar la consulta (TipoServicio store): " . $conn->error);
              $_SESSION['error_details'] = 'Error interno al preparar la inserción del tipo de servicio.';
              $conn->close();
              return false;
        }

        $nombre = $data['nombre'];
        $codigo = $data['codigo_servicio'] ?: null;
        $descripcion = $data['descripcion'] ?: null;
        $reqMedico = !empty($data['requiere_medico']) ? 1 : 0;
        $docsReq = $data['documentos_requeridos'] ?: null;
        $estado = $data['estado'];
        $types = "sssiss";
        $stmt->bind_param($types,
            $nombre, $codigo, $descripcion, $reqMedico, $docsReq, $estado
        );
        
        $result = false;
        $newId = false;
        try {
            $result = $stmt->execute();
            if ($result) { 
                $newId = $conn->insert_id;
            } else { 
                error_log("Error al ejecutar (TipoServicio store): " . $stmt->error);
                if ($conn->errno == 1062) {
                    $_SESSION['error_details'] = 'El Nombre o Código de Servicio ya existe.';
                } else {
                    $_SESSION['error_details'] = 'Error de base de datos al guardar el tipo de servicio: ' . $stmt->error;
                }
            }
        } catch (mysqli_sql_exception $e) {
             error_log("Error al insertar tipo de servicio: " . $e->getMessage());
             $_SESSION['error_details'] = 'Error de base de datos al guardar el tipo de servicio (' . $e->getCode() . '): ' . $e->getMessage();
             $result = false;
        }
        $stmt->close();
        $conn->close();
        return $result ? $newId : false;
    }

    /**
     * Actualiza un tipo de servicio existente.
     * @param int $id ID del tipo de servicio a actualizar.
     * @param array $data Nuevos datos del tipo de servicio.
     * @return bool True si se actualizó correctamente, False en caso contrario.
     */
    public static function update($id, $data) {
        $conn = dbConnect();
        if (!$conn) {
            $_SESSION['error_details'] = 'Error de conexión a la base de datos al intentar actualizar el tipo de servicio.';
            return false;
        }
        $sql = "UPDATE tipos_servicios SET
                nombre = ?, codigo_servicio = ?, descripcion = ?, requiere_medico = ?,
                documentos_requeridos = ?, estado = ?
                WHERE id_tipo_servicio = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
              error_log("Error al preparar la consulta (TipoServicio update): " . $conn->error);
              $_SESSION['error_details'] = 'Error interno al preparar la actualización del tipo de servicio.';
              $conn->close();
              return false;
        }

        $nombre = $data['nombre'];
        $codigo = $data['codigo_servicio'] ?: null;
        $descripcion = $data['descripcion'] ?: null;
        $reqMedico = !empty($data['requiere_medico']) ? 1 : 0;
        $docsReq = $data['documentos_requeridos'] ?: null;
        $estado = $data['estado'];
        $tipoId = $id;

        $types = "sssissi";
        $stmt->bind_param($types,
            $nombre, $codigo, $descripcion, $reqMedico, $docsReq, $estado, $tipoId
        );
        $result = false;
        try {
            $result = $stmt->execute();
            if (!$result) { 
                error_log("Error al ejecutar (TipoServicio update): " . $stmt->error);
                if ($conn->errno == 1062) {
                    $_SESSION['error_details'] = 'El Nombre o Código de Servicio ya existe para otro tipo.';
                } else {
                    $_SESSION['error_details'] = 'Error de base de datos al actualizar el tipo de servicio: ' . $stmt->error;
                }
            }
        } catch (mysqli_sql_exception $e) {
             error_log("Error al actualizar tipo servicio ID $id: " . $e->getMessage());
             $_SESSION['error_details'] = 'Error de base de datos al actualizar el tipo de servicio (' . $e->getCode() . '): ' . $e->getMessage();
             $result = false;
        }
        $stmt->close();
        $conn->close();
        return $result;
    }

    /**
     * Desactiva un tipo de servicio (borrado lógico).
     * @param int $id ID del tipo de servicio a desactivar.
     * @return bool True si se desactivó correctamente, False en caso contrario.
     */
    public static function delete($id) {
        $conn = dbConnect();
        if (!$conn) {
            $_SESSION['error_details'] = 'Error de conexión a la base de datos.';
            return false;
        }
        
        // --- CÓDIGO MODIFICADO ---
        // Cambiamos DELETE por UPDATE para hacer un borrado lógico
        $sql = "UPDATE tipos_servicios SET estado = 'inactivo' WHERE id_tipo_servicio = ?";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) { 
            error_log("Error al preparar la consulta (TipoServicio delete): " . $conn->error);
            $_SESSION['error_details'] = 'Error interno al preparar la desactivación del tipo de servicio.'; 
            $conn->close(); 
            return false;
        }
        
        $stmt->bind_param("i", $id);
        $result = false;
        try {
             $result = $stmt->execute();
             if (!$result) {
                  error_log("Error al ejecutar (TipoServicio delete): " . $stmt->error);
                  if ($conn->errno == 1451) {
                      $_SESSION['error_details'] = 'No se puede desactivar, está siendo usado por servicios existentes.';
                  } else {
                      $_SESSION['error_details'] = 'Error de base de datos al desactivar el tipo de servicio: ' . $stmt->error;
                  }
              }
         } catch (mysqli_sql_exception $e) {
             error_log("Excepción al desactivar tipo servicio ID $id: " . $e->getMessage());
             $_SESSION['error_details'] = 'Error de base de datos (' . $e->getCode() . '): ' . $e->getMessage();
             $result = false;
         }
         
        $stmt->close();
        $conn->close();
        return $result;
    }

     /**
     * Obtiene tipos de servicio activos para usar en selects.
     * @return array Array asociativo [id_tipo_servicio => 'Nombre Tipo (Código)'].
     */
    public static function getActiveForSelect() {
        $conn = dbConnect();
        $list = [];
        if (!$conn) {
            $_SESSION['error_details'] = 'Error de conexión a la base de datos al obtener tipos de servicio activos para select.';
            return $list;
        }
        $query = "SELECT id_tipo_servicio, nombre, codigo_servicio
                  FROM tipos_servicios
                  WHERE estado = 'activo'
                  ORDER BY nombre ASC";
        $result = $conn->query($query);
        if($result) {
            while($row = $result->fetch_assoc()){
                $displayText = $row['nombre'];
                if (!empty($row['codigo_servicio'])) {
                    $displayText .= ' (' . $row['codigo_servicio'] . ')';
                }
                $list[$row['id_tipo_servicio']] = $displayText;
            }
            $result->free();
        } else {
            error_log("Error al obtener tipos de servicio activos para select: " . $conn->error);
            $_SESSION['error_details'] = 'Error de base de datos al obtener tipos de servicio activos para select: ' . $conn->error;
        }
        $conn->close();
        return $list;
    }

}