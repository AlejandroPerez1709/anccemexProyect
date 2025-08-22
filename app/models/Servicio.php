<?php
// app/models/Servicio.php
require_once __DIR__ . '/../../config/config.php';
class Servicio {

    // ... (MANTENER IGUALES LOS MÉTODOS store y countAll) ...
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

        if (!empty($filters['estado'])) { $whereClauses[] = "s.estado = ?"; $params[] = $filters['estado']; $types .= "s";
        } 
        if (!empty($filters['socio_id'])) { $whereClauses[] = "s.socio_id = ?"; $params[] = $filters['socio_id'];
        $types .= "i"; } 
        if (!empty($filters['tipo_servicio_id'])) { $whereClauses[] = "s.tipo_servicio_id = ?";
        $params[] = $filters['tipo_servicio_id']; $types .= "i"; }
        
        if (!empty($filters['estado_not_in']) && is_array($filters['estado_not_in'])) {
            $placeholders = implode(',', array_fill(0, count($filters['estado_not_in']), '?'));
            $whereClauses[] = "s.estado NOT IN ($placeholders)";
            $params = array_merge($params, $filters['estado_not_in']);
            $types .= str_repeat('s', count($filters['estado_not_in']));
        }

        if (!empty($whereClauses)) { $sql .= " WHERE " .
        implode(" AND ", $whereClauses); }

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

