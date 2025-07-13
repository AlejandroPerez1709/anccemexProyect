<?php
// app/models/User.php
require_once __DIR__ . '/../../config/config.php';

class User {

    // --- Métodos existentes ---
    public static function getByUsername($username) {
        $conn = dbConnect();
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE username = ? AND estado = 'activo' LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = null;
        if($result->num_rows == 1){
            $user = $result->fetch_assoc();
        }
        $stmt->close();
        $conn->close();
        return $user;
    }

    public static function updateLastLogin($user_id){
        $conn = dbConnect();
        $stmt = $conn->prepare("UPDATE usuarios SET ultimo_login = NOW() WHERE id_usuario = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
        $conn->close();
    }

    // --- NUEVOS Métodos CRUD ---

    /**
     * Guarda un nuevo usuario en la base de datos.
     * @param array $data Datos del usuario (nombre, apellido_paterno, etc.)
     * @return bool True si se guardó correctamente, False en caso contrario.
     */
    public static function store($data) {
        $conn = dbConnect();

        // Hashear la contraseña antes de guardarla
        // ¡IMPORTANTE! Asegúrate de que tu servidor PHP tenga habilitada la extensión de hashing.
        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
        if (!$hashed_password) {
             // Manejar error de hashing si es necesario
             error_log("Error al hashear la contraseña para el usuario: " . $data['username']);
             $conn->close();
             return false;
        }


        $stmt = $conn->prepare("INSERT INTO usuarios (nombre, apellido_paterno, apellido_materno, email, username, password, rol, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        // 'ssssssss' corresponde a los tipos de datos: string, string, string, string, string, string, string, string
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
        } catch (mysqli_sql_exception $e) {
            // Capturar errores de duplicados (email o username)
             error_log("Error al insertar usuario: " . $e->getMessage());
             // Podrías verificar $e->getCode() si necesitas diferenciar errores específicos (ej. 1062 para duplicados)
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
        $query = "SELECT id_usuario, nombre, apellido_paterno, apellido_materno, email, username, rol, estado, created_at, ultimo_login FROM usuarios"; // No seleccionamos la contraseña
        $result = $conn->query($query);
        $usuarios = [];
        if($result) {
            while($row = $result->fetch_assoc()){
                $usuarios[] = $row;
            }
            $result->free(); // Liberar memoria del resultado
        } else {
            error_log("Error al obtener usuarios: " . $conn->error);
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
        // No seleccionamos la contraseña por seguridad
        $stmt = $conn->prepare("SELECT id_usuario, nombre, apellido_paterno, apellido_materno, email, username, rol, estado FROM usuarios WHERE id_usuario = ? LIMIT 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $usuario = null;
        if($result->num_rows == 1){
            $usuario = $result->fetch_assoc();
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

        // Verificar si se proporcionó una nueva contraseña
        if (isset($data['password']) && !empty($data['password'])) {
            // Hay una nueva contraseña, hashearla
            $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
             if (!$hashed_password) {
                 error_log("Error al hashear la nueva contraseña para el usuario ID: " . $id);
                 $conn->close();
                 return false;
            }
            // Actualizar con contraseña
            $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, apellido_paterno = ?, apellido_materno = ?, email = ?, username = ?, password = ?, rol = ?, estado = ? WHERE id_usuario = ?");
            // 'ssssssssi' corresponde a los tipos de datos
            $stmt->bind_param("ssssssssi",
                $data['nombre'],
                $data['apellido_paterno'],
                $data['apellido_materno'],
                $data['email'],
                $data['username'],
                $hashed_password, // Usar la nueva contraseña hasheada
                $data['rol'],
                $data['estado'],
                $id
            );
        } else {
            // No se proporcionó contraseña nueva, actualizar sin ella
            $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, apellido_paterno = ?, apellido_materno = ?, email = ?, username = ?, rol = ?, estado = ? WHERE id_usuario = ?");
             // 'sssssssi' corresponde a los tipos de datos (sin password)
            $stmt->bind_param("sssssssi",
                $data['nombre'],
                $data['apellido_paterno'],
                $data['apellido_materno'],
                $data['email'],
                $data['username'],
                $data['rol'],
                $data['estado'],
                $id
            );
        }

        $result = false;
        try {
            $result = $stmt->execute();
        } catch (mysqli_sql_exception $e) {
             // Capturar errores de duplicados (email o username)
             error_log("Error al actualizar usuario ID $id: " . $e->getMessage());
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
        // Considerar si realmente se quiere eliminar o solo marcar como 'inactivo'
        // Aquí implementamos la eliminación física:
        $stmt = $conn->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
        $stmt->bind_param("i", $id);

        $result = false;
         try {
             $result = $stmt->execute();
         } catch (mysqli_sql_exception $e) {
             // Capturar errores (ej. si el usuario tiene registros relacionados que impiden borrarlo por FK)
             error_log("Error al eliminar usuario ID $id: " . $e->getMessage());
         }

        $stmt->close();
        $conn->close();
        return $result;
    }

    // --- Fin de Métodos CRUD ---
}
?>