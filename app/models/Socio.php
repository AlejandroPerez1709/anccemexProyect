<?php
// app/models/Socio.php
require_once __DIR__ . '/../../config/config.php';

class Socio {

    /**
     * Guarda un nuevo socio en la base de datos (sin numeroSocio).
     */
    public static function store($data) {
        $conn = dbConnect();
        if (!$conn) return false;

        // Quitado numeroSocio de la lista de columnas
        $sql = "INSERT INTO socios (nombre, apellido_paterno, apellido_materno, nombre_ganaderia, direccion,
                codigoGanadero, telefono, email, fechaRegistro, estado, id_usuario";
        $values_placeholders = "?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?"; // 11 placeholders iniciales
        $types = "ssssssssssi"; // 11 tipos iniciales

         if (array_key_exists('identificacion_fiscal_titular', $data)) {
             $sql .= ", identificacion_fiscal_titular";
             $values_placeholders .= ", ?";
             $types .= "s";
         }
         $sql .= ") VALUES (" . $values_placeholders . ")";

        $stmt = $conn->prepare($sql);
         if (!$stmt) {
              error_log("Error al preparar la consulta (socio store): " . $conn->error);
              $_SESSION['error_details'] = 'Error interno al preparar datos.';
              $conn->close();
              return false;
         }

        // Asignar a variables (sin $numSocio)
        $nombre = $data['nombre'];
        $apPaterno = $data['apellido_paterno'];
        $apMaterno = $data['apellido_materno'];
        $nombreGanaderia = $data['nombre_ganaderia'] ?: null;
        $direccion = $data['direccion'] ?: null;
        // $numSocio ya no existe
        $codGanadero = $data['codigoGanadero'];
        $telefono = $data['telefono'] ?: null;
        $email = $data['email'] ?: null;
        $fechaReg = $data['fechaRegistro'];
        $estado = $data['estado'];
        $idUsuario = $data['id_usuario'];
        $params = [ // Array de parámetros (sin numeroSocio)
            $nombre, $apPaterno, $apMaterno, $nombreGanaderia, $direccion, $codGanadero,
            $telefono, $email, $fechaReg, $estado, $idUsuario
        ];

        if (array_key_exists('identificacion_fiscal_titular', $data)) {
             $identificacionFiscal = $data['identificacion_fiscal_titular'] ?: null;
             $params[] = $identificacionFiscal;
        }

        // Usar el operador splat (...)
        $stmt->bind_param($types, ...$params);


        $result = false; $newId = false;
        try {
            $result = $stmt->execute();
            if ($result) { $newId = $conn->insert_id; }
            else { error_log("Error al ejecutar (socio store): " . $stmt->error); $_SESSION['error_details'] = 'Error al guardar en BD: ' . $stmt->error; $result=false;}
        } catch (mysqli_sql_exception $e) {
            error_log("Error al insertar socio: " . $e->getMessage());
            $_SESSION['error_details'] = 'Error DB (' . $e->getCode() . '): ' . $e->getMessage();
            $result = false;
        }
        $stmt->close(); $conn->close();
        return $result ? $newId : false;
    }

    /**
     * Obtiene todos los socios (SELECT * aún funciona).
     */
    public static function getAll() {
        $conn = dbConnect();
        $query = "SELECT * FROM socios ORDER BY apellido_paterno, apellido_materno, nombre";
        $result = $conn->query($query); $socios = [];
        if($result) { while($row = $result->fetch_assoc()){ $socios[] = $row; } $result->free(); }
        else { error_log("Error al obtener socios: " . $conn->error); }
        $conn->close(); return $socios;
    }

    /**
     * Obtiene un socio por ID (SELECT * aún funciona).
     */
    public static function getById($id) {
        $conn = dbConnect();
        $stmt = $conn->prepare("SELECT * FROM socios WHERE id_socio = ? LIMIT 1");
        if (!$stmt) { error_log("Error prepare (socio getById): " . $conn->error); $conn->close(); return null; }
        $stmt->bind_param("i", $id); $stmt->execute(); $result = $stmt->get_result();
        $socio = ($result && $result->num_rows == 1) ? $result->fetch_assoc() : null;
        $stmt->close(); $conn->close(); return $socio;
    }

