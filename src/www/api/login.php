<?php
/**
 * API endpoint para login de administradores.
 * 
 * Recibe POST con JSON: {"email": "...", "password": "..."}
 * Devuelve JSON con success o error.
 */

// Suprimir errores y warnings para que no contaminen la respuesta JSON
error_reporting(0);
ini_set('display_errors', 0);

// Establecer el header JSON antes que nada
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    require_once '../controladores/ControladorDeAutenticacion.php';
    
    $controller = new ControladordeAutenticacion();
    $controller->apiLogin();
} catch (Exception $e) {
    echo json_encode(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
}