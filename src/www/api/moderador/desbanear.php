<?php
/**
 * API para desbanear usuarios (solo para moderadores)
 * 
 * POST: Desbanea un usuario por ID de baneo
 * Body: { "baneo_id": number }
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
require_once __DIR__ . '/../../modelos/AccionesAdministrativas.php';

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonError('Método no permitido', 405);
}

try {
    // Obtener datos del body
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['baneo_id'])) {
        sendJsonError('ID de baneo requerido', 400);
    }
    
    $baneoId = intval($input['baneo_id']);
    
    // Obtener DNI del moderador desde headers (enviado por Angular)
    $moderadorDni = $_SERVER['HTTP_X_USER_DNI'] ?? null;
    
    $db = ConexionBBDD::obtener();
    
    // Obtener información del baneo antes de actualizarlo
    $queryInfo = "SELECT usuario_dni, jefe_dni FROM historial_baneos WHERE id = :id";
    $stmtInfo = $db->prepare($queryInfo);
    $stmtInfo->execute([':id' => $baneoId]);
    $baneoInfo = $stmtInfo->fetch(PDO::FETCH_ASSOC);
    
    if (!$baneoInfo) {
        sendJsonError('Baneo no encontrado', 404);
    }
    
    // Actualizar el registro de baneo
    $query = "UPDATE historial_baneos 
              SET activo = 0, fecha_desbaneo = NOW() 
              WHERE id = :id";
    
    $stmt = $db->prepare($query);
    $resultado = $stmt->execute([':id' => $baneoId]);
    
    if ($resultado) {
        // Actualizar estado del usuario a 'activo'
        $queryUsuario = "UPDATE usuarios SET estado = 'activo', fecha_baneo = NULL, motivo_baneo = NULL 
                         WHERE dni = :dni";
        $stmtUsuario = $db->prepare($queryUsuario);
        $stmtUsuario->execute([':dni' => $baneoInfo['usuario_dni']]);
        
        // Registrar acción administrativa
        if ($moderadorDni) {
            $accion = new AccionesAdministrativas();
            $accion->registrar(
                $moderadorDni,
                'desbanear',
                $baneoInfo['usuario_dni'],
                'Usuario desbaneado por moderador'
            );
        }
        
        sendJsonSuccess(null, 'Usuario desbaneado exitosamente');
    } else {
        sendJsonError('Error al desbanear usuario', 500);
    }
    
} catch (Exception $e) {
    logError('Error en desbanear.php: ' . $e->getMessage());
    sendJsonError('Error interno del servidor', 500);
}
