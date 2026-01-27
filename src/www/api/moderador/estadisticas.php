<?php
/**
 * API para obtener estadísticas del moderador
 * 
 * GET: Obtiene todas las estadísticas del sistema para el dashboard del moderador
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
    
    $estadisticas = [];
    
    // Total de usuarios
    $stmt = $db->query("SELECT COUNT(*) FROM usuarios WHERE estado != 'eliminado'");
    $estadisticas['usuarios_total'] = (int)$stmt->fetchColumn();
    
    // Usuarios jefes de equipo
    $stmt = $db->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'jefe_equipo' AND estado = 'activo'");
    $estadisticas['usuarios_jefes'] = (int)$stmt->fetchColumn();
    
    // Usuarios trabajadores
    $stmt = $db->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'trabajador' AND estado = 'activo'");
    $estadisticas['usuarios_trabajadores'] = (int)$stmt->fetchColumn();
    
    // Usuarios baneados
    $stmt = $db->query("SELECT COUNT(*) FROM usuarios WHERE estado = 'baneado'");
    $estadisticas['usuarios_baneados'] = (int)$stmt->fetchColumn();
    
    // Usuarios eliminados
    $stmt = $db->query("SELECT COUNT(*) FROM usuarios WHERE estado = 'eliminado'");
    $estadisticas['usuarios_eliminados'] = (int)$stmt->fetchColumn();
    
    // Total de equipos (si existe la tabla)
    try {
        $stmt = $db->query("SELECT COUNT(*) FROM equipos");
        $estadisticas['equipos_total'] = (int)$stmt->fetchColumn();
    } catch (PDOException $e) {
        $estadisticas['equipos_total'] = 0;
    }
    
    // Estadísticas de presupuestos/proyectos
    $stmt = $db->query("SELECT COUNT(*) FROM presupuestos");
    $estadisticas['proyectos_total'] = (int)$stmt->fetchColumn();
    
    $stmt = $db->query("SELECT COUNT(*) FROM presupuestos WHERE estado = 'borrador'");
    $estadisticas['proyectos_planificacion'] = (int)$stmt->fetchColumn();
    
    $stmt = $db->query("SELECT COUNT(*) FROM presupuestos WHERE estado IN ('enviado', 'aprobado')");
    $estadisticas['proyectos_en_proceso'] = (int)$stmt->fetchColumn();
    
    $stmt = $db->query("SELECT COUNT(*) FROM presupuestos WHERE estado = 'finalizado'");
    $estadisticas['proyectos_finalizados'] = (int)$stmt->fetchColumn();
    
    $stmt = $db->query("SELECT COUNT(*) FROM presupuestos WHERE estado = 'rechazado'");
    $estadisticas['proyectos_cancelados'] = (int)$stmt->fetchColumn();
    
    // Baneos activos
    try {
        $stmt = $db->query("SELECT COUNT(*) FROM historial_baneos WHERE activo = 1");
        $estadisticas['baneos_activos'] = (int)$stmt->fetchColumn();
    } catch (PDOException $e) {
        $estadisticas['baneos_activos'] = 0;
    }
    
    // Acciones de la última semana
    try {
        $stmt = $db->query("SELECT COUNT(*) FROM acciones_administrativas WHERE creado_en >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
        $estadisticas['acciones_ultima_semana'] = (int)$stmt->fetchColumn();
    } catch (PDOException $e) {
        $estadisticas['acciones_ultima_semana'] = 0;
    }
    
    sendJsonSuccess($estadisticas);
    
} catch (Exception $e) {
    logError('Error en estadisticas.php: ' . $e->getMessage());
    sendJsonError('Error interno del servidor', 500);
}
