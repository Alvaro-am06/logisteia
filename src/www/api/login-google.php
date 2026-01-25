<?php
// Headers CORS PRIMERO (antes de cualquier output)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-User-DNI, X-User-Rol, X-User-Nombre, X-User-Email');
header('Access-Control-Allow-Credentials: false');
header('Content-Type: application/json; charset=UTF-8');

// Manejar OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

// Cargar configuración centralizada
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/jwt.php';

// Configurar CORS y manejar preflight (ya se hizo arriba, pero por compatibilidad)
setupCors();
handlePreflight();

require_once __DIR__ . '/../modelos/ConexionBBDD.php';

try {
    // Obtener datos JSON
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    error_log("Login Google - Datos recibidos: " . print_r($data, true));
    
    if (!isset($data['email']) || !isset($data['nombre'])) {
        error_log("Login Google - Error: Datos incompletos");
        echo json_encode([
            'success' => false,
            'error' => 'Datos incompletos de Google'
        ]);
        exit;
    }
    
    $email = trim($data['email']);
    $nombre = trim($data['nombre']);
    $picture = isset($data['picture']) ? trim($data['picture']) : '';
    $checkOnly = isset($data['checkOnly']) && $data['checkOnly'] === true;
    
    // Conectar a la base de datos
    $db = ConexionBBDD::obtenerInstancia()->obtenerBBDD();
    
    error_log("Login Google - Buscando usuario: " . $email);
    
    // Verificar si el usuario ya existe
    $stmt = $db->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    error_log("Login Google - Usuario encontrado: " . ($usuario ? "SI" : "NO"));
    
    if ($usuario) {
        // Usuario existe - iniciar sesión
        
        // Verificar si el usuario está activo
        if ($usuario['estado'] === 'eliminado') {
            echo json_encode([
                'success' => false,
                'error' => 'Este usuario ha sido eliminado'
            ]);
            exit;
        }
        
        if ($usuario['estado'] === 'baneado') {
            echo json_encode([
                'success' => false,
                'error' => 'Este usuario ha sido baneado'
            ]);
            exit;
        }
        
        // Obtener datos adicionales según el rol
        $datosAdicionales = [];
        
        if ($usuario['rol'] === 'moderador') {
            // Estadísticas de moderador
            $stmt = $db->query("SELECT COUNT(*) as total FROM usuarios WHERE estado != 'eliminado'");
            $datosAdicionales['usuarios_total'] = $stmt->fetchColumn();
            
            $stmt = $db->query("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'jefe_equipo' AND estado = 'activo'");
            $datosAdicionales['usuarios_jefes'] = $stmt->fetchColumn();
            
            $stmt = $db->query("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'trabajador' AND estado = 'activo'");
            $datosAdicionales['usuarios_trabajadores'] = $stmt->fetchColumn();
            
            $stmt = $db->query("SELECT COUNT(*) as total FROM usuarios WHERE estado = 'baneado'");
            $datosAdicionales['usuarios_baneados'] = $stmt->fetchColumn();
            
        } else if ($usuario['rol'] === 'jefe_equipo') {
            // Datos de jefe de equipo
            $stmt = $db->prepare("SELECT id, nombre FROM equipos WHERE jefe_dni = ?");
            $stmt->execute([$usuario['dni']]);
            $equipo = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($equipo) {
                $datosAdicionales['equipo_nombre'] = $equipo['nombre'];
                
                // Contar miembros del equipo usando equipo_id
                $stmt = $db->prepare("SELECT COUNT(*) as total FROM miembros_equipo WHERE equipo_id = ?");
                $stmt->execute([$equipo['id']]);
                $datosAdicionales['miembros_count'] = $stmt->fetchColumn();
            } else {
                $datosAdicionales['equipo_nombre'] = 'Sin equipo';
                $datosAdicionales['miembros_count'] = 0;
            }
        }
        
        // Generar token JWT
        $token = generarTokenJWT([
            'dni' => $usuario['dni'],
            'rol' => $usuario['rol'],
            'nombre' => $usuario['nombre'],
            'email' => $usuario['email']
        ]);
        
        // Preparar respuesta
        $responseData = array_merge([
            'dni' => $usuario['dni'],
            'nombre' => $usuario['nombre'],
            'email' => $usuario['email'],
            'telefono' => $usuario['telefono'],
            'rol' => $usuario['rol'],
            'estado' => $usuario['estado'],
            'fecha_registro' => $usuario['fecha_registro'],
            'token' => $token
        ], $datosAdicionales);
        
        echo json_encode([
            'success' => true,
            'data' => [
                'exists' => true,
                'usuario' => $responseData
            ]
        ]);
        
    } else {
        // Usuario no existe
        if ($checkOnly) {
            // Solo estamos verificando, no crear el usuario aún
            echo json_encode([
                'success' => true,
                'data' => [
                    'exists' => false
                ]
            ]);
        } else {
            // Modo antiguo: crear usuario automáticamente (por compatibilidad)
            echo json_encode([
                'success' => false,
                'error' => 'Usuario no encontrado. Use completar-registro-google.php'
            ]);
     rror_log("Login Google - Error: " . $e->getMessage());
    error_log("Login Google - Stack trace: " . $e->getTraceAsString());
    e   }
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
?>
