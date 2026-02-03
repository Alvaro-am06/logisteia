<?php
/**
 * API para obtener todos los usuarios del sistema (solo para moderadores)
 * 
 * GET: Obtiene todos los usuarios con su información
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
    
    // Consulta para obtener todos los usuarios con información de equipos
    $query = "SELECT 
                u.dni,
                u.nombre,
                u.email,
                u.rol,
                u.estado,
                u.telefono,
                u.fecha_registro,
                e.nombre as equipo_nombre,
                e.id as equipo_id,
                (SELECT COUNT(*) FROM proyectos p WHERE p.jefe_dni = u.dni) as total_proyectos,
                (SELECT COUNT(*) FROM miembros_equipo me WHERE me.trabajador_dni = u.dni AND me.activo = 1) as equipos_participante
              FROM usuarios u
              LEFT JOIN equipos e ON u.dni = e.jefe_dni
              WHERE u.rol != 'moderador'
              ORDER BY u.fecha_registro DESC";
    
    $stmt = $db->query($query);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear datos
    foreach ($usuarios as &$usuario) {
        $usuario['dni'] = $usuario['dni'];
        $usuario['total_proyectos'] = intval($usuario['total_proyectos']);
        $usuario['equipos_participante'] = intval($usuario['equipos_participante']);
        
        // Formatear fecha_registro
        if ($usuario['fecha_registro']) {
            $usuario['fecha_registro'] = date('d/m/Y H:i', strtotime($usuario['fecha_registro']));
        }
        
        // Si no tiene equipo como jefe
        if (empty($usuario['equipo_nombre'])) {
            $usuario['equipo_nombre'] = null;
            $usuario['equipo_id'] = null;
        } else {
            $usuario['equipo_id'] = intval($usuario['equipo_id']);
        }
    }
    
    sendJsonSuccess($usuarios);
    
} catch (Exception $e) {
    logError('Error en moderador/usuarios.php', $e);
    sendJsonError('Error interno del servidor', 500);
}
