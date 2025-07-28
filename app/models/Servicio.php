<?php
// app/models/Servicio.php
require_once __DIR__ . '/../../config/config.php';

class Servicio {

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
            if ($stmt->execute()) {
                $newId = $conn->insert_id;
                // Registrar el historial al crear
                self::updateStatus($newId, $data['estado'], null, $data['id_usuario_registro']);
            }
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

        $sql = "SELECT s.*, 
                       ts.nombre as tipo_servicio_nombre, ts.codigo_servicio, ts.flujo_trabajo,
                       so.nombre as socio_nombre, so.apellido_paterno as socio_apPaterno, so.codigoGanadero as socio_codigo_ganadero, 
                       e.nombre as ejemplar_nombre, e.codigo_ejemplar as ejemplar_codigo,
                       m.nombre as medico_nombre, m.apellido_paterno as medico_apPaterno,
                       umod.username as modificador_username 
                FROM servicios s 
                LEFT JOIN tipos_servicios ts ON s.tipo_servicio_id = ts.id_tipo_servicio 
                LEFT JOIN socios so ON s.socio_id = so.id_socio 
                LEFT JOIN ejemplares e ON s.ejemplar_id = e.id_ejemplar 
                LEFT JOIN medicos m ON s.medico_id = m.id_medico
                LEFT JOIN usuarios umod ON s.id_usuario_ultima_mod = umod.id_usuario";
        
        $whereClauses = []; 
        $params = [];
        $types = "";

        if (!empty($filters['estado'])) { $whereClauses[] = "s.estado = ?"; $params[] = $filters['estado']; $types .= "s"; } 
        if (!empty($filters['socio_id'])) { $whereClauses[] = "s.socio_id = ?"; $params[] = $filters['socio_id']; $types .= "i"; } 
        if (!empty($filters['tipo_servicio_id'])) { $whereClauses[] = "s.tipo_servicio_id = ?"; $params[] = $filters['tipo_servicio_id']; $types .= "i"; }

        if (!empty($whereClauses)) { $sql .= " WHERE " . implode(" AND ", $whereClauses); } 
        
        $orderBy = " ORDER BY s.fechaSolicitud DESC, s.id_servicio DESC";

        if ($limit != -1) {
            $sql .= $orderBy . " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            $types .= 'ii';
        } else {
            $sql .= $orderBy;
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
         $sql = "SELECT s.*, ts.nombre as tipo_servicio_nombre, ts.codigo_servicio, ts.requiere_medico, ts.flujo_trabajo, so.nombre as socio_nombre, so.apellido_paterno as socio_apPaterno, so.apellido_materno as socio_apMaterno, so.codigoGanadero as socio_codigo_ganadero, e.nombre as ejemplar_nombre, e.codigo_ejemplar as ejemplar_codigo, m.nombre as medico_nombre, m.apellido_paterno as medico_apPaterno, ureg.username as registrador_username, umod.username as modificador_username 
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
        
        $servicioActual = self::getById($id);
        if (!$servicioActual) {
            $_SESSION['error_details'] = "Servicio no encontrado para actualizar.";
            return false;
        }

        if ($servicioActual['estado'] !== $data['estado']) {
             self::updateStatus($id, $data['estado'], $data['motivo_rechazo'] ?? null, $data['id_usuario_ultima_mod']);
        }

        $sql = "UPDATE servicios SET ejemplar_id = ?, medico_id = ?, fechaSolicitud = ?, fechaRecepcionDocs = ?, fechaPago = ?, fechaAsignacionMedico = ?, fechaVisitaMedico = ?, fechaEnvioLG = ?, fechaRecepcionLG = ?, fechaFinalizacion = ?, descripcion = ?, referencia_pago = ?, id_usuario_ultima_mod = ? WHERE id_servicio = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) { $conn->close(); return false; }
        
        $stmt->bind_param("isssssssssssii", $data['ejemplar_id'], $data['medico_id'], $data['fechaSolicitud'], $data['fechaRecepcionDocs'], $data['fechaPago'], $data['fechaAsignacionMedico'], $data['fechaVisitaMedico'], $data['fechaEnvioLG'], $data['fechaRecepcionLG'], $data['fechaFinalizacion'], $data['descripcion'], $data['referencia_pago'], $data['id_usuario_ultima_mod'], $id);
        
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
        return self::updateStatus($id, 'Cancelado', 'Cancelado por el usuario desde el listado.', $userId);
    }
    
