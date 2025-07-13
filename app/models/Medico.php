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
        $sql = "INSERT INTO medicos (nombre, apellido_paterno, apellido_materno, especialidad, telefono, email,
                numero_cedula_profesional, entidad_residencia, numero_certificacion_ancce, estado, id_usuario)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
             error_log("Error al preparar la consulta (medico store): " . $conn->error);
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

        // Tipos: s=string, i=integer. Ajustar según definición de tabla.
        // nom, apP, apM, esp, tel, email, ced, ent, cert, est, idU
        // s,   s,   s,   s,   s,   s,     s,   s,   s,    s,   i
        $types = "ssssssssssi";

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
                 error_log("Error al ejecutar (medico store): " . $stmt->error);
            }
        } catch (mysqli_sql_exception $e) {
            error_log("Error al insertar médico: " . $e->getMessage());
            if ($e->getCode() == 1062) { // Error de duplicado (email)
                 $_SESSION['error_details'] = 'El Email proporcionado ya existe para otro médico.';
            } else {
                 $_SESSION['error_details'] = 'Error de base de datos al guardar.';
            }
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
        $query = "SELECT * FROM medicos ORDER BY apellido_paterno, apellido_materno, nombre";
        $result = $conn->query($query);
        $medicos = [];
        if($result) {
            while($row = $result->fetch_assoc()){
                $medicos[] = $row;
            }
            $result->free();
        } else {
            error_log("Error al obtener médicos: " . $conn->error);
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
        $stmt = $conn->prepare("SELECT * FROM medicos WHERE id_medico = ? LIMIT 1");
         if (!$stmt) {
             error_log("Error al preparar la consulta (medico getById): " . $conn->error);
             $conn->close();
             return null;
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $medico = null;
        if($result && $result->num_rows == 1){
            $medico = $result->fetch_assoc();
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
        $sql = "UPDATE medicos SET
                nombre = ?, apellido_paterno = ?, apellido_materno = ?, especialidad = ?, telefono = ?, email = ?,
                numero_cedula_profesional = ?, entidad_residencia = ?, numero_certificacion_ancce = ?, estado = ?, id_usuario = ?
                WHERE id_medico = ?";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
             error_log("Error al preparar la consulta (medico update): " . $conn->error);
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

        // Tipos: s,s,s,s,s,s,s,s,s,s,i,i (11 datos + id_medico)
        $types = "ssssssssssii";

        $stmt->bind_param($types,
            $nombre, $apellido_paterno, $apellido_materno, $especialidad, $telefono, $email,
            $numero_cedula, $entidad, $num_ancce, $estado, $id_usuario, $id_medico
        );


        $result = false;
        try {
            $result = $stmt->execute();
             if (!$result) {
                  error_log("Error al ejecutar (medico update): " . $stmt->error);
             }
            // Nota: fecha_modificacion se actualiza automáticamente
        } catch (mysqli_sql_exception $e) {
            error_log("Error al actualizar médico ID $id: " . $e->getMessage());
             if ($e->getCode() == 1062) {
                 $_SESSION['error_details'] = 'El Email proporcionado ya existe para otro médico.';
             } else {
                 $_SESSION['error_details'] = 'Error de base de datos al actualizar.';
             }
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
        // Verificar dependencias (ej. en tabla 'servicios') antes de eliminar.
        $sql = "DELETE FROM medicos WHERE id_medico = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
             error_log("Error al preparar la consulta (medico delete): " . $conn->error);
             $conn->close();
             return false;
        }
        $stmt->bind_param("i", $id);

        $result = false;
         try {
             $result = $stmt->execute();
              if (!$result) {
                  error_log("Error al ejecutar (medico delete): " . $stmt->error);
                  if ($conn->errno == 1451) { // Error de FK
                      $_SESSION['error_details'] = 'No se puede eliminar el médico, tiene servicios asociados.';
                  } else {
                      $_SESSION['error_details'] = 'Error de base de datos al eliminar.';
                  }
              }
         } catch (mysqli_sql_exception $e) {
             error_log("Excepción al eliminar médico ID $id: " . $e->getMessage());
             $_SESSION['error_details'] = 'Error general al eliminar médico.';
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
        $query = "SELECT id_medico, nombre, apellido_paterno, apellido_materno, especialidad
                  FROM medicos
                  WHERE estado = 'activo'
                  ORDER BY apellido_paterno, apellido_materno, nombre";
        $result = $conn->query($query);
        $medicosList = [];
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
        }
        $conn->close();
        return $medicosList;
    }
}
?>