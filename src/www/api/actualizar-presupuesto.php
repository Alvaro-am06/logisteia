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

// Cargar configuración centralizada
require_once __DIR__ . '/../config/config.php';

// Configurar CORS
setupCors();

// Manejar preflight OPTIONS
if (handlePreflight()) {
    exit();
}

header('Content-Type: application/json');

ob_start();

try {
    require_once __DIR__ . '/../modelos/ConexionBBDD.php';
    require_once __DIR__ . '/../modelos/Presupuesto.php';
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'PUT') {
        ob_end_clean();
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Método no permitido']);
        exit();
    }

    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['numero_presupuesto'])) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Número de presupuesto requerido']);
        exit();
    }

    $db = ConexionBBDD::obtener();
    
    // Verificar si es presupuesto wizard o clásico
    $esWizard = false;
    try {
        $queryCheck = "SELECT COUNT(*) FROM presupuestos_wizard WHERE numero_presupuesto = :numero";
        $stmtCheck = $db->prepare($queryCheck);
        $stmtCheck->execute([':numero' => $input['numero_presupuesto']]);
        $esWizard = $stmtCheck->fetchColumn() > 0;
    } catch (Exception $e) {
        // Tabla presupuestos_wizard no existe, no es wizard
        $esWizard = false;
    }
    
    $resultado = false;
    
    if ($esWizard && isset($input['estado'])) {
        // Actualizar presupuesto wizard
        $query = "UPDATE presupuestos_wizard SET estado = :estado WHERE numero_presupuesto = :numero";
        $stmt = $db->prepare($query);
        $resultado = $stmt->execute([
            ':estado' => $input['estado'],
            ':numero' => $input['numero_presupuesto']
        ]);
    } else {
        // Actualizar presupuesto clásico
        $presupuesto = new Presupuesto($db);
        $resultado = $presupuesto->actualizar($input);
    }

    if ($resultado) {
        ob_end_clean();
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Presupuesto actualizado exitosamente',
            'data' => ['numero_presupuesto' => $input['numero_presupuesto']]
        ]);
    } else {
        ob_end_clean();
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'No se pudo actualizar el presupuesto']);
    }
    
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    error_log('Error en actualizar-presupuesto.php: ' . $e->getMessage() . ' - ' . $e->getFile() . ':' . $e->getLine());
    echo json_encode(['success' => false, 'error' => 'Error interno: ' . $e->getMessage()]);
}
