<?php
/**
 * API para obtener el historial de baneos (solo para moderadores)
 * 
 * GET: Obtiene todos los baneos con información del usuario y jefe
 */

// Cargar configuración centralizada
require_once __DIR__ . '/../../config/config.php';

// Configurar CORS
setupCors();

// Manejar preflight OPTIONS
if (handlePreflight()) {
    exit();
}

header('Content-Type: application/json');

require_once __DIR__ . '/../../modelos/ConexionBBDD.php';

// Solo permitir GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendJsonError('Método no permitido', 405);
}

try {
    $db = ConexionBBDD::obtener();
    
    // Consulta para obtener el historial de baneos con nombres de usuarios
    $query = "SELECT 
                hb.id,
                hb.usuario_dni,
                u1.nombre as usuario_nombre,
                u1.email as usuario_email,
                hb.jefe_dni,
                u2.nombre as jefe_nombre,
                hb.motivo,
                hb.fecha_baneo,
                hb.fecha_desbaneo,
                hb.activo
              FROM historial_baneos hb
              LEFT JOIN usuarios u1 ON hb.usuario_dni = u1.dni
              LEFT JOIN usuarios u2 ON hb.jefe_dni = u2.dni
              ORDER BY hb.fecha_baneo DESC";
    
    $stmt = $db->query($query);
    $baneos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear fechas y booleanos
    foreach ($baneos as &$baneo) {
        $baneo['activo'] = (bool)$baneo['activo'];
        $baneo['fecha_baneo'] = date('d/m/Y H:i', strtotime($baneo['fecha_baneo']));
        if ($baneo['fecha_desbaneo']) {
            $baneo['fecha_desbaneo'] = date('d/m/Y H:i', strtotime($baneo['fecha_desbaneo']));
        }
    }
    
    sendJsonSuccess($baneos);
    
} catch (PDOException $e) {
    // Si la tabla no existe, devolver array vacío
    if (strpos($e->getMessage(), "doesn't exist") !== false) {
        sendJsonSuccess([]);
    } else {
        logError('Error en historial-baneos.php: ' . $e->getMessage());
        sendJsonError('Error interno del servidor', 500);
    }
} catch (Exception $e) {
    logError('Error en historial-baneos.php: ' . $e->getMessage());
    sendJsonError('Error interno del servidor', 500);
}
