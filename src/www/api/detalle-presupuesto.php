<?php
/**
 * API endpoint para obtener los detalles de un presupuesto.
 * 
 * Recibe GET con parámetro numero (numero_presupuesto)
 * Devuelve JSON con la lista de servicios del presupuesto.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Establecer el header JSON antes que nada
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    require_once __DIR__ . '/../modelos/ConexionBBDD.php';
    require_once __DIR__ . '/../modelos/Presupuesto.php';
    
    // Solo permitir GET
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        exit();
    }

    // Validar número de presupuesto
    if (!isset($_GET['numero']) || empty($_GET['numero'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Número de presupuesto requerido']);
        exit();
    }

    $numero = filter_var($_GET['numero'], FILTER_SANITIZE_SPECIAL_CHARS);

    // Obtener conexión a la base de datos
    $database = new Conexion();
    $db = $database->obtener();
    
    // Crear instancia del modelo
    $presupuesto = new Presupuesto($db);
    
    // Obtener detalles del presupuesto
    $detalles = $presupuesto->obtenerDetalles($numero);
    
    // Devolver respuesta exitosa
    echo json_encode([
        'success' => true,
        'data' => $detalles
    ]);
    http_response_code(200);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
}
