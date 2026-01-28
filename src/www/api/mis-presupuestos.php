<?php
/**
 * API endpoint para obtener presupuestos de un usuario.
 * 
 * Recibe GET con parámetro dni
 * Devuelve JSON con la lista de presupuestos del usuario.
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

    // Validar DNI
    if (!isset($_GET['dni']) || empty($_GET['dni'])) {
        http_response_code(400);
        echo json_encode(['error' => 'DNI requerido']);
        exit();
    }

    $dni = filter_var($_GET['dni'], FILTER_SANITIZE_SPECIAL_CHARS);

    // Obtener conexión a la base de datos
    $database = new Conexion();
    $db = $database->obtener();
    
    // Crear instancia del modelo
    $presupuesto = new Presupuesto($db);
    
    // Obtener presupuestos del usuario
    $stmt = $presupuesto->obtenerPorUsuario($dni);
    $presupuestos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Parsear proyecto y cliente de las notas para cada presupuesto
    foreach ($presupuestos as &$pres) {
        $notas = $pres['notas'] ?? '';
        
        // Extraer nombre del proyecto
        if (preg_match('/Proyecto:\s*(.+?)$/m', $notas, $matches) || 
            preg_match('/Presupuesto automático para proyecto:\s*(.+?)$/mi', $notas, $matches)) {
            $pres['nombre_proyecto'] = trim($matches[1]);
        } else {
            $pres['nombre_proyecto'] = '-';
        }
        
        // Extraer nombre del cliente
        if (preg_match('/Cliente:\s*(.+?)$/m', $notas, $matches)) {
            $pres['cliente_nombre'] = trim($matches[1]);
        } else {
            $pres['cliente_nombre'] = '-';
        }
    }
    
    // Devolver respuesta exitosa
    echo json_encode([
        'success' => true,
        'data' => $presupuestos
    ]);
    http_response_code(200);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
}