    /**
     * Actualiza un socio existente (sin numeroSocio).
     */
    public static function update($id, $data) {
        $conn = dbConnect();
        if (!$conn) return false;

        // Quitado numeroSocio = ? del SET
        $sql = "UPDATE socios SET nombre = ?, apellido_paterno = ?, apellido_materno = ?, nombre_ganaderia = ?, direccion = ?,
                codigoGanadero = ?, telefono = ?, email = ?, fechaRegistro = ?, estado = ?, id_usuario = ?";
        $types = "ssssssssssi"; // 11 tipos iniciales (sin numSocio)

        // Añadir identificacion_fiscal_titular si existe
         if (array_key_exists('identificacion_fiscal_titular', $data)) {
             $sql .= ", identificacion_fiscal_titular = ?";
             $types .= "s";
         }

        $sql .= " WHERE id_socio = ?";
        $types .= "i"; // Añadir tipo para id_socio

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("Error al preparar la consulta (socio update): " . $conn->error);
             $_SESSION['error_details'] = 'Error interno al preparar datos.';
            $conn->close(); return false;
        }

        // Asignar a variables (sin $numSocio)
        $nombre = $data['nombre'];
        $apPaterno = $data['apellido_paterno'];
        $apMaterno = $data['apellido_materno'];
        $nombreGanaderia = $data['nombre_ganaderia'] ?: null;
        $direccion = $data['direccion'] ?: null;
        // $numSocio ya no existe
        $codGanadero = $data['codigoGanadero'];
        $telefono = $data['telefono'] ?: null;
        $email = $data['email'] ?: null;
        $fechaReg = $data['fechaRegistro'];
        $estado = $data['estado'];
        $idUsuario = $data['id_usuario'];
        $socioId = $id;
        $params = [ // Array de params (sin numSocio)
             $nombre, $apPaterno, $apMaterno, $nombreGanaderia, $direccion, $codGanadero,
             $telefono, $email, $fechaReg, $estado, $idUsuario
        ];

         if (array_key_exists('identificacion_fiscal_titular', $data)) {
              $identificacionFiscal = $data['identificacion_fiscal_titular'] ?: null;
              $params[] = $identificacionFiscal;
         }
        // Añadir el ID al final
        $params[] = $socioId;

        $stmt->bind_param($types, ...$params);

        $result = false;
        try {
            $result = $stmt->execute();
             if (!$result) { error_log("Error al ejecutar (socio update): " . $stmt->error); $_SESSION['error_details'] = 'Error BD al actualizar: '. $stmt->error; $result=false;}
        } catch (mysqli_sql_exception $e) {
             error_log("Error al actualizar socio ID $id: " . $e->getMessage());
             $_SESSION['error_details'] = 'Error DB (' . $e->getCode() . '): ' . $e->getMessage();
             $result = false;
        }
        $stmt->close(); $conn->close();
        return $result;
    }

    /**
     * Elimina un socio por su ID. (Sin cambios)
     */
    public static function delete($id) { /* ... código igual que antes ... */ }

    /**
     * Obtiene socios activos para select (usa codigoGanadero en lugar de numeroSocio).
     */
    public static function getActiveSociosForSelect() {
        $conn = dbConnect();
        $query = "SELECT id_socio, nombre, apellido_paterno, apellido_materno, codigoGanadero, nombre_ganaderia
                  FROM socios
                  WHERE estado = 'activo'
                  ORDER BY apellido_paterno, apellido_materno, nombre";
        $result = $conn->query($query);
        $sociosList = [];
        if($result) {
            while($row = $result->fetch_assoc()){
                // Usar codigoGanadero en el texto display
                $displayText = $row['apellido_paterno'] . ' ' . $row['apellido_materno'] . ', ' . $row['nombre'];
                if (!empty($row['nombre_ganaderia'])) {
                    $displayText .= ' - ' . $row['nombre_ganaderia'];
                }
                // Mostrar codigoGanadero entre paréntesis
                $displayText .= ' (' . ($row['codigoGanadero'] ?: 'Sin Código') . ')';
                $sociosList[$row['id_socio']] = $displayText;
            }
            $result->free();
        } else { error_log("Error al obtener socios activos para select: " . $conn->error); }
        $conn->close();
        return $sociosList;
    }

} // Fin clase Socio
?>