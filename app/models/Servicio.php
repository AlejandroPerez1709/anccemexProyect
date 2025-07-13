<?php
// app/models/Servicio.php
require_once __DIR__ . '/../../config/config.php';

class Servicio {

    /**
     * Guarda una nueva solicitud de servicio.
     * @param array $data Datos del servicio.
     * @return int|false Retorna el ID del servicio insertado o False en caso de error.
     */
    public static function store($data) {
        $conn = dbConnect(); if (!$conn) return false;
        $sql = "INSERT INTO servicios ( socio_id, ejemplar_id, tipo_servicio_id, medico_id, estado, fechaSolicitud, fechaRecepcionDocs, fechaPago, fechaAsignacionMedico, fechaVisitaMedico, fechaEnvioLG, fechaRecepcionLG, fechaFinalizacion, descripcion, motivo_rechazo, referencia_pago, id_usuario_registro, id_usuario_ultima_mod ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) { error_log("Prepare failed (servicio store): " . $conn->error); $_SESSION['error_details'] = 'Error interno.'; $conn->close(); return false; }

        $socio_id = $data['socio_id']; $ejemplar_id = !empty($data['ejemplar_id']) ? $data['ejemplar_id'] : null; $tipo_servicio_id = $data['tipo_servicio_id']; $medico_id = !empty($data['medico_id']) ? $data['medico_id'] : null; $estado = $data['estado'] ?? 'Pendiente Docs/Pago'; $fechaSolicitud = $data['fechaSolicitud']; $fechaRecepcionDocs = !empty($data['fechaRecepcionDocs']) ? $data['fechaRecepcionDocs'] : null; $fechaPago = !empty($data['fechaPago']) ? $data['fechaPago'] : null; $fechaAsignacionMedico = !empty($data['fechaAsignacionMedico']) ? $data['fechaAsignacionMedico'] : null; $fechaVisitaMedico = !empty($data['fechaVisitaMedico']) ? $data['fechaVisitaMedico'] : null; $fechaEnvioLG = !empty($data['fechaEnvioLG']) ? $data['fechaEnvioLG'] : null; $fechaRecepcionLG = !empty($data['fechaRecepcionLG']) ? $data['fechaRecepcionLG'] : null; $fechaFinalizacion = !empty($data['fechaFinalizacion']) ? $data['fechaFinalizacion'] : null; $descripcion = $data['descripcion'] ?: null; $motivo_rechazo = $data['motivo_rechazo'] ?: null; $referencia_pago = $data['referencia_pago'] ?: null; $id_usuario_registro = $data['id_usuario_registro']; $id_usuario_ultima_mod = $data['id_usuario_ultima_mod'];
        $types = "iiiissssssssssssii"; // 18 tipos
        $stmt->bind_param($types, $socio_id, $ejemplar_id, $tipo_servicio_id, $medico_id, $estado, $fechaSolicitud, $fechaRecepcionDocs, $fechaPago, $fechaAsignacionMedico, $fechaVisitaMedico, $fechaEnvioLG, $fechaRecepcionLG, $fechaFinalizacion, $descripcion, $motivo_rechazo, $referencia_pago, $id_usuario_registro, $id_usuario_ultima_mod);

        $result = false; $newId = false;
        try {
            $result = $stmt->execute();
            if ($result) { $newId = $conn->insert_id; if ($newId == 0) { error_log("Servicio::store insert_id 0"); $result=false; $_SESSION['error_details'] = 'Error ID.'; } }
            else { error_log("Execute failed (servicio store): " . $stmt->error); $_SESSION['error_details'] = 'Error BD: ' . $stmt->error; $result=false; }
        } catch (mysqli_sql_exception $e) { error_log("Exception (servicio store): " . $e->getMessage()); $_SESSION['error_details'] = 'Error DB (' . $e->getCode() . '): ' . $e->getMessage(); $result = false; }
        $success = (bool) $result;
        if ($stmt) $stmt->close();
        $conn->close();
        return ($success && $newId) ? $newId : false; // <<< Retorno explícito final ID o false >>>
    }

