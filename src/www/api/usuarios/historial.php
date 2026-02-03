<?php
/**
 * API REST para obtener historial de acciones administrativas.
 * 
 * GET: Obtiene todas las acciones administrativas registradas
 */

// Cargar configuración centralizada
require_once __DIR__ . '/../config/config.php';

// Configurar CORS y headers
setupCors();
header('Content-Type: application/json');
handlePreflight();

// Solo permitir GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Método no permitido'
    ]);
    exit;
}

// Cargar modelos
require_once __DIR__ . '/../modelos/ConexionBBDD.php';
require_once __DIR__ . '/../modelos/AccionesAdministrativas.php';

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $modeloAccion = new AccionesAdministrativas();
    
    // Obtener todas las acciones administrativas
    $historial = $modeloAccion->obtenerTodos();
    
    echo json_encode([
        'success' => true,
        'data' => $historial
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
