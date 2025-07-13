<?php
// app/models/Empleado.php
require_once __DIR__ . '/../../config/config.php';

class Empleado {
    public static function store($data) {
        $conn = dbConnect();
        $stmt = $conn->prepare("INSERT INTO empleados (nombre, apellido_paterno, apellido_materno, email, direccion, telefono, puesto, fecha_ingreso) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
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
        $result = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $result;
    }
    
    public static function getAll() {
        $conn = dbConnect();
        $query = "SELECT * FROM empleados";
        $result = $conn->query($query);
        $empleados = [];
        if($result) {
            while($row = $result->fetch_assoc()){
                $empleados[] = $row;
            }
        }
        $conn->close();
        return $empleados;
    }
    
    public static function getById($id) {
        $conn = dbConnect();
        $stmt = $conn->prepare("SELECT * FROM empleados WHERE id_empleado = ? LIMIT 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $empleado = null;
        if($result->num_rows == 1){
            $empleado = $result->fetch_assoc();
        }
        $stmt->close();
        $conn->close();
        return $empleado;
    }
    
    public static function update($id, $data) {
        $conn = dbConnect();
        $stmt = $conn->prepare("UPDATE empleados SET nombre = ?, apellido_paterno = ?, apellido_materno = ?, email = ?, direccion = ?, telefono = ?, puesto = ?, fecha_ingreso = ? WHERE id_empleado = ?");
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
        $result = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $result;
    }
    
    public static function delete($id) {
        $conn = dbConnect();
        $stmt = $conn->prepare("DELETE FROM empleados WHERE id_empleado = ?");
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $result;
    }
}
?>