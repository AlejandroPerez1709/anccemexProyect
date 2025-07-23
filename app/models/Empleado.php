<?php
// app/models/Empleado.php
require_once __DIR__ . '/../../config/config.php';

class Empleado {
    public static function store($data) {
        $conn = dbConnect();
        if (!$conn) {
            $_SESSION['error_details'] = 'Error de conexión a la base de datos.';
            return false;
        }
        $stmt = $conn->prepare("INSERT INTO empleados (nombre, apellido_paterno, apellido_materno, email, direccion, telefono, puesto, estado, fecha_ingreso) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            error_log("Prepare failed (Empleado store): " . $conn->error);
            $conn->close();
            return false;
        }
        $stmt->bind_param("sssssssss", 
            $data['nombre'], $data['apellido_paterno'], $data['apellido_materno'], $data['email'], 
            $data['direccion'], $data['telefono'], $data['puesto'], $data['estado'], $data['fecha_ingreso']
        );
        
        $result = $stmt->execute();
        if (!$result && $conn->errno == 1062) {
            $_SESSION['error_details'] = 'El email proporcionado ya existe para otro empleado.';
        }
        
        $stmt->close();
        $conn->close();
        return $result;
    }
    
    public static function getAll($searchTerm = '') {
        $conn = dbConnect();
        $empleados = [];
        if (!$conn) return $empleados;

        $query = "SELECT * FROM empleados";
        $params = [];
        $types = '';

        if (!empty($searchTerm)) {
            $query .= " WHERE nombre LIKE ? OR apellido_paterno LIKE ? OR apellido_materno LIKE ? OR email LIKE ? OR puesto LIKE ?";
            $searchTermWildcard = "%" . $searchTerm . "%";
            $params = [$searchTermWildcard, $searchTermWildcard, $searchTermWildcard, $searchTermWildcard, $searchTermWildcard];
            $types = 'sssss';
        }

        $query .= " ORDER BY id_empleado ASC";
        
        $stmt = $conn->prepare($query);
        if ($stmt) {
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result) {
                while($row = $result->fetch_assoc()){
                    $empleados[] = $row;
                }
                $result->free();
            }
            $stmt->close();
        }
        
        $conn->close();
        return $empleados;
    }
    
    public static function getById($id) {
        $conn = dbConnect();
        if (!$conn) return null;

        $stmt = $conn->prepare("SELECT * FROM empleados WHERE id_empleado = ? LIMIT 1");
        if (!$stmt) {
            $conn->close();
            return null;
        }
        
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $empleado = $result ? $result->fetch_assoc() : null;
        
        $stmt->close();
        $conn->close();
        return $empleado;
    }
    
    public static function update($id, $data) {
        $conn = dbConnect();
        if (!$conn) return false;

        $sql = "UPDATE empleados SET nombre = ?, apellido_paterno = ?, apellido_materno = ?, email = ?, direccion = ?, telefono = ?, puesto = ?, estado = ?, fecha_ingreso = ?";
        
        // Si el estado se está cambiando a 'activo', se limpia la razón de desactivación
        if (isset($data['estado']) && $data['estado'] == 'activo') {
            $sql .= ", razon_desactivacion = NULL";
        }

        $sql .= " WHERE id_empleado = ?";

        $stmt = $conn->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param("sssssssssi", 
            $data['nombre'], $data['apellido_paterno'], $data['apellido_materno'], $data['email'], 
            $data['direccion'], $data['telefono'], $data['puesto'], $data['estado'], $data['fecha_ingreso'],
            $id
        );
        
        $result = $stmt->execute();
        if (!$result && $conn->errno == 1062) { 
            $_SESSION['error_details'] = 'El email proporcionado ya existe para otro empleado.';
        }
        
        $stmt->close();
        $conn->close();
        return $result;
    }
    
    public static function delete($id, $razon) {
        $conn = dbConnect();
        if (!$conn) return false;

        $stmt = $conn->prepare("UPDATE empleados SET estado = 'inactivo', razon_desactivacion = ? WHERE id_empleado = ?");
        if (!$stmt) return false;

        $stmt->bind_param("si", $razon, $id);
        $result = $stmt->execute();
        
        if (!$result && $conn->errno == 1451) {
            $_SESSION['error_details'] = 'No se puede desactivar el empleado porque tiene registros asociados.';
        }
        
        $stmt->close();
        $conn->close();
        return $result;
    }
}