<?php
/**
 * API endpoint para obtener presupuestos wizard de un usuario.
 * 
 * Recibe GET con parámetro dni
 * Devuelve JSON con la lista de presupuestos del configurador.
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
    require_once __DIR__ . '/../modelos/PresupuestoWizard.php';
    
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
    $db = Conexion::obtener();
    
    // Crear instancia del modelo
    $presupuesto = new PresupuestoWizard($db);
    
    // Obtener presupuestos del usuario
    $stmt = $presupuesto->obtenerPorUsuario($dni);
    $presupuestos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Parsear las notas para extraer los datos del wizard
    $presupuestos_wizard = [];
    foreach ($presupuestos as $p) {
        // Solo procesar presupuestos creados desde el configurador
        if (isset($p['notas']) && strpos($p['notas'], 'PRESUPUESTO GENERADO DESDE CONFIGURADOR') !== false) {
            $notas = $p['notas'];
            
            // Extraer datos usando expresiones regulares mejoradas
            if (preg_match('/Proyecto:\s*(.+?)$/m', $notas, $matches)) {
                $p['nombre_proyecto'] = trim($matches[1]);
            }
            
            if (preg_match('/Descripción:\s*(.+?)$/m', $notas, $matches)) {
                $p['descripcion_proyecto'] = trim($matches[1]);
            }
            
            if (preg_match('/Cliente:\s*(.+?)$/m', $notas, $matches)) {
                $p['cliente_nombre'] = trim($matches[1]);
            }
            
            if (preg_match('/Categoría:\s*(.+?)$/m', $notas, $matches)) {
                $p['categoria_principal'] = trim($matches[1]);
            }
            
            if (preg_match('/Tiempo estimado:\s*(.+?)$/m', $notas, $matches)) {
                $p['tiempo_estimado'] = trim($matches[1]);
            }
            
            if (preg_match('/Presupuesto:\s*(.+?)$/m', $notas, $matches)) {
                $p['presupuesto_aproximado'] = trim($matches[1]);
            }
            
            if (preg_match('/Tecnologías:\s*(\[.+?\])/s', $notas, $matches)) {
                $p['tecnologias_seleccionadas'] = json_decode($matches[1], true) ?: [];
            } else {
                $p['tecnologias_seleccionadas'] = [];
            }
            
            if (preg_match('/Fecha inicio:\s*(.+?)$/m', $notas, $matches)) {
                $p['fecha_inicio'] = trim($matches[1]);
            }
            
            if (preg_match('/Plazo entrega:\s*(.+?)$/m', $notas, $matches)) {
                $p['plazo_entrega'] = trim($matches[1]);
            }
            
            if (preg_match('/Prioridad:\s*(.+?)$/m', $notas, $matches)) {
                $p['prioridad'] = trim($matches[1]);
            } else {
                $p['prioridad'] = 'media';
            }
            
            if (preg_match('/Notas adicionales:\s*(.+)/s', $notas, $matches)) {
                $p['notas_adicionales'] = trim($matches[1]);
            } else {
                $p['notas_adicionales'] = '';
            }
            
            $presupuestos_wizard[] = $p;
        }
    }
    
    // Devolver respuesta exitosa
    echo json_encode([
        'success' => true,
        'data' => $presupuestos_wizard
    ]);
    http_response_code(200);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
}
