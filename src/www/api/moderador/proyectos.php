<?php
/**
 * API para obtener todos los proyectos/presupuestos (solo para moderadores)
 * 
 * GET: Obtiene todos los presupuestos del sistema
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
    
    // Consulta para obtener todos los proyectos con información del jefe y cliente
    $query = "SELECT 
                p.id,
                p.codigo,
                p.nombre,
                p.descripcion,
                u.nombre as jefe_nombre,
                c.nombre as cliente_nombre,
                p.estado,
                p.fecha_inicio,
                p.fecha_fin,
                COALESCE(p.horas_estimadas, 0) as horas_estimadas,
                COALESCE(p.precio_estimado, 0) as precio_estimado
              FROM proyectos p
              LEFT JOIN usuarios u ON p.jefe_dni = u.dni
              LEFT JOIN clientes c ON p.cliente_id = c.id
              ORDER BY p.fecha_inicio DESC";
    
    $stmt = $db->query($query);
    $proyectos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear datos
    foreach ($proyectos as &$proyecto) {
        $proyecto['fecha_inicio'] = date('d/m/Y', strtotime($proyecto['fecha_inicio']));
        $proyecto['precio_estimado'] = floatval($proyecto['precio_estimado']);
        $proyecto['horas_estimadas'] = intval($proyecto['horas_estimadas']);
        $proyecto['id'] = intval($proyecto['id']);
        
        // Valores por defecto si son NULL
        if (empty($proyecto['cliente_nombre'])) {
            $proyecto['cliente_nombre'] = 'Sin cliente';
        }
        if (empty($proyecto['jefe_nombre'])) {
            $proyecto['jefe_nombre'] = 'Sin asignar';
        }
        if (empty($proyecto['descripcion'])) {
            $proyecto['descripcion'] = '';
        }
    }
    
    sendJsonSuccess($proyectos);
    
} catch (Exception $e) {
    logError('Error en proyectos.php: ' . $e->getMessage());
    sendJsonError('Error interno del servidor', 500);
}
