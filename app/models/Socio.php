<?php
// app/models/Socio.php
require_once __DIR__ . '/../../config/config.php';

class Socio {

    // ... (El método store y getAll se mantienen igual) ...
    public static function store($data) {
        $conn = dbConnect();
        if (!$conn) {
            $_SESSION['error_details'] = 'Error de conexión a la base de datos al intentar guardar el socio.';
            return false;
        }

        $sql = "INSERT INTO socios (nombre, apellido_paterno, apellido_materno, nombre_ganaderia, direccion,
                                   codigoGanadero, telefono, email, fechaRegistro, estado, id_usuario,
                                   identificacion_fiscal_titular) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("Prepare failed (Socio store): " . $conn->error);
            $_SESSION['error_details'] = 'Error interno al preparar la inserción del socio: ' . $conn->error;
            $conn->close();
            return false;
        }
        
        $stmt->bind_param("ssssssssssis",
            $data['nombre'], $data['apellido_paterno'], $data['apellido_materno'], $data['nombre_ganaderia'], 
            $data['direccion'], $data['codigoGanadero'], $data['telefono'], $data['email'], 
            $data['fechaRegistro'], $data['estado'], $data['id_usuario'], $data['identificacion_fiscal_titular']
        );

        $newId = false;
        try {
            if ($stmt->execute()) {
                $newId = $conn->insert_id;
            } else {
                error_log("Execute failed (Socio store): " . $stmt->error);
                if ($conn->errno == 1062) {
                    $_SESSION['error_details'] = 'Ya existe un socio con el mismo Email, Código Ganadero o RFC/Identificación Fiscal.';
                } else {
                    $_SESSION['error_details'] = 'Error de base de datos al guardar el socio: ' . $stmt->error;
                }
            }
        } catch (mysqli_sql_exception $e) {
            error_log("Exception (Socio store): " . $e->getMessage());
            $_SESSION['error_details'] = 'Error de base de datos al guardar el socio (' . $e->getCode() . '): ' . $e->getMessage();
        }

        $stmt->close();
        $conn->close();
        return $newId;
    }

    public static function getAll($searchTerm = '') {
        $conn = dbConnect();
        $socios = [];
        if (!$conn) {
            $_SESSION['error_details'] = 'Error de conexión a la base de datos al obtener socios.';
            return $socios;
        }

        $query = "SELECT * FROM socios";
        $params = [];
        $types = '';

        if (!empty($searchTerm)) {
            $query .= " WHERE nombre LIKE ? OR apellido_paterno LIKE ? OR apellido_materno LIKE ? OR codigoGanadero LIKE ? OR email LIKE ? OR nombre_ganaderia LIKE ?";
            $searchTermWildcard = "%" . $searchTerm . "%";
            $params = [$searchTermWildcard, $searchTermWildcard, $searchTermWildcard, $searchTermWildcard, $searchTermWildcard, $searchTermWildcard];
            $types = 'ssssss';
        }

        $query .= " ORDER BY id_socio ASC";
        $stmt = $conn->prepare($query);

        if ($stmt) {
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            if($result) {
                while($row = $result->fetch_assoc()){
                    $socios[] = $row;
                }
                $result->free();
            } else {
                error_log("Error get_result (Socio getAll): " . $stmt->error);
            }
            $stmt->close();
        } else {
            error_log("Error query (Socio getAll): " . $conn->error);
            $_SESSION['error_details'] = 'Error de base de datos al obtener socios: ' . $conn->error;
        }

        $conn->close();
        return $socios;
    }

    public static function getById($id) {
        $conn = dbConnect();
        if (!$conn) {
            $_SESSION['error_details'] = 'Error de conexión a la base de datos al obtener socio por ID.';
            return null;
        }
        $stmt = $conn->prepare("SELECT * FROM socios WHERE id_socio = ? LIMIT 1");
        if (!$stmt) {
            error_log("Prepare failed (Socio getById): " . $conn->error);
            $_SESSION['error_details'] = 'Error interno al preparar la obtención del socio.';
            $conn->close();
            return null;
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $socio = null;
        if($result->num_rows == 1){
            $socio = $result->fetch_assoc();
        } else {
            error_log("Socio con ID $id no encontrado.");
            $_SESSION['error_details'] = "Socio no encontrado con el ID proporcionado.";
        }
        $stmt->close();
        $conn->close();
        return $socio;
    }

    // *** INICIO DE LA MODIFICACIÓN ***
    public static function update($id, $data) {
        $conn = dbConnect();
        if (!$conn) {
            $_SESSION['error_details'] = 'Error de conexión a la base de datos al intentar actualizar el socio.';
            return false;
        }
        
        // Se construye la consulta base
        $sql = "UPDATE socios SET
                nombre = ?, apellido_paterno = ?, apellido_materno = ?, nombre_ganaderia = ?, direccion = ?,
                codigoGanadero = ?, telefono = ?, email = ?, fechaRegistro = ?, estado = ?, id_usuario = ?,
                identificacion_fiscal_titular = ?";

        // Si el estado se está cambiando a 'activo', se añade la limpieza de la razón de desactivación
        if (isset($data['estado']) && $data['estado'] == 'activo') {
            $sql .= ", razon_desactivacion = NULL";
        }

        $sql .= " WHERE id_socio = ?";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("Prepare failed (Socio update): " . $conn->error);
            $_SESSION['error_details'] = 'Error interno al preparar la actualización del socio: ' . $conn->error;
            $conn->close();
            return false;
        }

        // Se enlazan los parámetros
        $stmt->bind_param("ssssssssssisi",
            $data['nombre'], $data['apellido_paterno'], $data['apellido_materno'], $data['nombre_ganaderia'], 
            $data['direccion'], $data['codigoGanadero'], $data['telefono'], $data['email'], 
            $data['fechaRegistro'], $data['estado'], $data['id_usuario'],
            $data['identificacion_fiscal_titular'], $id
        );

        $result = false;
        try {
            $result = $stmt->execute();
            if (!$result) {
                error_log("Execute failed (Socio update): " . $stmt->error);
                if ($conn->errno == 1062) {
                    $_SESSION['error_details'] = 'Ya existe otro socio con el mismo Email, Código Ganadero o RFC/Identificación Fiscal.';
                } else {
                    $_SESSION['error_details'] = 'Error de base de datos al actualizar el socio: ' . $stmt->error;
                }
            }
        } catch (mysqli_sql_exception $e) {
            error_log("Exception (Socio update): " . $e->getMessage());
            $_SESSION['error_details'] = 'Error de base de datos al actualizar el socio (' . $e->getCode() . '): ' . $e->getMessage();
            $result = false;
        }

        $stmt->close();
        $conn->close();
        return $result;
    }
    // *** FIN DE LA MODIFICACIÓN ***

    public static function delete($id, $razon) {
        $conn = dbConnect();
        if (!$conn) {
            $_SESSION['error_details'] = 'Error de conexión a la base de datos.';
            return false;
        }
        
        $sql = "UPDATE socios SET estado = 'inactivo', razon_desactivacion = ? WHERE id_socio = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $_SESSION['error_details'] = 'Error interno al preparar la desactivación del socio.';
            $conn->close();
            return false;
        }
        
        $stmt->bind_param("si", $razon, $id);

        $result = false;
        try {
            $result = $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            error_log("Exception (Socio delete): " . $e->getMessage());
            $_SESSION['error_details'] = 'Error de base de datos al desactivar el socio.';
        }

        $stmt->close();
        $conn->close();
        return $result;
    }

    public static function getActiveSociosForSelect() {
        $conn = dbConnect();
        $sociosList = [];
        if (!$conn) {
            $_SESSION['error_details'] = 'Error de conexión a la base de datos al obtener socios activos para select.';
            return $sociosList;
        }
        $query = "SELECT id_socio, nombre, apellido_paterno, apellido_materno, codigoGanadero
                  FROM socios
                  WHERE estado = 'activo'
                  ORDER BY apellido_paterno, apellido_materno, nombre";
        $result = $conn->query($query);
        if($result) {
            while($row = $result->fetch_assoc()){
                $displayText = $row['apellido_paterno'] . ' ' . $row['apellido_materno'] . ', ' . $row['nombre'];
                if (!empty($row['codigoGanadero'])) {
                    $displayText .= ' (' . $row['codigoGanadero'] . ')';
                }
                $sociosList[$row['id_socio']] = $displayText;
            }
            $result->free();
        } else {
            error_log("Error al obtener socios activos para select: " . $conn->error);
            $_SESSION['error_details'] = 'Error de base de datos al obtener socios activos para select: ' . $conn->error;
        }
        $conn->close();
        return $sociosList;
    }
}