    public static function getDashboardStats() {
        $conn = dbConnect();
        if (!$conn) return [];

        $stats = [
            'completados_mes_actual' => 0,
            'promedio_resolucion_dias' => 0,
            'distribucion_estados' => [],
            'pendientes_docs_pago' => 0,
            'nuevas_solicitudes_semana' => 0
        ];

        $query1 = "SELECT COUNT(id_servicio) as total FROM servicios WHERE estado = 'Completado' AND MONTH(fechaFinalizacion) = MONTH(CURDATE()) AND YEAR(fechaFinalizacion) = YEAR(CURDATE())";
        if ($result = $conn->query($query1)) {
            $stats['completados_mes_actual'] = $result->fetch_assoc()['total'] ?? 0;
            $result->free();
        }

        $query2 = "SELECT AVG(DATEDIFF(fechaFinalizacion, fechaSolicitud)) as promedio FROM servicios WHERE estado = 'Completado' AND fechaFinalizacion IS NOT NULL AND fechaSolicitud IS NOT NULL";
        if ($result = $conn->query($query2)) {
            $stats['promedio_resolucion_dias'] = $result->fetch_assoc()['promedio'] ?? 0;
        }

        $query3 = "SELECT estado, COUNT(id_servicio) as total FROM servicios WHERE estado NOT IN ('Completado', 'Rechazado', 'Cancelado') GROUP BY estado ORDER BY estado";
        if ($result = $conn->query($query3)) {
            while ($row = $result->fetch_assoc()) {
                $stats['distribucion_estados'][] = $row;
            }
            $result->free();
        }
        
        $query4 = "SELECT COUNT(id_servicio) as total FROM servicios WHERE estado = 'Pendiente Docs/Pago'";
        if ($result = $conn->query($query4)) {
            $stats['pendientes_docs_pago'] = $result->fetch_assoc()['total'] ?? 0;
            $result->free();
        }

        $query5 = "SELECT COUNT(id_servicio) as total FROM servicios WHERE fechaSolicitud >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        if ($result = $conn->query($query5)) {
            $stats['nuevas_solicitudes_semana'] = $result->fetch_assoc()['total'] ?? 0;
            $result->free();
        }

