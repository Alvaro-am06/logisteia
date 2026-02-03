<?php
/**
 * API endpoint para obtener servicios activos.
 * 
 * Recibe GET y devuelve JSON con la lista de servicios disponibles.
 */

// Cargar configuración centralizada
require_once __DIR__ . '/../config/config.php';

// Configurar CORS y headers
setupCors();
header('Content-Type: application/json');
handlePreflight();

try {
    require_once __DIR__ . '/../modelos/ConexionBBDD.php';
    require_once __DIR__ . '/../modelos/Servicio.php';
    
    // Solo permitir GET
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        exit();
    }

    // Obtener conexión a la base de datos
    $database = new Conexion();
    $db = $database->obtener();
    
    // Crear instancia del modelo
    $servicio = new Servicio($db);
    
    // Obtener servicios activos
    $stmt = $servicio->obtenerActivos();
    $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Devolver respuesta exitosa
    echo json_encode([
        'success' => true,
        'data' => $servicios
    ]);
    http_response_code(200);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
}
