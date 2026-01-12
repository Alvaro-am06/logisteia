<?php
/**
 * API endpoint para login de administradores.
 * 
 * Recibe POST con JSON: {"email": "...", "password": "..." }
 * Devuelve JSON con success o error.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

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
    
    $controller = new ControladorDeAutenticacion(); 
    $controller->apiLogin();
} catch (Exception $e) {
    echo json_encode(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
}