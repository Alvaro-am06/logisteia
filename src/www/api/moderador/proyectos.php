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
    
    // Consulta para obtener todos los presupuestos con información del usuario
    $query = "SELECT 
                p.id_presupuesto as id,
                p.numero_presupuesto as codigo,
                COALESCE(
                    SUBSTRING_INDEX(SUBSTRING_INDEX(p.notas, 'Proyecto: ', -1), '\n', 1),
                    CONCAT('Presupuesto ', p.numero_presupuesto)
                ) as nombre,
                COALESCE(
                    SUBSTRING_INDEX(SUBSTRING_INDEX(p.notas, 'Descripción: ', -1), '\n', 1),
                    ''
                ) as descripcion,
                u.nombre as jefe_nombre,
                COALESCE(
                    SUBSTRING_INDEX(SUBSTRING_INDEX(p.notas, 'Cliente: ', -1), '\n', 1),
                    'Sin cliente'
                ) as cliente_nombre,
                p.estado,
                p.fecha_creacion as fecha_inicio,
                NULL as fecha_fin,
                0 as horas_estimadas,
                p.total as precio_estimado
              FROM presupuestos p
              LEFT JOIN usuarios u ON p.usuario_dni = u.dni
              ORDER BY p.fecha_creacion DESC";
    
    $stmt = $db->query($query);
    $proyectos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear datos
    foreach ($proyectos as &$proyecto) {
        $proyecto['fecha_inicio'] = date('d/m/Y', strtotime($proyecto['fecha_inicio']));
        $proyecto['precio_estimado'] = floatval($proyecto['precio_estimado']);
        $proyecto['horas_estimadas'] = intval($proyecto['horas_estimadas']);
        
        // Limpiar nombre si tiene formato extraño
        $proyecto['nombre'] = trim($proyecto['nombre']);
        if (empty($proyecto['nombre']) || $proyecto['nombre'] === 'Proyecto:') {
            $proyecto['nombre'] = 'Presupuesto ' . $proyecto['codigo'];
        }
        
        // Limpiar descripción
        $proyecto['descripcion'] = trim($proyecto['descripcion']);
        if ($proyecto['descripcion'] === 'Descripción:') {
            $proyecto['descripcion'] = '';
        }
        
        // Limpiar cliente
        $proyecto['cliente_nombre'] = trim($proyecto['cliente_nombre']);
        if (empty($proyecto['cliente_nombre']) || $proyecto['cliente_nombre'] === 'Cliente:') {
            $proyecto['cliente_nombre'] = 'Sin cliente';
        }
    }
    
    sendJsonSuccess($proyectos);
    
} catch (Exception $e) {
    logError('Error en proyectos.php: ' . $e->getMessage());
    sendJsonError('Error interno del servidor', 500);
}
