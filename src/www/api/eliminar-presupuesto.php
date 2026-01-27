<?php
/**
 * API endpoint para eliminar (lógicamente) un presupuesto.
 * 
 * Recibe DELETE/POST con JSON: {
 *   "numero_presupuesto": "..."
 * }
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
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Método no permitido']);
        exit();
    }

    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['numero_presupuesto'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Número de presupuesto requerido']);
        exit();
    }

    $database = new Conexion();
    $db = $database->obtener();
    $presupuesto = new Presupuesto($db);

    $resultado = $presupuesto->eliminar($input['numero_presupuesto']);

    if ($resultado) {
        echo json_encode([
            'success' => true,
            'message' => 'Presupuesto eliminado exitosamente'
        ]);
        http_response_code(200);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'No se pudo eliminar el presupuesto']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error interno: ' . $e->getMessage()]);
}