    /**
     * Obtiene todos los servicios con información relacionada (VERSIÓN TEMPORAL CON LEFT JOIN).
     * @param array $filters Filtros opcionales.
     * @return array Lista de servicios.
     */
    public static function getAll($filters = []) {
        $conn = dbConnect(); $servicios = []; if (!$conn) return $servicios;
        // ***** Usando LEFT JOIN para socios y tipos *****
        $sql = "SELECT s.*, ts.nombre as tipo_servicio_nombre, ts.codigo_servicio, so.nombre as socio_nombre, so.apellido_paterno as socio_apPaterno, so.apellido_materno as socio_apMaterno, so.codigoGanadero as socio_codigo_ganadero, e.nombre as ejemplar_nombre, e.codigo_ejemplar as ejemplar_codigo, m.nombre as medico_nombre, m.apellido_paterno as medico_apPaterno, ureg.username as registrador_username, umod.username as modificador_username FROM servicios s LEFT JOIN tipos_servicios ts ON s.tipo_servicio_id = ts.id_tipo_servicio LEFT JOIN socios so ON s.socio_id = so.id_socio LEFT JOIN ejemplares e ON s.ejemplar_id = e.id_ejemplar LEFT JOIN medicos m ON s.medico_id = m.id_medico LEFT JOIN usuarios ureg ON s.id_usuario_registro = ureg.id_usuario LEFT JOIN usuarios umod ON s.id_usuario_ultima_mod = umod.id_usuario";
        $whereClauses = []; $params = []; $types = "";
        if (!empty($filters['estado'])) { $whereClauses[] = "s.estado = ?"; $params[] = $filters['estado']; $types .= "s"; } if (!empty($filters['socio_id'])) { $whereClauses[] = "s.socio_id = ?"; $params[] = $filters['socio_id']; $types .= "i"; } if (!empty($filters['tipo_servicio_id'])) { $whereClauses[] = "s.tipo_servicio_id = ?"; $params[] = $filters['tipo_servicio_id']; $types .= "i"; }
        if (!empty($whereClauses)) { $sql .= " WHERE " . implode(" AND ", $whereClauses); } $sql .= " ORDER BY s.fechaSolicitud DESC, s.id_servicio DESC";
        $stmt = $conn->prepare($sql);
        if (!$stmt) { error_log("Prepare failed (servicio getAll): " . $conn->error); $conn->close(); return $servicios; } // Return []
        if (!empty($params)) { $stmt->bind_param($types, ...$params); }
        $executeResult = $stmt->execute();
        if($executeResult) { $result = $stmt->get_result(); if($result) { while($row = $result->fetch_assoc()){ $servicios[] = $row; } $result->free(); } else { error_log("Error get_result (servicio getAll): " . $stmt->error); } }
        else { error_log("Error execute (servicio getAll): " . $stmt->error); }
        if ($stmt) $stmt->close(); $conn->close();
        return $servicios; // <<< Retorno explícito final array >>>
    }


    /**
     * Obtiene un servicio específico por su ID con info relacionada.
     * @param int $id ID del servicio.
     * @return array|null Datos del servicio o null si no se encuentra/error.
     */
    public static function getById($id) {
         $conn = dbConnect(); if (!$conn) { return null; }
         $sql = "SELECT s.*, ts.nombre as tipo_servicio_nombre, ts.codigo_servicio, ts.requiere_medico, so.nombre as socio_nombre, so.apellido_paterno as socio_apPaterno, so.apellido_materno as socio_apMaterno, so.codigoGanadero as socio_codigo_ganadero, e.nombre as ejemplar_nombre, e.codigo_ejemplar as ejemplar_codigo, m.nombre as medico_nombre, m.apellido_paterno as medico_apPaterno, ureg.username as registrador_username, umod.username as modificador_username FROM servicios s LEFT JOIN tipos_servicios ts ON s.tipo_servicio_id = ts.id_tipo_servicio LEFT JOIN socios so ON s.socio_id = so.id_socio LEFT JOIN ejemplares e ON s.ejemplar_id = e.id_ejemplar LEFT JOIN medicos m ON s.medico_id = m.id_medico LEFT JOIN usuarios ureg ON s.id_usuario_registro = ureg.id_usuario LEFT JOIN usuarios umod ON s.id_usuario_ultima_mod = umod.id_usuario WHERE s.id_servicio = ? LIMIT 1";
         $stmt = $conn->prepare($sql);
         if (!$stmt) { error_log("Prepare failed (servicio getById): ".$conn->error); $conn->close(); return null; }
         $stmt->bind_param("i", $id); $executeResult = $stmt->execute(); $servicio = null;
         if($executeResult){ $result = $stmt->get_result(); $servicio = ($result && $result->num_rows === 1) ? $result->fetch_assoc() : null; if($result) $result->free(); }
         else { error_log("Execute failed (servicio getById): ".$stmt->error); }
         if($stmt) $stmt->close(); $conn->close(); return $servicio; // <<< Retorno explícito final array o null >>>
    }

    /**
     * Actualiza los datos de un servicio existente.
     * @param int $id ID del servicio a actualizar.
     * @param array $data Nuevos datos del servicio.
     * @return bool Retorna true si éxito, false si falla.
     */
    public static function update($id, $data) {
        $conn = dbConnect(); if (!$conn) return false; // Return Path 1

        // SQL Query para actualizar - 15 columnas en SET + 1 en WHERE = 16 placeholders
        $sql = "UPDATE servicios SET
                    ejemplar_id = ?,           -- 1 (i)
                    medico_id = ?,             -- 2 (i)
                    estado = ?,               -- 3 (s)
                    fechaSolicitud = ?,      -- 4 (s)
                    fechaRecepcionDocs = ?,  -- 5 (s)
                    fechaPago = ?,             -- 6 (s)
                    fechaAsignacionMedico = ?,-- 7 (s)
                    fechaVisitaMedico = ?,    -- 8 (s)
                    fechaEnvioLG = ?,          -- 9 (s)
                    fechaRecepcionLG = ?,      -- 10 (s)
                    fechaFinalizacion = ?,     -- 11 (s)
                    descripcion = ?,           -- 12 (s)
                    motivo_rechazo = ?,        -- 13 (s)
                    referencia_pago = ?,       -- 14 (s)
                    id_usuario_ultima_mod = ? -- 15 (i)
                WHERE id_servicio = ?";          

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
             error_log("Prepare failed (servicio update): ".$conn->error);
             $_SESSION['error_details'] = 'Error interno.';
             $conn->close();
             return false; // Return Path 2
        }

