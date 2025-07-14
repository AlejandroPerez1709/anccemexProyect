<?php
// app/models/User.php
require_once __DIR__ . '/../../config/config.php';

class User {

    public static function getByUsername($username) {
        $conn = dbConnect();
        if (!$conn) {
            $_SESSION['error_details'] = 'Error de conexión a la base de datos al buscar usuario por nombre de usuario.';
            return null;
        }
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE username = ? AND estado = 'activo' LIMIT 1");
        if (!$stmt) {
            error_log("Prepare failed (User getByUsername): " . $conn->error);
            $_SESSION['error_details'] = 'Error interno al preparar la búsqueda de usuario por nombre de usuario.';
            $conn->close();
            return null;
        }
        $stmt->bind_param("s", $username);
        $executeResult = $stmt->execute();
        $user = null;
        if ($executeResult) {
            $result = $stmt->get_result();
            if($result->num_rows == 1){
                $user = $result->fetch_assoc();
            } else {
                error_log("Usuario con username '$username' no encontrado o inactivo.");
            }
            if ($result) $result->free(); // Liberar memoria
        } else {
            error_log("Execute failed (User getByUsername): " . $stmt->error);
            $_SESSION['error_details'] = 'Error de base de datos al buscar usuario por nombre de usuario: ' . $stmt->error;
        }
        $stmt->close();
        $conn->close();
        return $user;
    }

    public static function updateLastLogin($user_id){
        $conn = dbConnect();
        if (!$conn) {
            error_log("Error de conexión a la base de datos al actualizar último login para user ID $user_id.");
            return;
        }
        $stmt = $conn->prepare("UPDATE usuarios SET ultimo_login = NOW() WHERE id_usuario = ?");
        if (!$stmt) {
            error_log("Prepare failed (User updateLastLogin): " . $conn->error);
            $conn->close();
            return;
        }
        $stmt->bind_param("i", $user_id);
        if (!$stmt->execute()) {
            error_log("Execute failed (User updateLastLogin): " . $stmt->error);
        }
        $stmt->close();
        $conn->close();
    }

    /**
     * Guarda un nuevo usuario en la base de datos.
     * @param array $data Datos del usuario (nombre, apellido_paterno, etc.)
     * @return bool True si se guardó correctamente, False en caso contrario.
     */
    public static function store($data) {
        $conn = dbConnect();
        if (!$conn) {
            $_SESSION['error_details'] = 'Error de conexión a la base de datos al intentar guardar el usuario.';
            return false;
        }
        // Hashear la contraseña antes de guardarla
        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
        if (!$hashed_password) {
             error_log("Error al hashear la contraseña para el usuario: " . $data['username']);
             $_SESSION['error_details'] = 'Error interno al procesar la contraseña.';
             $conn->close();
             return false;
        }

        $stmt = $conn->prepare("INSERT INTO usuarios (nombre, apellido_paterno, apellido_materno, email, username, password, rol, estado, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())"); // Añadido created_at
        if (!$stmt) {
            error_log("Prepare failed (User store): " . $conn->error);
            $_SESSION['error_details'] = 'Error interno al preparar la inserción del usuario.';
            $conn->close();
            return false;
        }
        $stmt->bind_param("ssssssss",
            $data['nombre'],
            $data['apellido_paterno'],
            $data['apellido_materno'],
            $data['email'],
            $data['username'],
            $hashed_password, // Guardar la contraseña hasheada
            $data['rol'],
            $data['estado']
        );
        $result = false;
        try {
            $result = $stmt->execute();
            if (!$result) {
                error_log("Execute failed (User store): " . $stmt->error);
                if ($conn->errno == 1062) { // Error de duplicado
                    $_SESSION['error_details'] = 'Ya existe un usuario con el mismo Email o Nombre de Usuario.';
                } else {
                    $_SESSION['error_details'] = 'Error de base de datos al guardar el usuario: ' . $stmt->error;
                }
            }
        } catch (mysqli_sql_exception $e) {
             error_log("Error al insertar usuario: " . $e->getMessage());
             $_SESSION['error_details'] = 'Error de base de datos al guardar el usuario (' . $e->getCode() . '): ' . $e->getMessage();
        }

        $stmt->close();
        $conn->close();
        return $result;
    }

    /**
     * Obtiene todos los usuarios de la base de datos.
     * @return array Lista de usuarios.
     */
    public static function getAll() {
        $conn = dbConnect();
        $usuarios = [];
        if (!$conn) {
            $_SESSION['error_details'] = 'Error de conexión a la base de datos al obtener usuarios.';
            return $usuarios;
        }
        // CORREGIDO: Ordenar por id_usuario ASC (del más bajo al más alto) para reflejar orden de captura
        $query = "SELECT id_usuario, nombre, apellido_paterno, apellido_materno, email, username, rol, estado, created_at, ultimo_login FROM usuarios ORDER BY id_usuario ASC"; 
        $result = $conn->query($query);
        if($result) {
            while($row = $result->fetch_assoc()){
                $usuarios[] = $row;
            }
            $result->free(); // Liberar memoria del resultado
        } else {
            error_log("Error al obtener usuarios: " . $conn->error);
            $_SESSION['error_details'] = 'Error de base de datos al obtener usuarios: ' . $conn->error;
        }
        $conn->close();
        return $usuarios;
    }

