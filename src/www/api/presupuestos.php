<?php
/**
 * API endpoint para guardar presupuestos.
 * 
 * Recibe POST con JSON: {
 *   "usuario_dni": "...",
 *   "total": 1234.56,
 *   "notas": "...",
 *   "detalles": [
 *     {
 *       "servicio_nombre": "...",
 *       "cantidad": 2,
 *       "precio": 100.00,
 *       "comentario": "..."
 *     }
 *   ]
 * }
 * 
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
    require_once __DIR__ . '/../modelos/ConexionBBDD.php';
    require_once __DIR__ . '/../modelos/Presupuesto.php';
    
    // Solo permitir POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        exit();
    }

    // Obtener datos del POST
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Datos inválidos']);
        exit();
    }

    // Validar campos requeridos
    if (!isset($input['usuario_dni']) || empty(trim($input['usuario_dni']))) {
        http_response_code(400);
        echo json_encode(['error' => 'DNI de usuario requerido']);
        exit();
    }

    if (!isset($input['total']) || !is_numeric($input['total'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Total inválido']);
        exit();
    }

    if (!isset($input['detalles']) || !is_array($input['detalles']) || empty($input['detalles'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Debe incluir al menos un servicio']);
        exit();
    }

    // Validar detalles
    foreach ($input['detalles'] as $detalle) {
        if (!isset($detalle['servicio_nombre']) || empty($detalle['servicio_nombre'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Nombre de servicio requerido en detalles']);
            exit();
        }
        if (!isset($detalle['cantidad']) || !is_numeric($detalle['cantidad']) || $detalle['cantidad'] <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Cantidad inválida en detalles']);
            exit();
        }
        if (!isset($detalle['precio']) || !is_numeric($detalle['precio'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Precio inválido en detalles']);
            exit();
        }
    }

    // Obtener conexión a la base de datos
    $database = new Conexion();
    $db = $database->obtener();
    
    // Crear instancia del modelo
    $presupuesto = new Presupuesto($db);
    
    // Asignar valores
    $presupuesto->usuario_dni = $input['usuario_dni'];
    $presupuesto->total = $input['total'];
    $presupuesto->notas = $input['notas'] ?? null;
    $presupuesto->estado = $input['estado'] ?? 'borrador';
    $presupuesto->validez_dias = $input['validez_dias'] ?? 30;
    
    // Crear presupuesto
    $id_presupuesto = $presupuesto->crear($input['detalles']);
    
    if ($id_presupuesto) {
        echo json_encode([
            'success' => true,
            'message' => 'Presupuesto creado exitosamente',
            'data' => [
                'id_presupuesto' => $id_presupuesto,
                'numero_presupuesto' => $presupuesto->numero_presupuesto
            ]
        ]);
        http_response_code(201);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error al crear el presupuesto']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
}
