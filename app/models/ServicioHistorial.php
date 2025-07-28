<?php
// app/models/ServicioHistorial.php
require_once __DIR__ . '/../../config/config.php';

class ServicioHistorial {

    /**
     * Obtiene todos los registros del historial para un servicio específico.
     * @param int $servicioId El ID del servicio.
     * @return array La lista de registros del historial.
     */
    public static function getByServicioId($servicioId) {
        $conn = dbConnect();
        $historial = [];
        if (!$conn) { return $historial; }

        $sql = "SELECT h.*, u.username as usuario_nombre
                FROM servicios_historial h
                LEFT JOIN usuarios u ON h.usuario_id = u.id_usuario
                WHERE h.servicio_id = ?
                ORDER BY h.fecha_cambio DESC";
        
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $servicioId);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    $historial[] = $row;
                }
                $result->free();
            }
            $stmt->close();
        }
        
        $conn->close();
        return $historial;
    }
}