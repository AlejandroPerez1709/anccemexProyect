<?php
// app/models/Empleado.php
require_once __DIR__ . '/../../config/config.php';

class Empleado {
    public static function store($data) {
        $conn = dbConnect();
        if (!$conn) {
            $_SESSION['error_details'] = 'Error de conexión a la base de datos al intentar guardar el empleado.';
            return false;
        }
        $stmt = $conn->prepare("INSERT INTO empleados (nombre, apellido_paterno, apellido_materno, email, direccion, telefono, puesto, fecha_ingreso) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            error_log("Prepare failed (Empleado store): " . $conn->error);
            $_SESSION['error_details'] = 'Error interno al preparar la inserción del empleado.';
            $conn->close();
            return false;
        }
        $stmt->bind_param("ssssssss", 
            $data['nombre'], 
            $data['apellido_paterno'], 
            $data['apellido_materno'], 
            $data['email'], 
            $data['direccion'], 
            $data['telefono'], 
            $data['puesto'],
            $data['fecha_ingreso']
        );
        $result = false;
        try {
            $result = $stmt->execute();
            if (!$result) {
                error_log("Execute failed (Empleado store): " . $stmt->error);
                // Ejemplo de manejo de error de duplicado (si el email fuera UNIQUE)
                if ($conn->errno == 1062) { // Código de error para entrada duplicada
                    $_SESSION['error_details'] = 'El email proporcionado ya existe para otro empleado.';
                } else {
                    $_SESSION['error_details'] = 'Error de base de datos al guardar el empleado: ' . $stmt->error;
                }
            }
        } catch (mysqli_sql_exception $e) {
            error_log("Exception (Empleado store): " . $e->getMessage());
            $_SESSION['error_details'] = 'Error de base de datos al guardar el empleado (' . $e->getCode() . '): ' . $e->getMessage();
            $result = false;
        }
        $stmt->close();
        $conn->close();
        return $result;
    }
    
    public static function getAll() {
        $conn = dbConnect();
        $empleados = [];
        if (!$conn) {
            $_SESSION['error_details'] = 'Error de conexión a la base de datos al obtener empleados.';
            return $empleados;
        }
        // CORREGIDO: Ordenar por id_empleado ASC (del más bajo al más alto)
        $query = "SELECT * FROM empleados ORDER BY id_empleado ASC"; 
        $result = $conn->query($query);
        if($result) {
            while($row = $result->fetch_assoc()){
                $empleados[] = $row;
            }
            $result->free();
        } else {
            error_log("Error query (Empleado getAll): " . $conn->error);
            $_SESSION['error_details'] = 'Error de base de datos al obtener empleados: ' . $conn->error;
        }
        $conn->close();
        return $empleados;
    }
    
    public static function getById($id) {
        $conn = dbConnect();
        if (!$conn) {
            $_SESSION['error_details'] = 'Error de conexión a la base de datos al obtener empleado por ID.';
            return null;
        }
        $stmt = $conn->prepare("SELECT * FROM empleados WHERE id_empleado = ? LIMIT 1");
        if (!$stmt) {
            error_log("Prepare failed (Empleado getById): " . $conn->error);
            $_SESSION['error_details'] = 'Error interno al preparar la obtención del empleado.';
            $conn->close();
            return null;
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $empleado = null;
        if($result->num_rows == 1){
            $empleado = $result->fetch_assoc();
        } else {
            error_log("Empleado con ID $id no encontrado.");
            $_SESSION['error_details'] = "Empleado no encontrado con el ID proporcionado.";
        }
        $stmt->close();
        $conn->close();
        return $empleado;
    }
    
    public static function update($id, $data) {
        $conn = dbConnect();
        if (!$conn) {
            $_SESSION['error_details'] = 'Error de conexión a la base de datos al intentar actualizar el empleado.';
            return false;
        }
        $sql = "UPDATE empleados SET nombre = ?, apellido_paterno = ?, apellido_materno = ?, email = ?, direccion = ?, telefono = ?, puesto = ?, fecha_ingreso = ? WHERE id_empleado = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("Prepare failed (Empleado update): " . $conn->error);
            $_SESSION['error_details'] = 'Error interno al preparar la actualización del empleado.';
            $conn->close();
            return false;
        }
        $stmt->bind_param("ssssssssi", 
            $data['nombre'], 
            $data['apellido_paterno'], 
            $data['apellido_materno'], 
            $data['email'], 
            $data['direccion'], 
            $data['telefono'], 
            $data['puesto'],
            $data['fecha_ingreso'],
            $id
        );
        $result = false;
        try {
            $result = $stmt->execute();
            if (!$result) {
                error_log("Execute failed (Empleado update): " . $stmt->error);
                // Manejo de error de duplicado, si el email fuera UNIQUE
                if ($conn->errno == 1062) { 
                    $_SESSION['error_details'] = 'El email proporcionado ya existe para otro empleado.';
                } else {
                    $_SESSION['error_details'] = 'Error de base de datos al actualizar el empleado: ' . $stmt->error;
                }
            }
        } catch (mysqli_sql_exception $e) {
            error_log("Exception (Empleado update): " . $e->getMessage());
            $_SESSION['error_details'] = 'Error de base de datos al actualizar el empleado (' . $e->getCode() . '): ' . $e->getMessage();
            $result = false;
        }
        $stmt->close();
        $conn->close();
        return $result;
    }
    
    public static function delete($id) {
        $conn = dbConnect();
        if (!$conn) {
            $_SESSION['error_details'] = 'Error de conexión a la base de datos al intentar eliminar el empleado.';
            return false;
        }
        $stmt = $conn->prepare("DELETE FROM empleados WHERE id_empleado = ?");
        if (!$stmt) {
            error_log("Prepare failed (Empleado delete): " . $conn->error);
            $_SESSION['error_details'] = 'Error interno al preparar la eliminación del empleado.';
            $conn->close();
            return false;
        }
        $stmt->bind_param("i", $id);
        $result = false;
        try {
            $result = $stmt->execute();
            if (!$result) {
                error_log("Execute failed (Empleado delete): " . $stmt->error);
                if ($conn->errno == 1451) { // Código de error para Foreign Key Constraint (empleado tiene registros asociados)
                    $_SESSION['error_details'] = 'No se puede eliminar el empleado porque tiene registros asociados en otras tablas (ej. es usuario que registró algo).';
                } else {
                    $_SESSION['error_details'] = 'Error de base de datos al eliminar el empleado: ' . $stmt->error;
                }
            }
        } catch (mysqli_sql_exception $e) { 
            error_log("Exception (Empleado delete): " . $e->getMessage());
            $_SESSION['error_details'] = 'Error de base de datos al eliminar el empleado (' . $e->getCode() . '): ' . $e->getMessage();
            $result = false;
        }
        $stmt->close();
        $conn->close();
        return $result;
    }
}