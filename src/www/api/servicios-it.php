<?php
/**
 * API endpoint para obtener servicios IT activos.
 * 
 * Consulta la tabla servicios_informatica
 */

// Cargar configuración centralizada
require_once __DIR__ . '/../config/config.php';

// Configurar CORS y headers
setupCors();
header('Content-Type: application/json');
handlePreflight();

try {
    require_once __DIR__ . '/../modelos/ConexionBBDD.php';
    
    // Solo permitir GET
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        exit();
    }

    // Obtener conexión a la base de datos
    $database = new Conexion();
    $db = $database->obtener();
    
    // Consultar servicios IT activos
    $query = "SELECT 
                id,
                nombre,
                categoria as categoria_nombre,
                descripcion,
                precio_base,
                unidad,
                tecnologias,
                activo as esta_activo
              FROM servicios_informatica 
              WHERE activo = 1 
              ORDER BY categoria, nombre";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Devolver respuesta exitosa
    echo json_encode([
        'success' => true,
        'data' => $servicios,
        'total' => count($servicios)
    ]);
    http_response_code(200);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error interno del servidor: ' . $e->getMessage()
    ]);
}
