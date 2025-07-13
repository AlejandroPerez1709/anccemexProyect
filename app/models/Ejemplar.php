<?php
// app/models/Ejemplar.php
require_once __DIR__ . '/../../config/config.php';

class Ejemplar {

    /**
     * Guarda un nuevo ejemplar (sin numeroRegistro).
     * @param array $data Datos del ejemplar.
     * @return int|false ID del nuevo ejemplar o false si falla.
     */
    public static function store($data) {
        $conn = dbConnect(); if (!$conn) return false;
        // SQL sin numeroRegistro
        $sql = "INSERT INTO ejemplares (nombre, raza, fechaNacimiento, /* numeroRegistro, */ socio_id, sexo, codigo_ejemplar, capa, numero_microchip, numero_certificado, estado, id_usuario) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"; // 11 placeholders
        $stmt = $conn->prepare($sql);
        if (!$stmt) { error_log("Prepare failed (ejemplar store): " . $conn->error); $_SESSION['error_details'] = 'Error interno.'; $conn->close(); return false; }

        // Variables sin numeroRegistro
        $nombre = $data['nombre']; $raza = $data['raza'] ?: null; $fechaNacimiento = $data['fechaNacimiento'] ?: null; /* $numeroRegistro = $data['numeroRegistro']; */ $socio_id = $data['socio_id']; $sexo = $data['sexo']; $codigo_ejemplar = $data['codigo_ejemplar'] ?: null; $capa = $data['capa'] ?: null; $numero_microchip = $data['numero_microchip'] ?: null; $numero_certificado = $data['numero_certificado'] ?: null; $estado = $data['estado']; $id_usuario = $data['id_usuario'];
        // Tipos sin 's' para numeroRegistro
        $types = "sssissssssi"; // 11 tipos
        // Bind sin numeroRegistro
        $stmt->bind_param($types, $nombre, $raza, $fechaNacimiento, /* $numeroRegistro, */ $socio_id, $sexo, $codigo_ejemplar, $capa, $numero_microchip, $numero_certificado, $estado, $id_usuario);

        $result = false; $newId = false;
        try {
            $result = $stmt->execute();
            if ($result) { $newId = $conn->insert_id; if ($newId == 0) { $result=false; $_SESSION['error_details'] = 'Error ID.'; } }
            else { error_log("Execute failed (ejemplar store): " . $stmt->error); $_SESSION['error_details'] = 'Error BD: ' . $stmt->error; $result=false; }
        } catch (mysqli_sql_exception $e) { error_log("Exception (ejemplar store): " . $e->getMessage()); $_SESSION['error_details'] = 'Error DB (' . $e->getCode() . '): ' . $e->getMessage(); $result = false; }
        $result = (bool) $result; if ($stmt) $stmt->close(); $conn->close();
        return ($result && $newId) ? $newId : false;
    }

    /**
     * Obtiene todos los ejemplares (usando SELECT * que se adaptará).
     * @return array Array de ejemplares.
     */
    public static function getAll() {
        $conn = dbConnect(); $ejemplares = []; if (!$conn) return $ejemplares;
        // SELECT * recogerá las columnas existentes. codigoGanadero ya estaba incluido.
        $query = "SELECT e.*, CONCAT(s.nombre, ' ', s.apellido_paterno, ' ', s.apellido_materno) as nombre_socio, s.codigoGanadero as socio_codigo_ganadero
                  FROM ejemplares e LEFT JOIN socios s ON e.socio_id = s.id_socio ORDER BY e.nombre ASC";
        $result = $conn->query($query);
        if($result) { while($row = $result->fetch_assoc()){ $ejemplares[] = $row; } $result->free(); }
        else { error_log("Error query (ejemplar getAll): " . $conn->error); $conn->close(); return $ejemplares; }
        $conn->close(); return $ejemplares;
    }

    /**
     * Obtiene un ejemplar por ID (usando SELECT *).
     * @param int $id ID del ejemplar.
     * @return array|null Array con datos o null.
     */
    public static function getById($id) {
        $conn = dbConnect(); if (!$conn) return null;
         // SELECT * recogerá las columnas existentes.
        $query = "SELECT e.*, CONCAT(s.nombre, ' ', s.apellido_paterno, ' ', s.apellido_materno) as nombre_socio, s.codigoGanadero as socio_codigo_ganadero
                  FROM ejemplares e LEFT JOIN socios s ON e.socio_id = s.id_socio WHERE e.id_ejemplar = ? LIMIT 1";
        $stmt = $conn->prepare($query);
        if (!$stmt) { error_log("Prepare failed (ejemplar getById): ".$conn->error); $conn->close(); return null; }
        $stmt->bind_param("i", $id); $executeResult = $stmt->execute(); $ejemplar = null;
        if ($executeResult) { $result = $stmt->get_result(); if($result && $result->num_rows == 1){ $ejemplar = $result->fetch_assoc(); } if($result) $result->free(); }
        else { error_log("Execute failed (ejemplar getById): ".$stmt->error); }
        if ($stmt) $stmt->close(); $conn->close(); return $ejemplar;
    }