        $conn->close();
        return $stats;
    }

    public static function getRecientes($limit = 5) {
        $conn = dbConnect();
        $servicios = [];
        if (!$conn) return $servicios;

        $sql = "SELECT s.id_servicio, s.estado, s.fecha_modificacion, 
                       ts.nombre as tipo_servicio_nombre,
                       so.nombre as socio_nombre, so.apellido_paterno as socio_apPaterno
                FROM servicios s
                LEFT JOIN tipos_servicios ts ON s.tipo_servicio_id = ts.id_tipo_servicio
                LEFT JOIN socios so ON s.socio_id = so.id_socio
                ORDER BY s.fecha_modificacion DESC
                LIMIT ?";
        
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $limit);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    $servicios[] = $row;
                }
                $result->free();
            }
            $stmt->close();
        }
        
        $conn->close();
        return $servicios;
    }

    public static function getMonthlyStats() {
        $conn = dbConnect();
        if (!$conn) return [];

        $meses = [];
        for ($i = 11; $i >= 0; $i--) {
            $mes = date("Y-m", strtotime("-$i months"));
            $meses[$mes] = ['mes' => date("M Y", strtotime("-$i months")), 'creados' => 0, 'completados' => 0];
        }

        $queryCreados = "SELECT DATE_FORMAT(fechaSolicitud, '%Y-%m') as mes, COUNT(id_servicio) as total 
                         FROM servicios 
                         WHERE fechaSolicitud >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) 
                         GROUP BY mes";
        if ($result = $conn->query($queryCreados)) {
            while($row = $result->fetch_assoc()) {
                if (isset($meses[$row['mes']])) {
                    $meses[$row['mes']]['creados'] = (int)$row['total'];
                }
            }
            $result->free();
        }

        $queryCompletados = "SELECT DATE_FORMAT(fechaFinalizacion, '%Y-%m') as mes, COUNT(id_servicio) as total 
                             FROM servicios 
                             WHERE estado = 'Completado' AND fechaFinalizacion >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) 
                             GROUP BY mes";
        if ($result = $conn->query($queryCompletados)) {
            while($row = $result->fetch_assoc()) {
                if (isset($meses[$row['mes']])) {
                    $meses[$row['mes']]['completados'] = (int)$row['total'];
                }
            }
            $result->free();
        }

        $conn->close();
        return array_values($meses);
    }
    
    public static function getAtencionRequerida($days = 15, $limit = 5) {
        $conn = dbConnect();
        $servicios = [];
        if (!$conn) return $servicios;

        $sql = "SELECT s.id_servicio, s.estado,
                       DATEDIFF(CURDATE(), s.fecha_modificacion) as dias_sin_actualizar,
                       so.nombre as socio_nombre, so.apellido_paterno as socio_apPaterno
                FROM servicios s
                LEFT JOIN socios so ON s.socio_id = so.id_socio
                WHERE s.estado NOT IN ('Completado', 'Rechazado', 'Cancelado')
                AND DATEDIFF(CURDATE(), s.fecha_modificacion) > ?
                ORDER BY dias_sin_actualizar DESC
                LIMIT ?";
        
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ii", $days, $limit);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    $servicios[] = $row;
                }
                $result->free();
            }
            $stmt->close();
        }
        
        $conn->close();
        return $servicios;
    }
    
    public static function updateStatus($servicioId, $nuevoEstado, $motivoRechazo, $userId) {
        $conn = dbConnect();
        if (!$conn) {
            $_SESSION['error_details'] = 'Error de conexión.';
            return false;
        }

        $conn->begin_transaction();

        try {
            $stmt_get = $conn->prepare("SELECT estado FROM servicios WHERE id_servicio = ?");
            if (!$stmt_get) throw new Exception("Error al preparar la consulta para obtener el estado actual.");
            $stmt_get->bind_param("i", $servicioId);
            $stmt_get->execute();
            $result_get = $stmt_get->get_result();
            if ($result_get->num_rows === 0) throw new Exception("Servicio no encontrado.");
            $estadoAnterior = $result_get->fetch_assoc()['estado'];
            $stmt_get->close();

            if ($estadoAnterior === $nuevoEstado) {
                $conn->commit();
                return true;
            }

            $sql_parts = ["estado = ?", "id_usuario_ultima_mod = ?"];
            $params = [$nuevoEstado, $userId];
            $types = "si";

            if (in_array($nuevoEstado, ['Completado', 'Rechazado', 'Cancelado'])) {
                $sql_parts[] = "fechaFinalizacion = NOW()";
            }
            
            if ($nuevoEstado === 'Rechazado') {
                $sql_parts[] = "motivo_rechazo = ?";
                $params[] = $motivoRechazo;
                $types .= "s";
            } else {
                $sql_parts[] = "motivo_rechazo = NULL";
            }

            $params[] = $servicioId;
            $types .= "i";

            $sql = "UPDATE servicios SET " . implode(', ', $sql_parts) . " WHERE id_servicio = ?";
            
            $stmt_update = $conn->prepare($sql);
            if (!$stmt_update) throw new Exception("Error al preparar la actualización del servicio.");

            $stmt_update->bind_param($types, ...$params);
            if (!$stmt_update->execute()) {
                throw new Exception("Error al ejecutar la actualización del servicio: " . $stmt_update->error);
            }
            $stmt_update->close();

            $comentarios = ($nuevoEstado === 'Rechazado') ? "Motivo: " . $motivoRechazo : null;
            $stmt_historial = $conn->prepare("INSERT INTO servicios_historial (servicio_id, usuario_id, estado_anterior, estado_nuevo, comentarios) VALUES (?, ?, ?, ?, ?)");
            if (!$stmt_historial) throw new Exception("Error al preparar el registro del historial.");
            
            $stmt_historial->bind_param("iisss", $servicioId, $userId, $estadoAnterior, $nuevoEstado, $comentarios);
            if (!$stmt_historial->execute()) {
                throw new Exception("Error al guardar el cambio en el historial: " . $stmt_historial->error);
            }
            $stmt_historial->close();

            $conn->commit();
            $conn->close();
            return true;

        } catch (Exception $e) {
            $conn->rollback();
            error_log("Error en la transacción de updateStatus: " . $e->getMessage());
            $_SESSION['error_details'] = "Error interno al actualizar el estado: " . $e->getMessage();
            $conn->close();
            return false;
        }
    }

    public static function getSiguientesEstadosPosibles($estadoActual, $flujoTrabajo) {
        $estadosAdministrativos = [
            'Pendiente Docs/Pago',
            'Recibido Completo',
            'Enviado a LG',
            'Pendiente Respuesta LG',
            'Completado',
            'Rechazado',
            'Cancelado'
        ];
        
        $estadosZootecnicos = [
            'Pendiente Docs/Pago',
            'Recibido Completo',
            'Pendiente Visita Medico',
            'Pendiente Resultado Lab',
            'Enviado a LG',
            'Pendiente Respuesta LG',
            'Completado',
            'Rechazado',
            'Cancelado'
        ];

        if ($flujoTrabajo === 'ZOOTECNICO') {
            return $estadosZootecnicos;
        }
        
        // Por defecto, se devuelve el flujo administrativo
        return $estadosAdministrativos;
    }
}