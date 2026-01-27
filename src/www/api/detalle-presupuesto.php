<?php
/**
 * API endpoint para obtener los detalles de un presupuesto.
 * 
 * Recibe GET con parámetro numero (numero_presupuesto)
 * Devuelve JSON con la lista de servicios del presupuesto.
 */

// Cargar configuración centralizada
require_once __DIR__ . '/../config/config.php';

// Configurar CORS
setupCors();

// Manejar preflight OPTIONS
if (handlePreflight()) {
    exit();
}

header('Content-Type: application/json');

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
