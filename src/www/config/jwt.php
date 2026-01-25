<?php
/**
 * Helpers para autenticación JWT
 * 
 * Funciones para generar, validar y decodificar tokens JWT
 */

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Genera un token JWT para un usuario
 * 
 * @param array $userData Datos del usuario (dni, rol, nombre, email)
 * @return string Token JWT generado
 */
function generarTokenJWT($userData) {
    $issuedAt = time();
    $expire = $issuedAt + JWT_EXPIRATION;
    
    $payload = [
        'iat' => $issuedAt,              // Issued at: tiempo de emisión
        'exp' => $expire,                 // Expiration time: tiempo de expiración
        'iss' => APP_URL,                 // Issuer: emisor del token
        'data' => [
            'dni' => $userData['dni'],
            'rol' => $userData['rol'],
            'nombre' => $userData['nombre'],
            'email' => $userData['email']
        ]
    ];
    
    return JWT::encode($payload, JWT_SECRET, 'HS256');
}

/**
 * Valida y decodifica un token JWT
 * 
 * @param string $token Token JWT a validar
 * @return array|null Datos del usuario si el token es válido, null si es inválido
 */
function validarTokenJWT($token) {
    try {
        $decoded = JWT::decode($token, new Key(JWT_SECRET, 'HS256'));
        return (array)$decoded->data;
    } catch (Exception $e) {
        logError('Error al validar token JWT', $e);
        return null;
    }
}

/**
 * Obtiene el token JWT desde el header Authorization
 * 
 * @return string|null Token JWT o null si no existe
 */
function obtenerTokenDeHeader() {
    $headers = getallheaders();
    
    // Verificar header Authorization
    if (isset($headers['Authorization'])) {
        $authHeader = $headers['Authorization'];
        // Formato esperado: "Bearer TOKEN"
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }
    }
    
    // Verificar header X-Auth-Token (alternativo)
    if (isset($headers['X-Auth-Token'])) {
        return $headers['X-Auth-Token'];
    }
    
    return null;
}

/**
 * Verifica la autenticación mediante JWT
 * Si no hay token JWT, intenta verificar con headers personalizados (retrocompatibilidad)
 * 
 * @return array|null Datos del usuario autenticado o null si no está autenticado
 */
function verificarAutenticacionJWT() {
    // Intentar obtener token JWT
    $token = obtenerTokenDeHeader();
    
    if ($token) {
        // Validar token JWT
        $userData = validarTokenJWT($token);
        if ($userData) {
            return $userData;
        }
    }
    
    // Retrocompatibilidad: verificar headers personalizados
    return verificarAutenticacionHeaders();
}

/**
 * Verifica autenticación mediante headers personalizados (sistema anterior)
 * 
 * @return array|null Datos del usuario autenticado o null
 */
function verificarAutenticacionHeaders() {
    $userDni = $_SERVER['HTTP_X_USER_DNI'] ?? '';
    
    if (empty($userDni)) {
        return null;
    }
    
    // Verificar que el usuario existe y está activo
    try {
        require_once __DIR__ . '/../modelos/ConexionBBDD.php';
        $conn = ConexionBBDD::obtener();
        $stmt = $conn->prepare("SELECT dni, nombre, email, rol, estado FROM usuarios WHERE dni = ? AND estado = 'activo'");
        $stmt->execute([$userDni]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$usuario) {
            return null;
        }
        
        return [
            'dni' => $usuario['dni'],
            'rol' => $usuario['rol'],
            'nombre' => $usuario['nombre'],
            'email' => $usuario['email']
        ];
    } catch (Exception $e) {
        logError('Error en verificarAutenticacionHeaders', $e);
        return null;
    }
}

/**
 * Middleware de autenticación: verifica JWT y envía error si no está autenticado
 * 
 * @param array $rolesPermitidos Array de roles permitidos (opcional)
 * @return array Datos del usuario autenticado
 */
function requiereAutenticacion($rolesPermitidos = []) {
    $usuario = verificarAutenticacionJWT();
    
    if (!$usuario) {
        sendJsonError('No autenticado. Token inválido o expirado.', 401);
    }
    
    // Verificar rol si se especificaron roles permitidos
    if (!empty($rolesPermitidos) && !in_array($usuario['rol'], $rolesPermitidos)) {
        sendJsonError('Acceso no autorizado. Permisos insuficientes.', 403);
    }
    
    return $usuario;
}
