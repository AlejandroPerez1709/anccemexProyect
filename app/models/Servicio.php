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
        $conn = dbConnect();
        if (!$conn) {
            $_SESSION['error_details'] = 'Error de conexión a la base de datos al intentar guardar el servicio.';
            return false;
        }
        $sql = "INSERT INTO servicios ( socio_id, ejemplar_id, tipo_servicio_id, medico_id, estado, fechaSolicitud, fechaRecepcionDocs, fechaPago, fechaAsignacionMedico, fechaVisitaMedico, fechaEnvioLG, fechaRecepcionLG, fechaFinalizacion, descripcion, motivo_rechazo, referencia_pago, id_usuario_registro, id_usuario_ultima_mod ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) { 
            error_log("Prepare failed (Servicio store): " . $conn->error); 
            $_SESSION['error_details'] = 'Error interno al preparar la inserción del servicio.'; 
            $conn->close(); 
            return false;
        }

        $socio_id = $data['socio_id']; 
        $ejemplar_id = $data['ejemplar_id'] ?: null;
        $tipo_servicio_id = $data['tipo_servicio_id']; 
        $medico_id = $data['medico_id'] ?: null; 
        $estado = $data['estado'] ?? 'Recibido Completo'; 
        $fechaSolicitud = $data['fechaSolicitud'];
        $fechaRecepcionDocs = $data['fechaRecepcionDocs'] ?: null; 
        $fechaPago = $data['fechaPago'] ?: null; 
        $fechaAsignacionMedico = $data['fechaAsignacionMedico'] ?: null; 
        $fechaVisitaMedico = $data['fechaVisitaMedico'] ?: null; 
        $fechaEnvioLG = $data['fechaEnvioLG'] ?: null;
        $fechaRecepcionLG = $data['fechaRecepcionLG'] ?: null; 
        $fechaFinalizacion = $data['fechaFinalizacion'] ?: null; 
        $descripcion = $data['descripcion'] ?: null;
        $motivo_rechazo = $data['motivo_rechazo'] ?: null; 
        $referencia_pago = $data['referencia_pago'] ?: null; 
        $id_usuario_registro = $data['id_usuario_registro']; 
        $id_usuario_ultima_mod = $data['id_usuario_ultima_mod'];

        $types = "iiiissssssssssssii"; // 18 tipos
        $stmt->bind_param($types, 
            $socio_id, $ejemplar_id, $tipo_servicio_id, $medico_id, $estado, $fechaSolicitud, 
            $fechaRecepcionDocs, $fechaPago, $fechaAsignacionMedico, $fechaVisitaMedico, $fechaEnvioLG, 
            $fechaRecepcionLG, $fechaFinalizacion, $descripcion, $motivo_rechazo, $referencia_pago, 
            $id_usuario_registro, $id_usuario_ultima_mod
        );
        $result = false; 
        $newId = false;
        try {
            $result = $stmt->execute();
            if ($result) { 
                $newId = $conn->insert_id; 
                if ($newId == 0) { 
                    error_log("Servicio::store insert_id 0 para servicio registrado."); 
                    $result=false; 
                    $_SESSION['error_details'] = 'Error al obtener el ID del servicio insertado.';
                } 
            } else { 
                error_log("Execute failed (Servicio store): " . $stmt->error);
                if ($conn->errno == 1062) { // Error de duplicado si alguna combinación de campos fuera UNIQUE
                     $_SESSION['error_details'] = 'Ya existe un servicio con características similares.';
                 } else {
                     $_SESSION['error_details'] = 'Error de base de datos al guardar el servicio: ' . $stmt->error;
                 }
            }
        } catch (mysqli_sql_exception $e) { 
            error_log("Exception (Servicio store): " . $e->getMessage());
            $_SESSION['error_details'] = 'Error de base de datos al guardar el servicio (' . $e->getCode() . '): ' . $e->getMessage(); 
            $result = false;
        }
        $success = (bool) $result;
        if ($stmt) $stmt->close();
        $conn->close();
        return ($success && $newId) ? $newId : false;
    }

    /**
     * Obtiene todos los servicios con información relacionada.
     * @param array $filters Filtros opcionales (estado, socio_id, tipo_servicio_id).
     * @return array Lista de servicios.
     */
    public static function getAll($filters = []) {
        $conn = dbConnect();
        $servicios = []; 
        if (!$conn) {
            $_SESSION['error_details'] = 'Error de conexión a la base de datos al obtener servicios.';
            return $servicios;
        }

        $sql = "SELECT s.*, 
                       ts.nombre as tipo_servicio_nombre, ts.codigo_servicio, ts.requiere_medico,
                       so.nombre as socio_nombre, so.apellido_paterno as socio_apPaterno, so.apellido_materno as socio_apMaterno, so.codigoGanadero as socio_codigo_ganadero, 
                       e.nombre as ejemplar_nombre, e.codigo_ejemplar as ejemplar_codigo, 
                       m.nombre as medico_nombre, m.apellido_paterno as medico_apPaterno, 
                       ureg.username as registrador_username, umod.username as modificador_username 
                FROM servicios s 
                LEFT JOIN tipos_servicios ts ON s.tipo_servicio_id = ts.id_tipo_servicio 
                LEFT JOIN socios so ON s.socio_id = so.id_socio 
                LEFT JOIN ejemplares e ON s.ejemplar_id = e.id_ejemplar 
                LEFT JOIN medicos m ON s.medico_id = m.id_medico 
                LEFT JOIN usuarios ureg ON s.id_usuario_registro = ureg.id_usuario 
                LEFT JOIN usuarios umod ON s.id_usuario_ultima_mod = umod.id_usuario";
        
        $whereClauses = []; 
        $params = [];
        $types = "";

        if (!empty($filters['estado'])) { $whereClauses[] = "s.estado = ?"; $params[] = $filters['estado']; $types .= "s"; } 
        if (!empty($filters['socio_id'])) { $whereClauses[] = "s.socio_id = ?"; $params[] = $filters['socio_id']; $types .= "i"; } 
        if (!empty($filters['tipo_servicio_id'])) { $whereClauses[] = "s.tipo_servicio_id = ?"; $params[] = $filters['tipo_servicio_id']; $types .= "i"; } // Corregido el nombre del filtro de 'tipo_servicio_id' a 'tipo_id' como lo usas en el controlador

        if (!empty($whereClauses)) { $sql .= " WHERE " . implode(" AND ", $whereClauses); } 
        
        // CORREGIDO: Ordenar por fechaSolicitud DESC (más recientes primero) y luego por id_servicio DESC
        $sql .= " ORDER BY s.fechaSolicitud DESC, s.id_servicio DESC"; 
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) { 
            error_log("Prepare failed (Servicio getAll): " . $conn->error); 
            $_SESSION['error_details'] = 'Error interno al preparar la obtención de servicios: ' . $conn->error; 
            $conn->close(); 
            return $servicios;
        } 
        
        if (!empty($params)) { 
            $stmt->bind_param($types, ...$params);
        }
        
        $executeResult = $stmt->execute();
        if($executeResult) { 
            $result = $stmt->get_result(); 
            if($result) { 
                while($row = $result->fetch_assoc()){ 
                    $servicios[] = $row; 
                } 
                $result->free(); 
            } else { 
                error_log("Error get_result (Servicio getAll): " . $stmt->error);
                $_SESSION['error_details'] = 'Error al obtener resultados de servicios: ' . $stmt->error;
            } 
        } else { 
            error_log("Error execute (Servicio getAll): " . $stmt->error);
            $_SESSION['error_details'] = 'Error al ejecutar la consulta de servicios: ' . $stmt->error;
        }
        
        if ($stmt) $stmt->close(); 
        $conn->close();
        return $servicios;
    }

    /**
     * Obtiene un servicio específico por su ID con info relacionada.
     * @param int $id ID del servicio.
     * @return array|null Datos del servicio o null si no se encuentra/error.
     */
    public static function getById($id) {
         $conn = dbConnect();
         if (!$conn) {
            $_SESSION['error_details'] = 'Error de conexión a la base de datos al obtener servicio por ID.';
            return null;
        }
         $sql = "SELECT s.*, 
                        ts.nombre as tipo_servicio_nombre, ts.codigo_servicio, ts.requiere_medico, 
                        so.nombre as socio_nombre, so.apellido_paterno as socio_apPaterno, so.apellido_materno as socio_apMaterno, so.codigoGanadero as socio_codigo_ganadero, 
                        e.nombre as ejemplar_nombre, e.codigo_ejemplar as ejemplar_codigo, 
                        m.nombre as medico_nombre, m.apellido_paterno as medico_apPaterno, 
                        ureg.username as registrador_username, umod.username as modificador_username 
                 FROM servicios s 
                 LEFT JOIN tipos_servicios ts ON s.tipo_servicio_id = ts.id_tipo_servicio 
                 LEFT JOIN socios so ON s.socio_id = so.id_socio 
                 LEFT JOIN ejemplares e ON s.ejemplar_id = e.id_ejemplar 
                 LEFT JOIN medicos m ON s.medico_id = m.id_medico 
                 LEFT JOIN usuarios ureg ON s.id_usuario_registro = ureg.id_usuario 
                 LEFT JOIN usuarios umod ON s.id_usuario_ultima_mod = umod.id_usuario 
                 WHERE s.id_servicio = ? LIMIT 1";
         $stmt = $conn->prepare($sql);
         if (!$stmt) { 
             error_log("Prepare failed (Servicio getById): ".$conn->error); 
             $_SESSION['error_details'] = 'Error interno al preparar la obtención del servicio.';
             $conn->close(); 
             return null;
         }
         $stmt->bind_param("i", $id); 
         $executeResult = $stmt->execute(); 
         $servicio = null;
         if($executeResult){ 
             $result = $stmt->get_result(); 
             $servicio = ($result && $result->num_rows === 1) ? $result->fetch_assoc() : null; 
             if($result) $result->free();
         } else { 
             error_log("Execute failed (Servicio getById): ".$stmt->error);
             $_SESSION['error_details'] = 'Error de base de datos al obtener el servicio: ' . $stmt->error;
         }
         if($stmt) $stmt->close(); 
         $conn->close(); 
         return $servicio;
    }

    /**
     * Actualiza los datos de un servicio existente.
     * @param int $id ID del servicio a actualizar.
     * @param array $data Nuevos datos del servicio.
     * @return bool Retorna true si éxito, false si falla.
     */
    public static function update($id, $data) {
        $conn = dbConnect();
        if (!$conn) {
            $_SESSION['error_details'] = 'Error de conexión a la base de datos al intentar actualizar el servicio.';
            return false;
        }

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
             error_log("Prepare failed (Servicio update): ".$conn->error);
             $_SESSION['error_details'] = 'Error interno al preparar la actualización del servicio: ' . $conn->error;
             $conn->close();
             return false;
        }

         // Asignar a variables locales desde $data
         $ejemplar_id = $data['ejemplar_id'] ?: null;
         $medico_id = $data['medico_id'] ?: null;
         $estado = $data['estado'];
         $fechaSolicitud = $data['fechaSolicitud'];
         $fechaRecepcionDocs = $data['fechaRecepcionDocs'] ?: null;
         $fechaPago = $data['fechaPago'] ?: null;
         $fechaAsignacionMedico = $data['fechaAsignacionMedico'] ?: null;
         $fechaVisitaMedico = $data['fechaVisitaMedico'] ?: null;
         $fechaEnvioLG = $data['fechaEnvioLG'] ?: null;
         $fechaRecepcionLG = $data['fechaRecepcionLG'] ?: null;
         $fechaFinalizacion = $data['fechaFinalizacion'] ?: null;
         $descripcion = $data['descripcion'] ?: null;
         $motivo_rechazo = $data['motivo_rechazo']; // No se pone ?: null porque puede ser cadena vacía si no hay rechazo
         $referencia_pago = $data['referencia_pago'] ?: null;
         $id_usuario_ultima_mod = $data['id_usuario_ultima_mod'];
         $servicioId = $id; // El ID del servicio para el WHERE

         // Cadena de tipos: 16 caracteres coincidiendo con los 16 '?'
         // i, i, s, s, s, s, s, s, s, s, s, s, s, s, i, i
         $types = "iissssssssssssii"; 

         $stmt->bind_param($types,
            $ejemplar_id,           // 1 (i)
            $medico_id,             // 2 (i)
            $estado,                // 3 (s)
            $fechaSolicitud,        // 4 (s)
            $fechaRecepcionDocs,    // 5 (s)
            $fechaPago,             // 6 (s)
            $fechaAsignacionMedico, // 7 (s)
            $fechaVisitaMedico,     // 8 (s)
            $fechaEnvioLG,          // 9 (s)
            $fechaRecepcionLG,      // 10 (s)
            $fechaFinalizacion,     // 11 (s)
            $descripcion,           // 12 (s)
            $motivo_rechazo,        // 13 (s)
            $referencia_pago,       // 14 (s)
            $id_usuario_ultima_mod, // 15 (i)
            $servicioId             // 16 (i) - Para el WHERE
         );
        $result = false; // Valor por defecto
        try {
            $result = $stmt->execute();
            if (!$result) { 
                error_log("Execute failed (Servicio update): " . $stmt->error); 
                if ($conn->errno == 1062) { // Error de duplicado si aplica
                    $_SESSION['error_details'] = 'Ya existe otro servicio con características similares.';
                } else {
                    $_SESSION['error_details'] = 'Error de base de datos al actualizar el servicio: ' . $stmt->error;
                }
            }
        } catch (mysqli_sql_exception $e) { 
            error_log("Exception (Servicio update): " . $e->getMessage());
            $_SESSION['error_details'] = 'Error de base de datos al actualizar el servicio (' . $e->getCode() . '): ' . $e->getMessage(); 
            $result = false;
        }

        $result = (bool) $result; // Asegurar bool
        if ($stmt) $stmt->close();
        $conn->close();
        return $result;
    }

    /**
     * Cambia el estado de un servicio a 'Cancelado'.
     * @param int $id ID del servicio a cancelar.
     * @param int $userId ID del usuario que realiza la cancelación.
     * @return bool True si se canceló correctamente, False si falla.
     */
    public static function cancel($id, $userId) {
        $conn = dbConnect();
        if (!$conn) {
            $_SESSION['error_details'] = 'Error de conexión a la base de datos al intentar cancelar el servicio.';
            return false;
        }
        $sql = "UPDATE servicios SET estado = 'Cancelado', fechaFinalizacion = NOW(), id_usuario_ultima_mod = ? WHERE id_servicio = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) { 
            error_log("Prepare failed (Servicio cancel): ".$conn->error); 
            $_SESSION['error_details'] = 'Error interno al preparar la cancelación del servicio.'; 
            $conn->close(); 
            return false;
        } 
        $stmt->bind_param("ii", $userId, $id);
        $result = false; // Valor por defecto
        try {
            $result = $stmt->execute();
            if (!$result) { 
                error_log("Execute failed (Servicio cancel): " . $stmt->error); 
                $_SESSION['error_details'] = 'Error de base de datos al cancelar el servicio: ' . $stmt->error;
            }
        } catch (mysqli_sql_exception $e) { 
            error_log("Exception (Servicio cancel): " . $e->getMessage());
            $_SESSION['error_details'] = 'Error de base de datos al cancelar el servicio (' . $e->getCode() . '): ' . $e->getMessage(); 
            $result = false; 
        }
        $result = (bool) $result; // Asegurar bool
        if ($stmt) $stmt->close(); 
        $conn->close();
        return $result;
    }

}