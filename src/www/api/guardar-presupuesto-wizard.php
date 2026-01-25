<?php
/**
 * API endpoint para crear presupuestos desde el wizard.
 * 
 * Recibe POST con los datos del formulario
 * Devuelve JSON con el resultado de la creación.
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
    
    // Solo permitir POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        exit();
    }

    // Obtener datos JSON del body
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data) {
        http_response_code(400);
        echo json_encode(['error' => 'Datos JSON inválidos']);
        exit();
    }

    // Validar campos obligatorios
    $camposRequeridos = [
        'usuario_dni',
        'nombreProyecto',
        'descripcionProyecto',
        'clienteNombre',
        'categoriaPrincipal',
        'tiempoEstimado',
        'presupuestoAproximado',
        'tecnologiasSeleccionadas',
        'fechaInicio',
        'plazoEntrega',
        'prioridad'
    ];

    foreach ($camposRequeridos as $campo) {
        if (!isset($data[$campo]) || (is_string($data[$campo]) && trim($data[$campo]) === '')) {
            http_response_code(400);
            echo json_encode(['error' => "Campo requerido: $campo"]);
            exit();
        }
    }

    // Obtener conexión a la base de datos
    $db = Conexion::obtener();
    
    // Crear instancia del modelo
    $presupuesto = new PresupuestoWizard($db);
    
    // Asignar valores
    $presupuesto->usuario_dni = filter_var($data['usuario_dni'], FILTER_SANITIZE_SPECIAL_CHARS);
    $presupuesto->nombre_proyecto = filter_var($data['nombreProyecto'], FILTER_SANITIZE_SPECIAL_CHARS);
    $presupuesto->descripcion_proyecto = filter_var($data['descripcionProyecto'], FILTER_SANITIZE_SPECIAL_CHARS);
    $presupuesto->cliente_nombre = filter_var($data['clienteNombre'], FILTER_SANITIZE_SPECIAL_CHARS);
    $presupuesto->cliente_email = isset($data['clienteEmail']) ? filter_var($data['clienteEmail'], FILTER_SANITIZE_EMAIL) : null;
    $presupuesto->categoria_principal = filter_var($data['categoriaPrincipal'], FILTER_SANITIZE_SPECIAL_CHARS);
    $presupuesto->tiempo_estimado = filter_var($data['tiempoEstimado'], FILTER_SANITIZE_SPECIAL_CHARS);
    $presupuesto->presupuesto_aproximado = filter_var($data['presupuestoAproximado'], FILTER_SANITIZE_SPECIAL_CHARS);
    $presupuesto->tecnologias_seleccionadas = $data['tecnologiasSeleccionadas']; // Ya es array
    $presupuesto->fecha_inicio = filter_var($data['fechaInicio'], FILTER_SANITIZE_SPECIAL_CHARS);
    $presupuesto->plazo_entrega = filter_var($data['plazoEntrega'], FILTER_SANITIZE_SPECIAL_CHARS);
    $presupuesto->prioridad = filter_var($data['prioridad'], FILTER_SANITIZE_SPECIAL_CHARS);
    $presupuesto->notas_adicionales = isset($data['notasAdicionales']) 
        ? filter_var($data['notasAdicionales'], FILTER_SANITIZE_SPECIAL_CHARS) 
        : null;
    $presupuesto->estado = 'borrador';
    
    // Crear presupuesto
    $id = $presupuesto->crear();
    
    if ($id) {
        echo json_encode([
            'success' => true,
            'message' => 'Presupuesto creado correctamente',
            'data' => [
                'id' => $id,
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
    error_log("Error en guardar-presupuesto-wizard.php: " . $e->getMessage());
    echo json_encode([
        'error' => 'Error interno del servidor: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
