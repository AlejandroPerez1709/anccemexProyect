<?php
// app/models/Servicio.php
require_once __DIR__ . '/../../config/config.php';

class Servicio {

    // ... (método store se mantiene igual) ...
    public static function store($data) {
        $conn = dbConnect();
        if (!$conn) {
            $_SESSION['error_details'] = 'Error de conexión a la base de datos al intentar guardar el servicio.';
            return false;
        }
        $sql = "INSERT INTO servicios ( socio_id, ejemplar_id, tipo_servicio_id, medico_id, estado, fechaSolicitud, fechaRecepcionDocs, fechaPago, fechaAsignacionMedico, descripcion, referencia_pago, id_usuario_registro, id_usuario_ultima_mod ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) { 
            error_log("Prepare failed (Servicio store): " . $conn->error);
            $_SESSION['error_details'] = 'Error interno al preparar la inserción del servicio.'; 
            $conn->close(); 
            return false;
        }
        $stmt->bind_param("iiiisssssssii", $data['socio_id'], $data['ejemplar_id'], $data['tipo_servicio_id'], $data['medico_id'], $data['estado'], $data['fechaSolicitud'], $data['fechaRecepcionDocs'], $data['fechaPago'], $data['fechaAsignacionMedico'], $data['descripcion'], $data['referencia_pago'], $data['id_usuario_registro'], $data['id_usuario_ultima_mod']);
        $newId = false;
        try {
            if ($stmt->execute()) { $newId = $conn->insert_id; }
        } catch (mysqli_sql_exception $e) {
            error_log("Exception (Servicio store): " . $e->getMessage());
            $_SESSION['error_details'] = 'Error de base de datos al guardar el servicio.';
        }
        $stmt->close();
        $conn->close();
        return $newId;
    }

    public static function countAll($filters = []) {
        $conn = dbConnect();
        if (!$conn) return 0;

        $sql = "SELECT COUNT(s.id_servicio) as total FROM servicios s";
        $whereClauses = []; 
        $params = [];
        $types = "";

        if (!empty($filters['estado'])) { $whereClauses[] = "s.estado = ?"; $params[] = $filters['estado']; $types .= "s"; } 
        if (!empty($filters['socio_id'])) { $whereClauses[] = "s.socio_id = ?"; $params[] = $filters['socio_id']; $types .= "i"; } 
        if (!empty($filters['tipo_servicio_id'])) { $whereClauses[] = "s.tipo_servicio_id = ?"; $params[] = $filters['tipo_servicio_id']; $types .= "i"; }

        if (!empty($whereClauses)) { $sql .= " WHERE " . implode(" AND ", $whereClauses); }

        $stmt = $conn->prepare($sql);
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

    // AÑADIR ESTE NUEVO MÉTODO
    public static function countActive() {
        $conn = dbConnect();
        if (!$conn) return 0;

        $query = "SELECT COUNT(id_servicio) as total FROM servicios WHERE estado NOT IN ('Completado', 'Rechazado', 'Cancelado')";
        $result = $conn->query($query);
        $total = 0;
        if ($result) {
            $total = $result->fetch_assoc()['total'];
            $result->free();
        }
        $conn->close();
        return $total;
    }

    public static function getAll($filters = [], $limit = 15, $offset = 0) {
        $conn = dbConnect();
        $servicios = []; 
        if (!$conn) return $servicios;

        $sql = "SELECT s.*, ts.nombre as tipo_servicio_nombre, ts.codigo_servicio, so.nombre as socio_nombre, so.apellido_paterno as socio_apPaterno, so.codigoGanadero as socio_codigo_ganadero, e.nombre as ejemplar_nombre, umod.username as modificador_username 
                FROM servicios s 
                LEFT JOIN tipos_servicios ts ON s.tipo_servicio_id = ts.id_tipo_servicio 
                LEFT JOIN socios so ON s.socio_id = so.id_socio 
                LEFT JOIN ejemplares e ON s.ejemplar_id = e.id_ejemplar 
                LEFT JOIN usuarios umod ON s.id_usuario_ultima_mod = umod.id_usuario";
        
        $whereClauses = []; 
        $params = [];
        $types = "";

        if (!empty($filters['estado'])) { $whereClauses[] = "s.estado = ?"; $params[] = $filters['estado']; $types .= "s"; } 
        if (!empty($filters['socio_id'])) { $whereClauses[] = "s.socio_id = ?"; $params[] = $filters['socio_id']; $types .= "i"; } 
        if (!empty($filters['tipo_servicio_id'])) { $whereClauses[] = "s.tipo_servicio_id = ?"; $params[] = $filters['tipo_servicio_id']; $types .= "i"; }

        if (!empty($whereClauses)) { $sql .= " WHERE " . implode(" AND ", $whereClauses); } 
        
        if ($limit != -1) {
            $sql .= " ORDER BY s.id_servicio ASC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            $types .= 'ii';
        } else {
            $sql .= " ORDER BY s.id_servicio ASC";
        }
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) return $servicios;
        
        if (!empty($params)) { 
            $stmt->bind_param($types, ...$params);
        }
        
        if($stmt->execute()) { 
            $result = $stmt->get_result();
            while($row = $result->fetch_assoc()){ 
                $servicios[] = $row;
            } 
            $result->free();
        }
        
        $stmt->close(); 
        $conn->close();
        return $servicios;
    }

    public static function getById($id) {
         $conn = dbConnect();
         if (!$conn) return null;
         $sql = "SELECT s.*, ts.nombre as tipo_servicio_nombre, ts.codigo_servicio, ts.requiere_medico, so.nombre as socio_nombre, so.apellido_paterno as socio_apPaterno, so.apellido_materno as socio_apMaterno, so.codigoGanadero as socio_codigo_ganadero, e.nombre as ejemplar_nombre, e.codigo_ejemplar as ejemplar_codigo, m.nombre as medico_nombre, m.apellido_paterno as medico_apPaterno, ureg.username as registrador_username, umod.username as modificador_username 
                 FROM servicios s 
                 LEFT JOIN tipos_servicios ts ON s.tipo_servicio_id = ts.id_tipo_servicio 
                 LEFT JOIN socios so ON s.socio_id = so.id_socio 
                 LEFT JOIN ejemplares e ON s.ejemplar_id = e.id_ejemplar 
                 LEFT JOIN medicos m ON s.medico_id = m.id_medico 
                 LEFT JOIN usuarios ureg ON s.id_usuario_registro = ureg.id_usuario 
                 LEFT JOIN usuarios umod ON s.id_usuario_ultima_mod = umod.id_usuario 
                 WHERE s.id_servicio = ? LIMIT 1";
         $stmt = $conn->prepare($sql);
         if (!$stmt) { $conn->close(); return null; }
         $stmt->bind_param("i", $id); 
         $servicio = null;
         if($stmt->execute()){ 
             $result = $stmt->get_result(); 
             $servicio = $result->fetch_assoc(); 
             $result->free();
         }
         $stmt->close(); 
         $conn->close(); 
         return $servicio;
    }

    public static function update($id, $data) {
        $conn = dbConnect();
        if (!$conn) return false;
        $sql = "UPDATE servicios SET ejemplar_id = ?, medico_id = ?, estado = ?, fechaSolicitud = ?, fechaRecepcionDocs = ?, fechaPago = ?, fechaAsignacionMedico = ?, fechaVisitaMedico = ?, fechaEnvioLG = ?, fechaRecepcionLG = ?, fechaFinalizacion = ?, descripcion = ?, motivo_rechazo = ?, referencia_pago = ?, id_usuario_ultima_mod = ? WHERE id_servicio = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) { $conn->close(); return false; }
         $stmt->bind_param("iissssssssssssii", $data['ejemplar_id'], $data['medico_id'], $data['estado'], $data['fechaSolicitud'], $data['fechaRecepcionDocs'], $data['fechaPago'], $data['fechaAsignacionMedico'], $data['fechaVisitaMedico'], $data['fechaEnvioLG'], $data['fechaRecepcionLG'], $data['fechaFinalizacion'], $data['descripcion'], $data['motivo_rechazo'], $data['referencia_pago'], $data['id_usuario_ultima_mod'], $id);
        $result = false;
        try {
            $result = $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            error_log("Exception (Servicio update): " . $e->getMessage());
        }
        $stmt->close();
        $conn->close();
        return $result;
    }

    public static function cancel($id, $userId) {
        $conn = dbConnect();
        if (!$conn) return false;
        $sql = "UPDATE servicios SET estado = 'Cancelado', fechaFinalizacion = NOW(), id_usuario_ultima_mod = ? WHERE id_servicio = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) { $conn->close(); return false; } 
        $stmt->bind_param("ii", $userId, $id);
        $result = false;
        try {
            $result = $stmt->execute();
        } catch (mysqli_sql_exception $e) { 
            error_log("Exception (Servicio cancel): " . $e->getMessage());
        }
        $stmt->close(); 
        $conn->close();
        return $result;
    }
}