    public static function countActive($filtros = []) {
        $conn = dbConnect();
        if (!$conn) return 0;

        $sql = "SELECT COUNT(id_servicio) as total FROM servicios WHERE estado NOT IN ('Completado', 'Rechazado', 'Cancelado')";
        $params = [];
        $types = '';

        if (!empty($filtros['fecha_inicio'])) {
            $sql .= " AND fechaSolicitud >= ?";
            $params[] = $filtros['fecha_inicio'];
            $types .= 's';
        }
        if (!empty($filtros['fecha_fin'])) {
            $sql .= " AND fechaSolicitud <= ?";
            $params[] = $filtros['fecha_fin'];
            $types .= 's';
        }
        
        $total = 0;
        $stmt = $conn->prepare($sql);
        if($stmt){
            if(!empty($params)){
                $stmt->bind_param($types, ...$params);
            }
            if($stmt->execute()){
                $result = $stmt->get_result();
                $total = $result->fetch_assoc()['total'];
                $result->free();
            }
            $stmt->close();
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

        if (!empty($filters['estado'])) { $whereClauses[] = "s.estado = ?"; $params[] = $filters['estado'];
        $types .= "s"; } 
        if (!empty($filters['socio_id'])) { $whereClauses[] = "s.socio_id = ?";
        $params[] = $filters['socio_id']; $types .= "i"; } 
        if (!empty($filters['tipo_servicio_id'])) { $whereClauses[] = "s.tipo_servicio_id = ?";
        $params[] = $filters['tipo_servicio_id']; $types .= "i"; }
        
        if (!empty($filters['estado_not_in']) && is_array($filters['estado_not_in'])) {
            $placeholders = implode(',', array_fill(0, count($filters['estado_not_in']), '?'));
            $whereClauses[] = "s.estado NOT IN ($placeholders)";
            $params = array_merge($params, $filters['estado_not_in']);
            $types .= str_repeat('s', count($filters['estado_not_in']));
        }

        if (!empty($whereClauses)) { $sql .= " WHERE " .
        implode(" AND ", $whereClauses); } 
        
        $orderBy = " ORDER BY s.fechaSolicitud DESC, s.id_servicio DESC";
        if ($limit != -1) {
            $sql .= $orderBy .
            " LIMIT ? OFFSET ?";
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
                $row['health_status'] = self::getServicioHealthStatus($row['estado'], $row['fecha_modificacion']);
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
                 WHERE s.id_servicio = ?
                 LIMIT 1";
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

        $sql = "UPDATE servicios SET ejemplar_id = ?, medico_id = ?, fechaSolicitud = ?, fechaRecepcionDocs = ?, fechaPago = ?, fechaAsignacionMedico = ?, fechaVisitaMedico = ?, fechaEnvioLG = ?, fechaRecepcionLG = ?, fechaFinalizacion = ?, descripcion = ?, referencia_pago = ?, id_usuario_ultima_mod = ?
        WHERE id_servicio = ?";
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
    
    public static function getDashboardStats($filtros = []) {
        $conn = dbConnect();
        if (!$conn) return [];

        $stats = [
            'completados_periodo' => 0,
            'promedio_resolucion_dias' => 0,
            'distribucion_estados' => [],
            'pendientes_docs_pago' => 0,
            'nuevas_solicitudes_periodo' => 0
        ];
        $where_clause = "";
        $params = [];
        $types = '';
        if (!empty($filtros['fecha_inicio']) && !empty($filtros['fecha_fin'])) {
            $where_clause = " WHERE fechaSolicitud BETWEEN ? AND ?";
            $params = [$filtros['fecha_inicio'], $filtros['fecha_fin']];
            $types = 'ss';
        }

        // 1. Completados en el período
        $sql1 = "SELECT COUNT(id_servicio) as total FROM servicios WHERE estado = 'Completado'" .
        (empty($params) ? " AND MONTH(fechaFinalizacion) = MONTH(CURDATE()) AND YEAR(fechaFinalizacion) = YEAR(CURDATE())" : " AND fechaFinalizacion BETWEEN ? AND ?");
        $stmt1 = $conn->prepare($sql1);
        if($stmt1){
            if(!empty($params)) $stmt1->bind_param($types, ...$params);
            $stmt1->execute();
            $result = $stmt1->get_result();
            $stats['completados_periodo'] = $result->fetch_assoc()['total'] ?? 0;
            $stmt1->close();
        }

        // 2. Promedio resolución
        $sql2 = "SELECT AVG(DATEDIFF(fechaFinalizacion, fechaSolicitud)) as promedio FROM servicios WHERE estado = 'Completado' AND fechaFinalizacion IS NOT NULL AND fechaSolicitud IS NOT NULL" .
        (empty($params) ? "" : " AND fechaSolicitud BETWEEN ? AND ?");
        $stmt2 = $conn->prepare($sql2);
        if($stmt2){
            if(!empty($params)) $stmt2->bind_param($types, ...$params);
            $stmt2->execute();
            $result = $stmt2->get_result();
            $stats['promedio_resolucion_dias'] = $result->fetch_assoc()['promedio'] ?? 0;
            $stmt2->close();
        }

        // 3. Distribución de estados (de los servicios creados en el período)
        $sql3 = "SELECT estado, COUNT(id_servicio) as total FROM servicios " .
        $where_clause . " GROUP BY estado ORDER BY estado";
        $stmt3 = $conn->prepare($sql3);
        if($stmt3){
            if(!empty($params)) $stmt3->bind_param($types, ...$params);
            $stmt3->execute();
            $result = $stmt3->get_result();
            while ($row = $result->fetch_assoc()) {
                $stats['distribucion_estados'][] = $row;
            }
            $stmt3->close();
        }

        // 4. Pendientes Docs/Pago (de los servicios creados en el período)
        $sql4 = "SELECT COUNT(id_servicio) as total FROM servicios WHERE estado = 'Pendiente Docs/Pago'" .
        (empty($params) ? "" : " AND fechaSolicitud BETWEEN ? AND ?");
        $stmt4 = $conn->prepare($sql4);
        if($stmt4){
            if(!empty($params)) $stmt4->bind_param($types, ...$params);
            $stmt4->execute();
            $result = $stmt4->get_result();
            $stats['pendientes_docs_pago'] = $result->fetch_assoc()['total'] ?? 0;
            $stmt4->close();
        }

        // 5. Nuevas solicitudes en el período
        $sql5 = "SELECT COUNT(id_servicio) as total FROM servicios" .
        $where_clause;
        $stmt5 = $conn->prepare($sql5);
        if($stmt5){
            if(!empty($params)) $stmt5->bind_param($types, ...$params);
            $stmt5->execute();
            $result = $stmt5->get_result();
            $stats['nuevas_solicitudes_periodo'] = $result->fetch_assoc()['total'] ?? 0;
            $stmt5->close();
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
    
    public static function getAtencionRequerida($limit = 5) {
        $conn = dbConnect();
        $servicios_atencion = [];
        if (!$conn) return $servicios_atencion;

        $sql = "SELECT s.id_servicio, s.estado, s.fecha_modificacion,
                       so.nombre as socio_nombre, so.apellido_paterno as socio_apPaterno
                FROM servicios s
                LEFT JOIN socios so ON s.socio_id = so.id_socio
                WHERE s.estado NOT IN ('Completado', 'Rechazado', 'Cancelado')
                ORDER BY s.fecha_modificacion ASC";
        $result = $conn->query($sql);
        if ($result) {
            while ($servicio = $result->fetch_assoc()) {
                if (self::getServicioHealthStatus($servicio['estado'], $servicio['fecha_modificacion']) === 'retrasado') {
                    $servicio['dias_sin_actualizar'] = (new DateTime())->diff(new DateTime($servicio['fecha_modificacion']))->days;
                    $servicios_atencion[] = $servicio;
                }
            }
            $result->free();
        }
        
        $conn->close();
        return array_slice($servicios_atencion, 0, $limit);
    }
    
    public static function updateStatus($servicioId, $nuevoEstado, $motivoRechazo, $userId) {
        $conn = dbConnect();
        if (!$conn) {
            $_SESSION['error_details'] = 'Error de conexión.';
            return false;
        }

        $conn->begin_transaction();
        try {
            $stmt_get = $conn->prepare("SELECT estado, medico_id FROM servicios WHERE id_servicio = ?");
            if (!$stmt_get) throw new Exception("Error al preparar la consulta para obtener el estado actual.");
            $stmt_get->bind_param("i", $servicioId);
            $stmt_get->execute();
            $result_get = $stmt_get->get_result();
            if ($result_get->num_rows === 0) throw new Exception("Servicio no encontrado.");
            $servicioActual = $result_get->fetch_assoc();
            $estadoAnterior = $servicioActual['estado'];
            $stmt_get->close();
            if ($estadoAnterior === $nuevoEstado) {
                if ($estadoAnterior !== null) {
                     $comentarios = "Actualización manual sin cambio de estado.";
                     $stmt_historial = $conn->prepare("INSERT INTO servicios_historial (servicio_id, usuario_id, estado_anterior, estado_nuevo, comentarios) VALUES (?, ?, ?, ?, ?)");
                     if ($stmt_historial) {
                         $stmt_historial->bind_param("iisss", $servicioId, $userId, $estadoAnterior, $nuevoEstado, $comentarios);
                         $stmt_historial->execute();
                         $stmt_historial->close();
                     }
                }
                $conn->commit();
                return true;
            }

            $sql_parts = ["estado = ?", "id_usuario_ultima_mod = ?"];
            $params = [$nuevoEstado, $userId];
            $types = "si";

            switch ($nuevoEstado) {
                case 'Pendiente Visita Medico':
                    if ($servicioActual['medico_id']) {
                        $sql_parts[] = "fechaAsignacionMedico = IF(fechaAsignacionMedico IS NULL, NOW(), fechaAsignacionMedico)";
                    }
                    break;
                case 'Pendiente Resultado Lab':
                    $sql_parts[] = "fechaVisitaMedico = IF(fechaVisitaMedico IS NULL, NOW(), fechaVisitaMedico)";
                    break;
                case 'Enviado a LG':
                    $sql_parts[] = "fechaEnvioLG = IF(fechaEnvioLG IS NULL, NOW(), fechaEnvioLG)";
                    break;
                case 'Completado':
                    $sql_parts[] = "fechaRecepcionLG = IF(fechaRecepcionLG IS NULL, NOW(), fechaRecepcionLG)";
                    $sql_parts[] = "fechaFinalizacion = NOW()";
                    break;
                case 'Rechazado':
                case 'Cancelado':
                    $sql_parts[] = "fechaFinalizacion = NOW()";
                    break;
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

            $comentarios = ($nuevoEstado === 'Rechazado') ?
            "Motivo: " . $motivoRechazo : null;
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

    // --- INICIO DE MODIFICACIÓN: LÓGICA DE ESTADOS SECUENCIAL ---
    public static function getSiguientesEstadosPosibles($estadoActual, $flujoTrabajo) {
        $estadosFinales = ['Completado', 'Rechazado', 'Cancelado'];
        // Si el estado actual ya es final, solo puede ser él mismo.
        if (in_array($estadoActual, $estadosFinales)) {
            return [$estadoActual];
        }

        $flujoCompleto = [];
        $estadosTerminales = ['Rechazado', 'Cancelado']; // Estados a los que se puede llegar desde cualquier punto

        $flujoAdministrativo = [
            'Pendiente Docs/Pago', 
            'Recibido Completo', 
            'Enviado a LG', 
            'Pendiente Respuesta LG', 
            'Completado'
        ];
        $flujoZootecnico = [
            'Pendiente Docs/Pago', 
            'Recibido Completo', 
            'Pendiente Visita Medico', 
            'Pendiente Resultado Lab', 
            'Enviado a LG', 
            'Pendiente Respuesta LG', 
            'Completado'
        ];
        
        if ($flujoTrabajo === 'ZOOTECNICO') {
            $flujoCompleto = $flujoZootecnico;
        } else {
            $flujoCompleto = $flujoAdministrativo;
        }

        $posicionActual = array_search($estadoActual, $flujoCompleto);

        if ($posicionActual === false) {
            // Si por alguna razón el estado actual no está en el flujo, devolver todos los posibles para evitar un bloqueo.
            return array_unique(array_merge($flujoCompleto, $estadosTerminales));
        }

        // Obtener todos los estados desde la posición actual hasta el final
        $siguientesEstados = array_slice($flujoCompleto, $posicionActual);
        
        // Añadir los estados terminales (Rechazado, Cancelado) si aún no están en la lista de siguientes
        foreach ($estadosTerminales as $terminal) {
            if (!in_array($terminal, $siguientesEstados)) {
                $siguientesEstados[] = $terminal;
            }
        }
        
        return $siguientesEstados;
    }
    // --- FIN DE MODIFICACIÓN ---

    private static function getServicioHealthStatus($estado, $fechaModificacion) {
        $estadosFinales = ['Completado', 'Rechazado', 'Cancelado'];
        if (in_array($estado, $estadosFinales)) {
            return 'ok';
        }

        $diasEnEstado = (new DateTime())->diff(new DateTime($fechaModificacion))->days;
        $sla = [
            'Recibido Completo' => ['warn' => 3, 'danger' => 5],
            'Pendiente Visita Medico' => ['warn' => 15, 'danger' => 20],
            'Pendiente Resultado Lab' => ['warn' => 10, 'danger' => 15],
            'Pendiente Respuesta LG' => ['warn' => 20, 'danger' => 30]
        ];
        if (isset($sla[$estado])) {
            if ($diasEnEstado >= $sla[$estado]['danger']) {
                return 'retrasado';
            }
            if ($diasEnEstado >= $sla[$estado]['warn']) {
                return 'advertencia';
            }
        }

        return 'ok';
    }
    
    private static function buildReportWhereClause($filtros, &$params, &$types) {
        $whereClauses = [];
        if (!empty($filtros['fecha_inicio'])) {
            $whereClauses[] = "s.fechaSolicitud >= ?";
            $params[] = $filtros['fecha_inicio'];
            $types .= "s";
        }
        if (!empty($filtros['fecha_fin'])) {
            $whereClauses[] = "s.fechaSolicitud <= ?";
            $params[] = $filtros['fecha_fin'];
            $types .= "s";
        }
        if (!empty($filtros['estado'])) {
            $whereClauses[] = "s.estado = ?";
            $params[] = $filtros['estado'];
            $types .= "s";
        }
        if (!empty($filtros['tipo_servicio_id'])) {
            $whereClauses[] = "s.tipo_servicio_id = ?";
            $params[] = $filtros['tipo_servicio_id'];
            $types .= "i";
        }
        if (!empty($filtros['socio_id'])) {
            $whereClauses[] = "s.socio_id = ?";
            $params[] = $filtros['socio_id'];
            $types .= "i";
        }
        return !empty($whereClauses) ?
        " WHERE " . implode(" AND ", $whereClauses) : "";
    }

    public static function countServiciosParaReporte($filtros) {
        $conn = dbConnect();
        if (!$conn) return 0;

        $params = [];
        $types = "";
        $whereSql = self::buildReportWhereClause($filtros, $params, $types);
        $sql = "SELECT COUNT(s.id_servicio) as total FROM servicios s" . $whereSql;
        
        $total = 0;
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                $total = $result->fetch_assoc()['total'];
                $result->free();
            }
            $stmt->close();
        }
        
        $conn->close();
        return $total;
    }

    public static function getServiciosParaReporte($filtros, $limit = -1, $offset = 0) {
        $conn = dbConnect();
        $servicios = [];
        if (!$conn) return $servicios;

        $sql_select = "SELECT s.*, 
                       ts.nombre as tipo_servicio_nombre,
                       so.nombre as socio_nombre, so.apellido_paterno as socio_apPaterno,
                       e.nombre as ejemplar_nombre
                FROM servicios s
                LEFT JOIN tipos_servicios ts ON s.tipo_servicio_id = ts.id_tipo_servicio
                LEFT JOIN socios so ON s.socio_id = so.id_socio
                LEFT JOIN ejemplares e ON s.ejemplar_id = e.id_ejemplar";
        $params = [];
        $types = "";
        $whereSql = self::buildReportWhereClause($filtros, $params, $types);
        $sql = $sql_select . $whereSql .
        " ORDER BY s.fechaSolicitud DESC";

        if ($limit != -1) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            $types .= 'ii';
        }

        $stmt = $conn->prepare($sql);
        if ($stmt) {
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
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
}







