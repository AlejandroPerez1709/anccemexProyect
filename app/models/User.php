<?php
// app/models/User.php
require_once __DIR__ . '/../../config/config.php';

class User {

    public static function getByUsername($username) {
        $conn = dbConnect();
        if (!$conn) return null;
        
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE username = ? AND estado = 'activo' LIMIT 1");
        if (!$stmt) {
            $conn->close();
            return null;
        }
        
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result ? $result->fetch_assoc() : null;
        
        $stmt->close();
        $conn->close();
        return $user;
    }

    public static function updateLastLogin($user_id){
        $conn = dbConnect();
        if (!$conn) return;

        $stmt = $conn->prepare("UPDATE usuarios SET ultimo_login = NOW() WHERE id_usuario = ?");
        if ($stmt) {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();
        }
        $conn->close();
    }

    public static function store($data) {
        $conn = dbConnect();
        if (!$conn) return false;

        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
        if (!$hashed_password) {
             $_SESSION['error_details'] = 'Error interno al procesar la contraseña.';
             $conn->close();
             return false;
        }

        $stmt = $conn->prepare("INSERT INTO usuarios (nombre, apellido_paterno, apellido_materno, email, username, password, rol, estado, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        if (!$stmt) {
            $_SESSION['error_details'] = 'Error interno al preparar la inserción del usuario.';
            $conn->close();
            return false;
        }
        
        $stmt->bind_param("ssssssss",
            $data['nombre'], $data['apellido_paterno'], $data['apellido_materno'], $data['email'],
            $data['username'], $hashed_password, $data['rol'], $data['estado']
        );
        $result = $stmt->execute();
        if (!$result && $conn->errno == 1062) {
            $_SESSION['error_details'] = 'Ya existe un usuario con el mismo Email o Nombre de Usuario.';
        }

        $stmt->close();
        $conn->close();
        return $result;
    }

    public static function countAll($searchTerm = '') {
        $conn = dbConnect();
        if (!$conn) return 0;

        $query = "SELECT COUNT(id_usuario) as total FROM usuarios";
        $params = [];
        $types = '';
        if (!empty($searchTerm)) {
            $query .= " WHERE nombre LIKE ? OR apellido_paterno LIKE ? OR apellido_materno LIKE ? OR email LIKE ? OR username LIKE ?";
            $searchTermWildcard = "%" . $searchTerm . "%";
            $params = [$searchTermWildcard, $searchTermWildcard, $searchTermWildcard, $searchTermWildcard, $searchTermWildcard];
            $types = 'sssss';
        }

        $stmt = $conn->prepare($query);
        if ($stmt) {
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            $total = $result->fetch_assoc()['total'];
            $stmt->close();
        } else {
            $total = 0;
        }
        
        $conn->close();
        return $total;
    }

    public static function getAll($searchTerm = '', $limit = 15, $offset = 0) {
        $conn = dbConnect();
        $usuarios = [];
        if (!$conn) return $usuarios;

        $query = "SELECT id_usuario, nombre, apellido_paterno, apellido_materno, email, username, rol, estado, created_at, ultimo_login FROM usuarios";
        $params = [];
        $types = '';

        if (!empty($searchTerm)) {
            $query .= " WHERE nombre LIKE ? OR apellido_paterno LIKE ? OR apellido_materno LIKE ? OR email LIKE ? OR username LIKE ?";
            $searchTermWildcard = "%" . $searchTerm . "%";
            $params = [$searchTermWildcard, $searchTermWildcard, $searchTermWildcard, $searchTermWildcard, $searchTermWildcard];
            $types = 'sssss';
        }

        if ($limit != -1) {
            $query .= " ORDER BY id_usuario ASC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            $types .= 'ii';
        } else {
            $query .= " ORDER BY id_usuario ASC";
        }
        
        $stmt = $conn->prepare($query);
        if ($stmt) {
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            if($result) {
                while($row = $result->fetch_assoc()){
                    $usuarios[] = $row;
                }
                $result->free();
            }
            $stmt->close();
        }

        $conn->close();
        return $usuarios;
    }

    public static function getById($id) {
        $conn = dbConnect();
        if (!$conn) return null;

        $stmt = $conn->prepare("SELECT id_usuario, nombre, apellido_paterno, apellido_materno, email, username, rol, estado, razon_desactivacion FROM usuarios WHERE id_usuario = ? LIMIT 1");
        if (!$stmt) {
            $conn->close();
            return null;
        }
        
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $usuario = $result ? $result->fetch_assoc() : null;
        
        $stmt->close();
        $conn->close();
        return $usuario;
    }

    public static function update($id, $data) {
        $conn = dbConnect();
        if (!$conn) return false;
    
        // Iniciar la construcción de la consulta y los parámetros
        $sql = "UPDATE usuarios SET nombre = ?, apellido_paterno = ?, apellido_materno = ?, email = ?, username = ?, rol = ?, estado = ?";
        $params = [
            $data['nombre'],
            $data['apellido_paterno'],
            $data['apellido_materno'],
            $data['email'],
            $data['username'],
            $data['rol'],
            $data['estado']
        ];
        $types = 'sssssss';
    
        // Añadir la contraseña a la consulta SOLO si se proporcionó una nueva
        if (isset($data['password']) && !empty($data['password'])) {
            $sql .= ", password = ?";
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
            $types .= 's';
        }
    
        // Limpiar la razón de desactivación SOLO si el estado es 'activo'
        if (isset($data['estado']) && $data['estado'] == 'activo') {
            $sql .= ", razon_desactivacion = NULL";
        }
    
        // Finalizar la consulta con la cláusula WHERE
        $sql .= " WHERE id_usuario = ?";
        $params[] = $id;
        $types .= 'i';
    
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("Error al preparar la actualización de usuario: " . $conn->error);
            $conn->close();
            return false;
        }
        
        // Vincular los parámetros y ejecutar
        $stmt->bind_param($types, ...$params);
        $result = $stmt->execute();
        
        if (!$result) {
            error_log("Error al ejecutar la actualización de usuario: " . $stmt->error);
            if ($conn->errno == 1062) {
                 $_SESSION['error_details'] = 'Ya existe un usuario con el mismo Email o Nombre de Usuario.';
            }
        }
    
        $stmt->close();
        $conn->close();
        return $result;
    }

    public static function delete($id, $razon) {
        $conn = dbConnect();
        if (!$conn) return false;

        $stmt = $conn->prepare("UPDATE usuarios SET estado = 'inactivo', razon_desactivacion = ? WHERE id_usuario = ?");
        if (!$stmt) return false;

        $stmt->bind_param("si", $razon, $id);
        $result = $stmt->execute();
        
        $stmt->close();
        $conn->close();
        return $result;
    }
}