         // Asignar a variables locales desde $data
         $ejemplar_id = !empty($data['ejemplar_id']) ? $data['ejemplar_id'] : null;
         $medico_id = !empty($data['medico_id']) ? $data['medico_id'] : null;
         $estado = $data['estado'];
         $fechaSolicitud = $data['fechaSolicitud']; // Asegurarse que $data la contenga
         $fechaRecepcionDocs = !empty($data['fechaRecepcionDocs']) ? $data['fechaRecepcionDocs'] : null;
         $fechaPago = !empty($data['fechaPago']) ? $data['fechaPago'] : null;
         $fechaAsignacionMedico = !empty($data['fechaAsignacionMedico']) ? $data['fechaAsignacionMedico'] : null;
         $fechaVisitaMedico = !empty($data['fechaVisitaMedico']) ? $data['fechaVisitaMedico'] : null;
         $fechaEnvioLG = !empty($data['fechaEnvioLG']) ? $data['fechaEnvioLG'] : null;
         $fechaRecepcionLG = !empty($data['fechaRecepcionLG']) ? $data['fechaRecepcionLG'] : null;
         $fechaFinalizacion = !empty($data['fechaFinalizacion']) ? $data['fechaFinalizacion'] : null;
         $descripcion = $data['descripcion'] ?: null;
         $motivo_rechazo = $data['motivo_rechazo'] ?: null;
         $referencia_pago = $data['referencia_pago'] ?: null;
         $id_usuario_ultima_mod = $data['id_usuario_ultima_mod'];
         $servicioId = $id; // El ID del servicio para el WHERE

         // *** CORRECCIÓN DE LA CADENA DE TIPOS ***
         // Deben ser 16 caracteres coincidiendo con los 16 '?'
         // i, i, s, s, s, s, s, s, s, s, s, s, s, s, i, i
         $types = "iissssssssssssii"; // <<< REVISADO: 16 caracteres

         // *** VINCULAR LAS 16 VARIABLES EN EL ORDEN CORRECTO ***
         $stmt->bind_param($types,
            $ejemplar_id,           // 1 (i)
            $medico_id,             // 2 (i)
            $estado,               // 3 (s)
            $fechaSolicitud,      // 4 (s)
            $fechaRecepcionDocs,  // 5 (s)
            $fechaPago,             // 6 (s)
            $fechaAsignacionMedico,// 7 (s)
            $fechaVisitaMedico,    // 8 (s)
            $fechaEnvioLG,          // 9 (s)
            $fechaRecepcionLG,      // 10 (s)
            $fechaFinalizacion,     // 11 (s)
            $descripcion,           // 12 (s)
            $motivo_rechazo,        // 13 (s)
            $referencia_pago,       // 14 (s)
            $id_usuario_ultima_mod, // 15 (i)
            $servicioId            // 16 (i) - Para el WHERE
         );

        $result = false; // Valor por defecto
        try {
            $result = $stmt->execute();
            if (!$result) { error_log("Execute failed (servicio update): " . $stmt->error); $_SESSION['error_details'] = 'Error BD al actualizar.'; }
        } catch (mysqli_sql_exception $e) { error_log("Exception (servicio update): " . $e->getMessage()); $_SESSION['error_details'] = 'Error DB (' . $e->getCode() . '): ' . $e->getMessage(); $result = false; }

        $result = (bool) $result; // Asegurar bool
        if ($stmt) $stmt->close();
        $conn->close();
        return $result; // <<< Retorno explícito final bool >>> // Return Path 3
    }

    /**
     * @return bool
     */
    public static function cancel($id, $userId) {
        $conn = dbConnect(); if (!$conn) return false; // Return Path 1
        $sql = "UPDATE servicios SET estado = 'Cancelado', fechaFinalizacion = NOW(), id_usuario_ultima_mod = ? WHERE id_servicio = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) { error_log("Prepare failed (servicio cancel): ".$conn->error); $_SESSION['error_details'] = 'Error interno.'; $conn->close(); return false; } // Return Path 2
        $stmt->bind_param("ii", $userId, $id);
        $result = false; // Valor por defecto
        try {
            $result = $stmt->execute();
            if (!$result) { error_log("Execute failed (servicio cancel): " . $stmt->error); $_SESSION['error_details'] = 'Error BD al cancelar.'; }
        } catch (mysqli_sql_exception $e) { error_log("Exception (servicio cancel): " . $e->getMessage()); $_SESSION['error_details'] = 'Error DB al cancelar.'; $result = false; }
        $result = (bool) $result; // Asegurar bool
        if ($stmt) $stmt->close(); $conn->close();
        return $result; // <<< Retorno explícito final bool >>> // Return Path 3
    }

} // Fin clase Servicio
?>