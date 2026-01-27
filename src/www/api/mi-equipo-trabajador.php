<?php
// Cargar configuración centralizada
require_once __DIR__ . '/../config/config.php';

// Configurar CORS y manejar preflight
setupCors();
handlePreflight();

header('Content-Type: application/json');

require_once __DIR__ . '/../modelos/ConexionBBDD.php';

// Verificar autenticación
$usuario = verificarAutenticacion();

if (!$usuario) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Usuario no autenticado']);
    exit;
}

if ($usuario['rol'] !== 'trabajador') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Solo los trabajadores pueden acceder a esta información']);
    exit;
}

try {
    $conn = ConexionBBDD::obtener();
    
    // Obtener el equipo del trabajador
    $stmt = $conn->prepare("
        SELECT 
            e.id,
            e.nombre as equipo_nombre,
            u.nombre as jefe_nombre,
            u.email as jefe_email,
            me.rol_proyecto,
            me.fecha_ingreso
        FROM miembros_equipo me
        INNER JOIN equipos e ON me.equipo_id = e.id
        INNER JOIN usuarios u ON e.jefe_dni = u.dni
        WHERE me.trabajador_dni = ?
        LIMIT 1
    ");
    
    $stmt->execute([$usuario['dni']]);
    $equipo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($equipo) {
        echo json_encode([
            'success' => true,
            'data' => $equipo
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'data' => null,
            'message' => 'No estás asignado a ningún equipo aún'
        ]);
    }
    
} catch(PDOException $e) {
    handleDatabaseError('Error al obtener información del equipo', $e);
}
