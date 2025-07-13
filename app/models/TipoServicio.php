<?php
// app/models/TipoServicio.php
require_once __DIR__ . '/../../config/config.php';

class TipoServicio {

    /**
     * Obtiene todos los tipos de servicio.
     */
    public static function getAll($orderBy = 'nombre') {
        $conn = dbConnect();
        $allowedOrderBy = ['id_tipo_servicio', 'nombre', 'codigo_servicio', 'estado'];
        $orderBy = in_array($orderBy, $allowedOrderBy) ? $orderBy : 'nombre';

        // Quitado requiere_ejemplar del SELECT
        $query = "SELECT id_tipo_servicio, nombre, codigo_servicio, descripcion, requiere_medico, documentos_requeridos, estado FROM tipos_servicios ORDER BY $orderBy ASC";
        $result = $conn->query($query);
        $tipos = [];
        if($result) {
            while($row = $result->fetch_assoc()){
                $tipos[] = $row;
            }
            $result->free();
        } else {
            error_log("Error al obtener tipos de servicio: " . $conn->error);
        }
        $conn->close();
        return $tipos;
    }

    /**
     * Obtiene un tipo de servicio específico por su ID.
     */
    public static function getById($id) {
        $conn = dbConnect();
        // Quitado requiere_ejemplar del SELECT
        $stmt = $conn->prepare("SELECT id_tipo_servicio, nombre, codigo_servicio, descripcion, requiere_medico, documentos_requeridos, estado FROM tipos_servicios WHERE id_tipo_servicio = ? LIMIT 1");
        if (!$stmt) {
             error_log("Error al preparar la consulta (tipoServicio getById): " . $conn->error);
             $conn->close();
             return null;
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $tipo = null;
        if($result && $result->num_rows == 1){
            $tipo = $result->fetch_assoc();
        }
        $stmt->close();
        $conn->close();
        return $tipo;
    }

    /**
     * Guarda un nuevo tipo de servicio.
     */
    public static function store($data) {
        $conn = dbConnect();
        // Quitado requiere_ejemplar de la consulta
        $sql = "INSERT INTO tipos_servicios (nombre, codigo_servicio, descripcion, requiere_medico, documentos_requeridos, estado)
                VALUES (?, ?, ?, ?, ?, ?)"; // 6 placeholders
        $stmt = $conn->prepare($sql);
         if (!$stmt) {
              error_log("Error al preparar la consulta (tipoServicio store): " . $conn->error);
              $conn->close();
              return false;
         }

        $nombre = $data['nombre'];
        $codigo = $data['codigo_servicio'] ?: null;
        $descripcion = $data['descripcion'] ?: null;
        $reqMedico = !empty($data['requiere_medico']) ? 1 : 0;
        // $reqEjemplar ya no existe
        $docsReq = $data['documentos_requeridos'] ?: null;
        $estado = $data['estado'];

        // Tipos: s, s, s, i, s, s (6 datos)
        $types = "sssiss";
        $stmt->bind_param($types,
            $nombre, $codigo, $descripcion, $reqMedico, $docsReq, $estado
        );

        $result = false;
        $newId = false;
        try {
            $result = $stmt->execute();
            if ($result) { $newId = $conn->insert_id; }
            else { error_log("Error al ejecutar (tipoServicio store): " . $stmt->error); }
        } catch (mysqli_sql_exception $e) {
             error_log("Error al insertar tipo de servicio: " . $e->getMessage());
             if ($e->getCode() == 1062) { $_SESSION['error_details'] = 'El Nombre o Código de Servicio ya existe.'; }
             else { $_SESSION['error_details'] = 'Error de base de datos.'; }
             $result = false;
        }
        $stmt->close();
        $conn->close();
        return $result ? $newId : false;
    }

    /**
     * Actualiza un tipo de servicio existente.
     */
    public static function update($id, $data) {
        $conn = dbConnect();
         // Quitado requiere_ejemplar de la consulta
        $sql = "UPDATE tipos_servicios SET
                nombre = ?, codigo_servicio = ?, descripcion = ?, requiere_medico = ?,
                documentos_requeridos = ?, estado = ?
                WHERE id_tipo_servicio = ?"; // 6 placeholders + ID
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
              error_log("Error al preparar la consulta (tipoServicio update): " . $conn->error);
              $conn->close();
              return false;
         }

        $nombre = $data['nombre'];
        $codigo = $data['codigo_servicio'] ?: null;
        $descripcion = $data['descripcion'] ?: null;
        $reqMedico = !empty($data['requiere_medico']) ? 1 : 0;
        // $reqEjemplar ya no existe
        $docsReq = $data['documentos_requeridos'] ?: null;
        $estado = $data['estado'];
        $tipoId = $id;

        // Tipos: s,s,s, i, s, s, i (6 datos + ID)
        $types = "sssissi";
        $stmt->bind_param($types,
            $nombre, $codigo, $descripcion, $reqMedico, $docsReq, $estado, $tipoId
        );

         $result = false;
        try {
            $result = $stmt->execute();
            if (!$result) { error_log("Error al ejecutar (tipoServicio update): " . $stmt->error); }
        } catch (mysqli_sql_exception $e) {
             error_log("Error al actualizar tipo servicio ID $id: " . $e->getMessage());
             if ($e->getCode() == 1062) { $_SESSION['error_details'] = 'El Nombre o Código de Servicio ya existe para otro tipo.'; }
             else { $_SESSION['error_details'] = 'Error de base de datos al actualizar.'; }
             $result = false;
        }
        $stmt->close();
        $conn->close();
        return $result;
    }

    /**
     * Elimina un tipo de servicio por su ID. (Sin cambios)
     */
    public static function delete($id) {
        $conn = dbConnect();
        $sql = "DELETE FROM tipos_servicios WHERE id_tipo_servicio = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) { error_log("Error al preparar la consulta (tipoServicio delete): " . $conn->error); $conn->close(); return false; }
        $stmt->bind_param("i", $id);
        $result = false;
         try {
             $result = $stmt->execute();
              if (!$result) {
                  error_log("Error al ejecutar (tipoServicio delete): " . $stmt->error);
                  if ($conn->errno == 1451) { $_SESSION['error_details'] = 'No se puede eliminar, este tipo está siendo usado por servicios existentes.'; }
                  else { $_SESSION['error_details'] = 'Error de base de datos al eliminar.'; }
              }
         } catch (mysqli_sql_exception $e) {
             error_log("Excepción al eliminar tipo servicio ID $id: " . $e->getMessage());
             $_SESSION['error_details'] = 'Error general al eliminar.';
             $result = false;
         }
        $stmt->close();
        $conn->close();
        return $result;
    }

     /**
     * Obtiene tipos de servicio activos para usar en selects. (Sin cambios)
     */
    public static function getActiveForSelect() {
        $conn = dbConnect();
        $query = "SELECT id_tipo_servicio, nombre, codigo_servicio
                  FROM tipos_servicios
                  WHERE estado = 'activo'
                  ORDER BY nombre";
        $result = $conn->query($query);
        $list = [];
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
        }
        $conn->close();
        return $list;
    }

} // Fin clase TipoServicio
?>