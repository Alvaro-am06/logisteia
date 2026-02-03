<?php
/**
 * API para banear/suspender usuarios (solo para moderadores)
 * 
 * POST: Banea o suspende a un usuario
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

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonError('Método no permitido', 405);
}

try {
    $db = ConexionBBDD::obtener();
    
    // Obtener datos JSON
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['usuario_dni']) || !isset($data['accion'])) {
        sendJsonError('DNI del usuario y acción son requeridos', 400);
    }
    
    $usuario_dni = $data['usuario_dni'];
    $accion = $data['accion']; // 'banear', 'suspender', 'activar', 'eliminar'
    $motivo = $data['motivo'] ?? 'Sin motivo especificado';
    
    // Obtener DNI del moderador desde headers
    $moderador_dni = $_SERVER['HTTP_X_USER_DNI'] ?? null;
    
    if (!$moderador_dni) {
        sendJsonError('Moderador no autenticado', 401);
    }
    
    // Verificar que el usuario existe
    $stmtCheck = $db->prepare("SELECT dni, nombre, estado FROM usuarios WHERE dni = ?");
    $stmtCheck->execute([$usuario_dni]);
    $usuario = $stmtCheck->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        sendJsonError('Usuario no encontrado', 404);
    }
    
    // No permitir acciones sobre moderadores
    $stmtRol = $db->prepare("SELECT rol FROM usuarios WHERE dni = ?");
    $stmtRol->execute([$usuario_dni]);
    $rolUsuario = $stmtRol->fetchColumn();
    
    if ($rolUsuario === 'moderador') {
        sendJsonError('No se puede realizar esta acción sobre un moderador', 403);
    }
    
    $db->beginTransaction();
    
    try {
        switch ($accion) {
            case 'banear':
                // Cambiar estado a baneado
                $stmt = $db->prepare("UPDATE usuarios SET estado = 'suspendido' WHERE dni = ?");
                $stmt->execute([$usuario_dni]);
                
                // Registrar en historial de baneos si la tabla existe
                try {
                    $checkTable = $db->query("SHOW TABLES LIKE 'historial_baneos'");
                    if ($checkTable->fetch() !== false) {
                        $stmtBaneo = $db->prepare("
                            INSERT INTO historial_baneos 
                            (usuario_dni, jefe_dni, motivo, fecha_baneo, activo) 
                            VALUES (?, ?, ?, NOW(), 1)
                        ");
                        $stmtBaneo->execute([$usuario_dni, $moderador_dni, $motivo]);
                    }
                } catch (Exception $e) {
                    // Continuar aunque no se pueda registrar en historial
                }
                
                $mensaje = 'Usuario baneado exitosamente';
                break;
                
            case 'suspender':
                $stmt = $db->prepare("UPDATE usuarios SET estado = 'suspendido' WHERE dni = ?");
                $stmt->execute([$usuario_dni]);
                $mensaje = 'Usuario suspendido exitosamente';
                break;
                
            case 'activar':
                $stmt = $db->prepare("UPDATE usuarios SET estado = 'activo' WHERE dni = ?");
                $stmt->execute([$usuario_dni]);
                
                // Desactivar baneos activos si existen
                try {
                    $checkTable = $db->query("SHOW TABLES LIKE 'historial_baneos'");
                    if ($checkTable->fetch() !== false) {
                        $stmtDesbanear = $db->prepare("
                            UPDATE historial_baneos 
                            SET activo = 0, fecha_desbaneo = NOW() 
                            WHERE usuario_dni = ? AND activo = 1
                        ");
                        $stmtDesbanear->execute([$usuario_dni]);
                    }
                } catch (Exception $e) {
                    // Continuar aunque no se pueda actualizar historial
                }
                
                $mensaje = 'Usuario activado exitosamente';
                break;
                
            case 'eliminar':
                $stmt = $db->prepare("UPDATE usuarios SET estado = 'eliminado' WHERE dni = ?");
                $stmt->execute([$usuario_dni]);
                $mensaje = 'Usuario eliminado exitosamente';
                break;
                
            default:
                $db->rollBack();
                sendJsonError('Acción no válida', 400);
        }
        
        // Registrar acción administrativa si la tabla existe
        try {
            $checkTable = $db->query("SHOW TABLES LIKE 'acciones_administrativas'");
            if ($checkTable->fetch() !== false) {
                $stmtAccion = $db->prepare("
                    INSERT INTO acciones_administrativas 
                    (administrador_dni, accion, usuario_dni, motivo, ip_origen, creado_en) 
                    VALUES (?, ?, ?, ?, ?, NOW())
                ");
                $ip = $_SERVER['REMOTE_ADDR'] ?? 'Desconocida';
                $stmtAccion->execute([
                    $moderador_dni,
                    ucfirst($accion) . ' usuario',
                    $usuario_dni,
                    $motivo,
                    $ip
                ]);
            }
        } catch (Exception $e) {
            // Continuar aunque no se pueda registrar la acción
        }
        
        $db->commit();
        
        sendJsonSuccess([
            'message' => $mensaje,
            'usuario_dni' => $usuario_dni,
            'nueva_estado' => $accion === 'eliminar' ? 'eliminado' : ($accion === 'activar' ? 'activo' : 'suspendido')
        ]);
        
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    logError('Error en moderador/banear.php', $e);
    sendJsonError('Error interno del servidor', 500);
}