    /**
     * Actualiza un ejemplar existente (sin numeroRegistro).
     * @param int $id ID del ejemplar.
     * @param array $data Nuevos datos.
     * @return bool Retorna true si éxito, false si falla.
     */
    public static function update($id, $data) {
        $conn = dbConnect(); if (!$conn) return false;
        // SQL sin numeroRegistro en SET
        $sql = "UPDATE ejemplares SET
                    nombre = ?, raza = ?, fechaNacimiento = ?, /* numeroRegistro = ?, */ socio_id = ?,
                    sexo = ?, codigo_ejemplar = ?, capa = ?, numero_microchip = ?, numero_certificado = ?,
                    estado = ?, id_usuario = ?
                WHERE id_ejemplar = ?"; // 11 placeholders + ID
        $stmt = $conn->prepare($sql);
        if (!$stmt) { error_log("Prepare failed (ejemplar update): " . $conn->error); $_SESSION['error_details'] = 'Error interno.'; $conn->close(); return false; }

        // Variables sin numeroRegistro
        $nombre = $data['nombre']; $raza = $data['raza'] ?: null; $fechaNacimiento = $data['fechaNacimiento'] ?: null; /* $numeroRegistro = $data['numeroRegistro']; */ $socio_id = $data['socio_id']; $sexo = $data['sexo']; $codigo_ejemplar = $data['codigo_ejemplar'] ?: null; $capa = $data['capa'] ?: null; $numero_microchip = $data['numero_microchip'] ?: null; $numero_certificado = $data['numero_certificado'] ?: null; $estado = $data['estado']; $id_usuario = $data['id_usuario']; $ejemplarId = $id;
        // Tipos sin 's' para numeroRegistro
        $types = "sssissssssii"; // 12 tipos (11 datos + ID)
        // Bind sin numeroRegistro
        $stmt->bind_param($types,
            $nombre, $raza, $fechaNacimiento, /* $numeroRegistro, */ $socio_id, $sexo,
            $codigo_ejemplar, $capa, $numero_microchip, $numero_certificado, $estado, $id_usuario,
            $ejemplarId
        );

        $result = false;
        try {
            $result = $stmt->execute();
            if (!$result) { error_log("Execute failed (ejemplar update): " . $stmt->error); $_SESSION['error_details'] = 'Error BD: ' . $stmt->error; }
        } catch (mysqli_sql_exception $e) { error_log("Exception (ejemplar update): " . $e->getMessage()); $_SESSION['error_details'] = 'Error DB (' . $e->getCode() . '): ' . $e->getMessage(); $result = false; }
        $result = (bool) $result; if ($stmt) $stmt->close(); $conn->close();
        return $result;
    }

    /**
     * Elimina un ejemplar por su ID. (Sin cambios)
     * @return bool
     */
    public static function delete($id) {
        $conn = dbConnect(); if (!$conn) return false; // Return Path 1
        $sql = "DELETE FROM ejemplares WHERE id_ejemplar = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) { error_log("Prepare failed (ejemplar delete): " . $conn->error); $_SESSION['error_details'] = 'Error interno.'; $conn->close(); return false; } // Return Path 2
        $stmt->bind_param("i", $id);

        $result = false; // Valor por defecto
        try {
            $result = $stmt->execute();
             if (!$result) { error_log("Execute failed (ejemplar delete): " . $stmt->error); if ($conn->errno == 1451) { $_SESSION['error_details'] = 'No se puede eliminar, tiene registros asociados.'; } else { $_SESSION['error_details'] = 'Error BD al eliminar.'; } }
        } catch (mysqli_sql_exception $e) { error_log("Exception (ejemplar delete): " . $e->getMessage()); $_SESSION['error_details'] = 'Error general al eliminar.'; $result = false; }

        // Asegurar que $result es estrictamente booleano
        $result = (bool) $result;
        if ($stmt) $stmt->close();
        $conn->close();
        return $result; // <<< Retorno explícito final bool >>> // Return Path 3
    }

} // Fin clase
?>