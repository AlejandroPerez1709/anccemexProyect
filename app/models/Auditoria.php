<?php
// app/models/Auditoria.php
require_once __DIR__ . '/../../config/config.php';

class Auditoria {

    /**
     * Registra una acción en la bitácora de auditoría.
     *
     * @param string $accion Descripción de la acción (ej: 'CREACIÓN DE SOCIO').
     * @param int|null $entidadId El ID del registro afectado (ej: el nuevo ID del socio).
     * @param string|null $entidadTipo El tipo de entidad afectada (ej: 'Socio').
     * @param string|null $descripcion Detalles adicionales sobre el evento.
     * @return bool True si el registro fue exitoso, false en caso contrario.
     */
    public static function registrar($accion, $entidadId = null, $entidadTipo = null, $descripcion = null) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user'])) {
            return false; 
        }

        $conn = dbConnect();
        if (!$conn) {
            error_log("Error de conexión a la BD al intentar registrar en auditoría.");
            return false;
        }

        $sql = "INSERT INTO auditoria (usuario_id, usuario_nombre, accion, tipo_entidad, id_entidad, descripcion) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("Error al preparar la consulta de auditoría: " . $conn->error);
            $conn->close();
            return false;
        }

        $userId = $_SESSION['user']['id_usuario'];
        $userName = $_SESSION['user']['nombre'] . ' ' . $_SESSION['user']['apellido_paterno'];

        $stmt->bind_param(
            "isssis",
            $userId,
            $userName,
            $accion,
            $entidadTipo,
            $entidadId,
            $descripcion
        );

        $success = $stmt->execute();

        if (!$success) {
            error_log("Error al ejecutar la inserción en auditoría: " . $stmt->error);
        }

        $stmt->close();
        $conn->close();

        return $success;
    }

    /**
     * Construye la cláusula WHERE para las consultas de auditoría.
     * @param array $filters Filtros a aplicar.
     * @param array &$params Array de parámetros para bind_param.
     * @param string &$types String de tipos para bind_param.
     * @return string
     */
    private static function buildWhereClause($filters, &$params, &$types) {
        $whereClauses = [];
        if (!empty($filters['usuario_id'])) {
            $whereClauses[] = "usuario_id = ?";
            $params[] = $filters['usuario_id'];
            $types .= "i";
        }
        if (!empty($filters['fecha_inicio'])) {
            $whereClauses[] = "fecha_hora >= ?";
            $params[] = $filters['fecha_inicio'] . ' 00:00:00';
            $types .= "s";
        }
        if (!empty($filters['fecha_fin'])) {
            $whereClauses[] = "fecha_hora <= ?";
            $params[] = $filters['fecha_fin'] . ' 23:59:59';
            $types .= "s";
        }
        return !empty($whereClauses) ? " WHERE " . implode(" AND ", $whereClauses) : "";
    }

    /**
     * Cuenta todos los registros de auditoría, aplicando filtros.
     * @param array $filters Filtros de búsqueda.
     * @return int
     */
    public static function countAll($filters = []) {
        $conn = dbConnect();
        if (!$conn) return 0;

        $params = [];
        $types = "";
        $whereSql = self::buildWhereClause($filters, $params, $types);
        $sql = "SELECT COUNT(id_auditoria) as total FROM auditoria" . $whereSql;
        
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

    /**
     * Obtiene todos los registros de auditoría, con filtros y paginación.
     * @param array $filters Filtros.
     * @param int $limit Límite de registros.
     * @param int $offset Desplazamiento.
     * @return array
     */
    public static function getAll($filters = [], $limit = 15, $offset = 0) {
        $conn = dbConnect();
        $registros = [];
        if (!$conn) return $registros;

        $params = [];
        $types = "";
        $whereSql = self::buildWhereClause($filters, $params, $types);
        $sql = "SELECT * FROM auditoria" . $whereSql . " ORDER BY fecha_hora DESC";

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
                    $registros[] = $row;
                }
                $result->free();
            }
            $stmt->close();
        }
        
        $conn->close();
        return $registros;
    }
}