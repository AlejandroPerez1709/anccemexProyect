<?php
// app/models/Socio.php
require_once __DIR__ . '/../../config/config.php';

class Socio {

    /**
     * Guarda un nuevo socio en la base de datos.
     * @param array $data Datos del socio.
     * @return int|false ID del nuevo socio o false si falla.
     */
    public static function store($data) {
        $conn = dbConnect();
        if (!$conn) {
            $_SESSION['error_details'] = 'Error de conexión a la base de datos al intentar guardar el socio.';
            return false;
        }

        // CORREGIDO: Ajustado el SQL para que coincida con las columnas existentes en tu BD
        // Las columnas: nombre, apellido_paterno, apellido_materno, nombre_ganaderia, direccion,
        // codigoGanadero, telefono, email, fechaRegistro, estado, id_usuario, identificacion_fiscal_titular
        $sql = "INSERT INTO socios (nombre, apellido_paterno, apellido_materno, nombre_ganaderia, direccion,
                                    codigoGanadero, telefono, email, fechaRegistro, estado, id_usuario,
                                    identificacion_fiscal_titular) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"; // 12 placeholders

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("Prepare failed (Socio store): " . $conn->error);
            $_SESSION['error_details'] = 'Error interno al preparar la inserción del socio: ' . $conn->error;
            $conn->close();
            return false;
        }

        // Asignar valores a variables para bind_param
        // Asegúrate de que el orden y tipo de estas variables coincidan con el SQL de arriba
        $nombre = $data['nombre'];
        $apellido_paterno = $data['apellido_paterno'];
        $apellido_materno = $data['apellido_materno'];
        $nombre_ganaderia = $data['nombre_ganaderia'] ?: null;
        $direccion = $data['direccion'] ?: null;
        $codigoGanadero = $data['codigoGanadero'] ?: null;
        $telefono = $data['telefono'] ?: null;
        $email = $data['email'] ?: null;
        $fechaRegistro = $data['fechaRegistro'] ?: date('Y-m-d'); // Usar el dato o la fecha actual
        $estado = $data['estado'];
        $id_usuario = $data['id_usuario'];
        $identificacion_fiscal_titular = $data['identificacion_fiscal_titular'] ?: null;

        // 12 tipos: 11 strings (s), 1 int (i)
        $types = "sssssssssssi"; 
        $stmt->bind_param($types,
            $nombre, $apellido_paterno, $apellido_materno, $nombre_ganaderia, $direccion,
            $codigoGanadero, $telefono, $email, $fechaRegistro, $estado, $id_usuario,
            $identificacion_fiscal_titular
        );

        $result = false;
        $newId = false;
        try {
            $result = $stmt->execute();
            if ($result) {
                $newId = $conn->insert_id;
            } else {
                error_log("Execute failed (Socio store): " . $stmt->error);
                if ($conn->errno == 1062) { // Error de duplicado
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
        return $result ? $newId : false;
    }

    public static function getAll() {
        $conn = dbConnect();
        $socios = [];
        if (!$conn) {
            $_SESSION['error_details'] = 'Error de conexión a la base de datos al obtener socios.';
            return $socios;
        }
        // CORREGIDO: Ordenar por id_socio ASC para reflejar el orden de captura (número)
        $query = "SELECT * FROM socios ORDER BY id_socio ASC"; 
        $result = $conn->query($query);
        if($result) {
            while($row = $result->fetch_assoc()){
                $socios[] = $row;
            }
            $result->free();
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

    public static function update($id, $data) {
        $conn = dbConnect();
        if (!$conn) {
            $_SESSION['error_details'] = 'Error de conexión a la base de datos al intentar actualizar el socio.';
            return false;
        }
        // CORREGIDO: Ajustado el SQL para que coincida con las columnas existentes en tu BD
        $sql = "UPDATE socios SET
                nombre = ?, apellido_paterno = ?, apellido_materno = ?, nombre_ganaderia = ?, direccion = ?,
                codigoGanadero = ?, telefono = ?, email = ?, fechaRegistro = ?, estado = ?, id_usuario = ?,
                identificacion_fiscal_titular = ?
                WHERE id_socio = ?"; // 12 columnas en SET + 1 en WHERE = 13 placeholders

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("Prepare failed (Socio update): " . $conn->error);
            $_SESSION['error_details'] = 'Error interno al preparar la actualización del socio: ' . $conn->error;
            $conn->close();
            return false;
        }

        // Asignar valores a variables para bind_param
        // Asegúrate de que el orden y tipo de estas variables coincidan con el SQL de arriba
        $nombre = $data['nombre'];
        $apellido_paterno = $data['apellido_paterno'];
        $apellido_materno = $data['apellido_materno'];
        $nombre_ganaderia = $data['nombre_ganaderia'] ?: null;
        $direccion = $data['direccion'] ?: null;
        $codigoGanadero = $data['codigoGanadero'] ?: null;
        $telefono = $data['telefono'] ?: null;
        $email = $data['email'] ?: null;
        $fechaRegistro = $data['fechaRegistro'] ?: null; // Puede ser null si no se actualiza o no es obligatorio
        $estado = $data['estado'];
        $id_usuario = $data['id_usuario']; // Usuario que edita
        $identificacion_fiscal_titular = $data['identificacion_fiscal_titular'] ?: null;
        $id_socio = $id; // ID del socio a editar

        // 13 tipos: 12 strings (s), 1 int (i)
        $types = "sssssssssssi"; // 12 s, 1 i (para id_usuario)
        $types .= "i"; // Añadir 'i' para el id_socio del WHERE
        
        $stmt->bind_param($types,
            $nombre, $apellido_paterno, $apellido_materno, $nombre_ganaderia, $direccion,
            $codigoGanadero, $telefono, $email, $fechaRegistro, $estado, $id_usuario,
            $identificacion_fiscal_titular, $id_socio
        );

        $result = false;
        try {
            $result = $stmt->execute();
            if (!$result) {
                error_log("Execute failed (Socio update): " . $stmt->error);
                if ($conn->errno == 1062) { // Error de duplicado
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

    public static function delete($id) {
        $conn = dbConnect();
        if (!$conn) {
            $_SESSION['error_details'] = 'Error de conexión a la base de datos al intentar eliminar el socio.';
            return false;
        }
        $sql = "DELETE FROM socios WHERE id_socio = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("Prepare failed (Socio delete): " . $conn->error);
            $_SESSION['error_details'] = 'Error interno al preparar la eliminación del socio.';
            $conn->close();
            return false;
        }
        $stmt->bind_param("i", $id);

        $result = false;
        try {
            $result = $stmt->execute();
            if (!$result) {
                error_log("Execute failed (Socio delete): " . $stmt->error);
                if ($conn->errno == 1451) { // Código de error para FK
                    $_SESSION['error_details'] = 'No se puede eliminar el socio, tiene ejemplares o servicios asociados.';
                } else {
                    $_SESSION['error_details'] = 'Error de base de datos al eliminar el socio: ' . $stmt->error;
                }
            }
        } catch (mysqli_sql_exception $e) {
            error_log("Exception (Socio delete): " . $e->getMessage());
            $_SESSION['error_details'] = 'Error de base de datos al eliminar el socio (' . $e->getCode() . '): ' . $e->getMessage();
        }

        $stmt->close();
        $conn->close();
        return $result;
    }

    /**
     * Obtiene una lista de socios activos formateada para usar en selects (<select>).
     * @return array Array asociativo [id_socio => 'Nombre Completo (Código Ganadero)'].
     */
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
                  ORDER BY apellido_paterno, apellido_materno, nombre"; // Mantener orden alfabético para select
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