<?php
/**
 * API endpoint para actualizar un presupuesto existente.
 * 
 * Recibe PUT/POST con JSON: {
 *   "numero_presupuesto": "...",
 *   "estado": "...", (opcional)
 *   "total": 1234.56, (opcional)
 *   "notas": "...", (opcional)
 *   "validez_dias": 30, (opcional)
 *   "detalles": [...] (opcional)
 * }
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    require_once __DIR__ . '/../modelos/ConexionBBDD.php';
    require_once __DIR__ . '/../modelos/Presupuesto.php';
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'PUT') {
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

    $resultado = $presupuesto->actualizar($input);

    if ($resultado) {
        echo json_encode([
            'success' => true,
            'message' => 'Presupuesto actualizado exitosamente',
            'data' => ['numero_presupuesto' => $input['numero_presupuesto']]
        ]);
        http_response_code(200);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'No se pudo actualizar el presupuesto']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error interno: ' . $e->getMessage()]);
}