    /**
     * Obtiene un usuario específico por su ID.
     * @param int $id ID del usuario.
     * @return array|null Datos del usuario o null si no se encuentra.
     */
    public static function getById($id) {
        $conn = dbConnect();
        if (!$conn) {
            $_SESSION['error_details'] = 'Error de conexión a la base de datos al obtener usuario por ID.';
            return null;
        }
        // No seleccionamos la contraseña por seguridad
        $stmt = $conn->prepare("SELECT id_usuario, nombre, apellido_paterno, apellido_materno, email, username, rol, estado FROM usuarios WHERE id_usuario = ? LIMIT 1");
        if (!$stmt) {
            error_log("Prepare failed (User getById): " . $conn->error);
            $_SESSION['error_details'] = 'Error interno al preparar la obtención del usuario.';
            $conn->close();
            return null;
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $usuario = null;
        if($result->num_rows == 1){
            $usuario = $result->fetch_assoc();
        } else {
            error_log("Usuario con ID $id no encontrado.");
            $_SESSION['error_details'] = "Usuario no encontrado con el ID proporcionado.";
        }
        $stmt->close();
        $conn->close();
        return $usuario;
    }

    /**
     * Actualiza los datos de un usuario existente.
     * @param int $id ID del usuario a actualizar.
     * @param array $data Nuevos datos del usuario.
     * @return bool True si se actualizó correctamente, False en caso contrario.
     */
    public static function update($id, $data) {
        $conn = dbConnect();
        if (!$conn) {
            $_SESSION['error_details'] = 'Error de conexión a la base de datos al intentar actualizar el usuario.';
            return false;
        }
        // Verificar si se proporcionó una nueva contraseña
        if (isset($data['password']) && !empty($data['password'])) {
            // Hay una nueva contraseña, hashearla
            $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
            if (!$hashed_password) {
                 error_log("Error al hashear la nueva contraseña para el usuario ID: " . $id);
                 $_SESSION['error_details'] = 'Error interno al procesar la nueva contraseña.';
                 $conn->close();
                 return false;
            }
            // Actualizar con contraseña
            $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, apellido_paterno = ?, apellido_materno = ?, email = ?, username = ?, password = ?, rol = ?, estado = ? WHERE id_usuario = ?");
            $types = "ssssssssi"; // 9 tipos de datos
            $bind_params = [
                $data['nombre'],
                $data['apellido_paterno'],
                $data['apellido_materno'],
                $data['email'],
                $data['username'],
                $hashed_password, // Usar la nueva contraseña hasheada
                $data['rol'],
                $data['estado'],
                $id
            ];
        } else {
            // No se proporcionó contraseña nueva, actualizar sin ella
            $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, apellido_paterno = ?, apellido_materno = ?, email = ?, username = ?, rol = ?, estado = ? WHERE id_usuario = ?");
            $types = "sssssssi"; // 8 tipos de datos
            $bind_params = [
                $data['nombre'],
                $data['apellido_paterno'],
                $data['apellido_materno'],
                $data['email'],
                $data['username'],
                $data['rol'],
                $data['estado'],
                $id
            ];
        }

        if (!$stmt) {
            error_log("Prepare failed (User update): " . $conn->error);
            $_SESSION['error_details'] = 'Error interno al preparar la actualización del usuario.';
            $conn->close();
            return false;
        }

        $stmt->bind_param($types, ...$bind_params);
        $result = false;
        try {
            $result = $stmt->execute();
            if (!$result) {
                 error_log("Execute failed (User update): " . $stmt->error);
                 if ($conn->errno == 1062) { // Error de duplicado
                     $_SESSION['error_details'] = 'Ya existe un usuario con el mismo Email o Nombre de Usuario.';
                 } else {
                     $_SESSION['error_details'] = 'Error de base de datos al actualizar el usuario: ' . $stmt->error;
                 }
            }
        } catch (mysqli_sql_exception $e) {
             error_log("Error al actualizar usuario ID $id: " . $e->getMessage());
             $_SESSION['error_details'] = 'Error de base de datos al actualizar el usuario (' . $e->getCode() . '): ' . $e->getMessage();
             $result = false;
        }

        $stmt->close();
        $conn->close();
        return $result;
    }

    /**
     * Elimina un usuario por su ID.
     * @param int $id ID del usuario a eliminar.
     * @return bool True si se eliminó correctamente, False en caso contrario.
     */
    public static function delete($id) {
        $conn = dbConnect();
        if (!$conn) {
            $_SESSION['error_details'] = 'Error de conexión a la base de datos al intentar eliminar el usuario.';
            return false;
        }
        // Aquí implementamos la eliminación física:
        $stmt = $conn->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
        if (!$stmt) {
            error_log("Prepare failed (User delete): " . $conn->error);
            $_SESSION['error_details'] = 'Error interno al preparar la eliminación del usuario.';
            $conn->close();
            return false;
        }
        $stmt->bind_param("i", $id);

        $result = false;
        try {
            $result = $stmt->execute();
            if (!$result) {
                error_log("Execute failed (User delete): " . $stmt->error);
                if ($conn->errno == 1451) { // Código de error para Foreign Key Constraint
                    $_SESSION['error_details'] = 'No se puede eliminar el usuario porque tiene registros asociados en otras tablas (ej. es responsable de registros).';
                } else {
                    $_SESSION['error_details'] = 'Error de base de datos al eliminar el usuario: ' . $stmt->error;
                }
            }
        } catch (mysqli_sql_exception $e) {
             error_log("Exception (User delete): " . $e->getMessage());
             $_SESSION['error_details'] = 'Error de base de datos al eliminar el usuario (' . $e->getCode() . '): ' . $e->getMessage();
        }

        $stmt->close();
        $conn->close();
        return $result;
    }
}