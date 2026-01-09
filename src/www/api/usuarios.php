<?php
/**
 * API REST para gestión de usuarios (HU-03).
 * 
 * GET: Obtiene lista de todos los usuarios
 * POST: Cambia el estado de un usuario (activar/suspender/eliminar)
 * GET /{dni}: Obtiene detalle de un usuario específico con historial
 */

// Configurar headers CORS y JSON
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Manejar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

// Cargar modelos
require_once __DIR__ . '/../modelos/ConexionBBDD.php';
require_once __DIR__ . '/../modelos/Usuarios.php';
require_once __DIR__ . '/../modelos/AccionesAdministrativas.php';

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inicializar modelos
$modeloUsuario = new Usuarios();
$modeloAccion = new AccionesAdministrativas();

// Obtener método HTTP
$metodo = $_SERVER['REQUEST_METHOD'];

try {
    if ($metodo === 'GET') {
        // Verificar si se solicita un usuario específico por DNI
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $pathParts = explode('/', trim($path, '/'));
        $dni = null;
        
        // Si la URL termina con un DNI (ejemplo: /api/usuarios/22222222B)
        if (count($pathParts) > 0 && end($pathParts) !== 'usuarios.php') {
            $dni = end($pathParts);
        }
        
        if ($dni) {
            // Obtener detalle de usuario específico con historial
            $usuario = $modeloUsuario->obtenerPorDni($dni);
            
            if (!$usuario) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Usuario no encontrado'
                ]);
                exit;
            }
            
            // Obtener historial del usuario
            $historial = $modeloAccion->obtenerPorUsuario($dni);
            
            // Agregar campo 'estado' basado en rol (para compatibilidad con frontend)
            $usuario['estado'] = $usuario['rol'] === 'administrador' ? 'activo' : 'suspendido';
            $usuario['telefono'] = $usuario['telefono'] ?? '';
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'usuario' => $usuario,
                    'historial' => $historial
                ]
            ]);
        } else {
            // Listar todos los usuarios
            $usuarios = $modeloUsuario->obtenerTodos();
            
            // Agregar campo 'estado' y 'telefono' a cada usuario
            foreach ($usuarios as &$user) {
                $user['estado'] = $user['rol'] === 'administrador' ? 'activo' : 'suspendido';
                $user['telefono'] = $user['telefono'] ?? '';
                $user['fecha_registro'] = $user['fecha_registro'] ?? date('Y-m-d H:i:s');
            }
            
            echo json_encode([
                'success' => true,
                'data' => $usuarios
            ]);
        }
        
    } elseif ($metodo === 'POST') {
        // Cambiar estado de usuario
        
        // Obtener DNI de la URL
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $pathParts = explode('/', trim($path, '/'));
        $dni = null;
        
        if (count($pathParts) > 0 && end($pathParts) !== 'usuarios.php') {
            $dni = end($pathParts);
        }
        
        if (!$dni) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'DNI no especificado'
            ]);
            exit;
        }
        
        // Leer datos del cuerpo de la petición
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['operacion'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Operación no especificada'
            ]);
            exit;
        }
        
        $operacion = $input['operacion'];
        $motivo = $input['motivo'] ?? null;
        
        // Validar operación
        $operacionesValidas = ['activar', 'suspender', 'eliminar'];
        if (!in_array($operacion, $operacionesValidas)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Operación inválida'
            ]);
            exit;
        }
        
        // Verificar que el usuario existe
        $usuario = $modeloUsuario->obtenerPorDni($dni);
        if (!$usuario) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Usuario no encontrado'
            ]);
            exit;
        }
        
        // Obtener DNI del administrador (simulación por ahora)
        // TODO: Obtener del token de sesión o JWT
        $adminDni = $_SESSION['usuario_dni'] ?? '11111111A';
        
        // Ejecutar operación
        try {
            if ($operacion === 'activar') {
                $modeloUsuario->activar($dni);
                $modeloAccion->registrar($adminDni, 'activar', $dni, $motivo);
                $mensaje = 'Usuario activado correctamente';
            } elseif ($operacion === 'suspender') {
                $modeloUsuario->suspender($dni);
                $modeloAccion->registrar($adminDni, 'suspender', $dni, $motivo);
                $mensaje = 'Usuario suspendido correctamente';
            } elseif ($operacion === 'eliminar') {
                $modeloUsuario->eliminarLogico($dni);
                $modeloAccion->registrar($adminDni, 'eliminar', $dni, $motivo);
                $mensaje = 'Usuario eliminado correctamente';
            }
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'message' => $mensaje
                ]
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error al ejecutar operación: ' . $e->getMessage()
            ]);
        }
        
    } else {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'error' => 'Método no permitido'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
