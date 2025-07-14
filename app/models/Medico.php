<?php
// app/models/Medico.php
require_once __DIR__ . '/../../config/config.php';

class Medico {

    /**
     * Guarda un nuevo médico en la base de datos.
     * @param array $data Datos del médico.
     * @return bool|int Retorna el ID del médico insertado en éxito, False en caso contrario.
     */
    public static function store($data) {
        $conn = dbConnect();
        if (!$conn) {
            $_SESSION['error_details'] = 'Error de conexión a la base de datos al intentar guardar el médico.';
            return false;
        }
        $sql = "INSERT INTO medicos (nombre, apellido_paterno, apellido_materno, especialidad, telefono, email,
                numero_cedula_profesional, entidad_residencia, numero_certificacion_ancce, estado, id_usuario)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
             error_log("Prepare failed (Medico store): " . $conn->error);
             $_SESSION['error_details'] = 'Error interno al preparar la inserción del médico.';
             $conn->close();
             return false;
        }

        // Asignar valores a variables para bind_param (evita error de referencia)
        $nombre = $data['nombre'];
        $apellido_paterno = $data['apellido_paterno'];
        $apellido_materno = $data['apellido_materno'];
        $especialidad = $data['especialidad'] ?: null;
        $telefono = $data['telefono'] ?: null;
        $email = $data['email'] ?: null; // Permite NULL si está vacío
        $numero_cedula = $data['numero_cedula_profesional'] ?: null;
        $entidad = $data['entidad_residencia'] ?: null;
        $num_ancce = $data['numero_certificacion_ancce'] ?: null;
        $estado = $data['estado'];
        $id_usuario = $data['id_usuario'];

        $types = "ssssssssssi"; // nom, apP, apM, esp, tel, email, ced, ent, cert, est, idU
        $stmt->bind_param($types,
            $nombre, $apellido_paterno, $apellido_materno, $especialidad, $telefono, $email,
            $numero_cedula, $entidad, $num_ancce, $estado, $id_usuario
        );
        $result = false;
        $newId = false;
        try {
            $result = $stmt->execute();
            if ($result) {
                $newId = $conn->insert_id;
            } else {
                 error_log("Execute failed (Medico store): " . $stmt->error);
                 if ($conn->errno == 1062) { // Error de duplicado (si el email o cédula fueran UNIQUE)
                     $_SESSION['error_details'] = 'Ya existe un médico con el Email o Cédula Profesional proporcionados.';
                 } else {
                     $_SESSION['error_details'] = 'Error de base de datos al guardar el médico: ' . $stmt->error;
                 }
            }
        } catch (mysqli_sql_exception $e) {
            error_log("Error al insertar médico: " . $e->getMessage());
            $_SESSION['error_details'] = 'Error de base de datos al guardar el médico (' . $e->getCode() . '): ' . $e->getMessage();
        }

        $stmt->close();
        $conn->close();
        return $result ? $newId : false;
    }

    /**
     * Obtiene todos los médicos.
     * @return array Lista de médicos.
     */
    public static function getAll() {
        $conn = dbConnect();
        $medicos = [];
        if (!$conn) {
            $_SESSION['error_details'] = 'Error de conexión a la base de datos al obtener médicos.';
            return $medicos;
        }
        // CORREGIDO: Ordenar por id_medico ASC para reflejar el orden de captura (número)
        $query = "SELECT * FROM medicos ORDER BY id_medico ASC"; 
        $result = $conn->query($query);
        if($result) {
            while($row = $result->fetch_assoc()){
                $medicos[] = $row;
            }
            $result->free();
        } else {
            error_log("Error al obtener médicos: " . $conn->error);
            $_SESSION['error_details'] = 'Error de base de datos al obtener médicos: ' . $conn->error;
        }
        $conn->close();
        return $medicos;
    }

    /**
     * Obtiene un médico específico por su ID.
     * @param int $id ID del médico.
     * @return array|null Datos del médico o null si no se encuentra.
     */
    public static function getById($id) {
        $conn = dbConnect();
        if (!$conn) {
            $_SESSION['error_details'] = 'Error de conexión a la base de datos al obtener médico por ID.';
            return null;
        }
        $stmt = $conn->prepare("SELECT * FROM medicos WHERE id_medico = ? LIMIT 1");
        if (!$stmt) {
             error_log("Error al preparar la consulta (Medico getById): " . $conn->error);
             $_SESSION['error_details'] = 'Error interno al preparar la obtención del médico.';
             $conn->close();
             return null;
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $medico = null;
        if($result && $result->num_rows == 1){
            $medico = $result->fetch_assoc();
        } else {
            error_log("Médico con ID $id no encontrado.");
            $_SESSION['error_details'] = "Médico no encontrado con el ID proporcionado.";
        }
        $stmt->close();
        $conn->close();
        return $medico;
    }

    /**
     * Actualiza los datos de un médico existente.
     * @param int $id ID del médico a actualizar.
     * @param array $data Nuevos datos del médico.
     * @return bool True si se actualizó correctamente, False en caso contrario.
     */
    public static function update($id, $data) {
        $conn = dbConnect();
        if (!$conn) {
            $_SESSION['error_details'] = 'Error de conexión a la base de datos al intentar actualizar el médico.';
            return false;
        }
        $sql = "UPDATE medicos SET
                nombre = ?, apellido_paterno = ?, apellido_materno = ?, especialidad = ?, telefono = ?, email = ?,
                numero_cedula_profesional = ?, entidad_residencia = ?, numero_certificacion_ancce = ?, estado = ?, id_usuario = ?
                WHERE id_medico = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
             error_log("Error al preparar la consulta (Medico update): " . $conn->error);
             $_SESSION['error_details'] = 'Error interno al preparar la actualización del médico.';
             $conn->close();
             return false;
        }

        // Asignar valores a variables para bind_param
        $nombre = $data['nombre'];
        $apellido_paterno = $data['apellido_paterno'];
        $apellido_materno = $data['apellido_materno'];
        $especialidad = $data['especialidad'] ?: null;
        $telefono = $data['telefono'] ?: null;
        $email = $data['email'] ?: null;
        $numero_cedula = $data['numero_cedula_profesional'] ?: null;
        $entidad = $data['entidad_residencia'] ?: null;
        $num_ancce = $data['numero_certificacion_ancce'] ?: null;
        $estado = $data['estado'];
        $id_usuario = $data['id_usuario']; // Usuario que edita
        $id_medico = $id; // ID del médico a editar

        $types = "ssssssssssii"; // 11 datos + id_medico
        $stmt->bind_param($types,
            $nombre, $apellido_paterno, $apellido_materno, $especialidad, $telefono, $email,
            $numero_cedula, $entidad, $num_ancce, $estado, $id_usuario, $id_medico
        );
        $result = false;
        try {
            $result = $stmt->execute();
            if (!$result) {
                  error_log("Error al ejecutar (Medico update): " . $stmt->error);
                  if ($conn->errno == 1062) { // Error de duplicado (si el email o cédula fueran UNIQUE)
                      $_SESSION['error_details'] = 'Ya existe otro médico con el Email o Cédula Profesional proporcionados.';
                  } else {
                      $_SESSION['error_details'] = 'Error de base de datos al actualizar el médico: ' . $stmt->error;
                  }
            }
            // Nota: fecha_modificacion se actualiza automáticamente si tu tabla tiene TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        } catch (mysqli_sql_exception $e) {
            error_log("Error al actualizar médico ID $id: " . $e->getMessage());
            $_SESSION['error_details'] = 'Error de base de datos al actualizar el médico (' . $e->getCode() . '): ' . $e->getMessage();
            $result = false;
        }

        $stmt->close();
        $conn->close();
        return $result;
    }

    /**
     * Elimina un médico por su ID.
     * @param int $id ID del médico a eliminar.
     * @return bool True si se eliminó correctamente, False en caso contrario.
     */
    public static function delete($id) {
        $conn = dbConnect();
        if (!$conn) {
            $_SESSION['error_details'] = 'Error de conexión a la base de datos al intentar eliminar el médico.';
            return false;
        }
        // Verificar dependencias (ej. en tabla 'servicios') antes de eliminar.
        $sql = "DELETE FROM medicos WHERE id_medico = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
             error_log("Error al preparar la consulta (Medico delete): " . $conn->error);
             $_SESSION['error_details'] = 'Error interno al preparar la eliminación del médico.';
             $conn->close();
             return false;
        }
        $stmt->bind_param("i", $id);

        $result = false;
        try {
             $result = $stmt->execute();
            if (!$result) {
                  error_log("Error al ejecutar (Medico delete): " . $stmt->error);
                  if ($conn->errno == 1451) { // Código de error para FK
                      $_SESSION['error_details'] = 'No se puede eliminar el médico, tiene servicios asociados u otros registros dependientes.';
                  } else {
                      $_SESSION['error_details'] = 'Error de base de datos al eliminar el médico: ' . $stmt->error;
                  }
              }
         } catch (mysqli_sql_exception $e) {
             error_log("Excepción al eliminar médico ID $id: " . $e->getMessage());
             $_SESSION['error_details'] = 'Error de base de datos al eliminar el médico (' . $e->getCode() . '): ' . $e->getMessage();
         }

        $stmt->close();
        $conn->close();
        return $result;
    }

    /**
     * Obtiene una lista de médicos activos formateada para usar en selects (<select>).
     * @return array Array asociativo [id_medico => 'Nombre Completo (Especialidad?)'].
     */
    public static function getActiveMedicosForSelect() {
        $conn = dbConnect();
        $medicosList = [];
        if (!$conn) {
            $_SESSION['error_details'] = 'Error de conexión a la base de datos al obtener médicos activos para select.';
            return $medicosList;
        }
        $query = "SELECT id_medico, nombre, apellido_paterno, apellido_materno, especialidad
                  FROM medicos
                  WHERE estado = 'activo'
                  ORDER BY apellido_paterno, apellido_materno, nombre"; // Mantener orden alfabético para select
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
        } else {
            error_log("Error al obtener médicos activos para select: " . $conn->error);
            $_SESSION['error_details'] = 'Error de base de datos al obtener médicos activos para select: ' . $conn->error;
        }
        $conn->close();
        return $medicosList;
    }
}