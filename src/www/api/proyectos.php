<?php
/**
 * API endpoint para operaciones con proyectos
 *
 * GET /api/proyectos - Obtener proyectos del usuario actual
 * POST /api/proyectos - Crear nuevo proyecto
 * GET /api/proyectos/{id}/trabajadores - Obtener trabajadores asignados
 * POST /api/proyectos/{id}/trabajadores - Asignar trabajadores
 * DELETE /api/proyectos/{id}/trabajadores/{dni} - Remover asignación
 * GET /api/proyectos/{id}/miembros-disponibles - Obtener miembros disponibles
 */

// Cargar configuración centralizada
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

try {
    // Configurar CORS y manejar preflight dentro del try-catch
    setupCors();
    handlePreflight();
    
    require_once __DIR__ . '/../modelos/ConexionBBDD.php';
    require_once __DIR__ . '/../modelos/Proyecto.php';
    require_once __DIR__ . '/../modelos/Usuarios.php';

    // Obtener conexión a la base de datos
    $conn = ConexionBBDD::obtener();

    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $request = $_SERVER['REQUEST_URI'] ?? '/';

    // Obtener el path relativo
    $path = parse_url($request, PHP_URL_PATH);
    // Quitar /api/proyectos.php o /api/proyectos del path
    $path = preg_replace('#^/api/proyectos(\.php)?#', '', $path);
    $path_parts = array_values(array_filter(explode('/', trim($path, '/')), fn($p) => $p !== ''));
    // Si no hay partes, asegurar que el array tenga al menos un elemento vacío
    if (empty($path_parts)) {
        $path_parts = [''];
    }

    $proyecto = new Proyecto($conn);
    $usuarios = new Usuarios($conn);

    // Verificar autenticación
    $usuario_actual = verificarAutenticacion();
    
    // DEBUG: Loguear autenticación
    error_log("API Proyectos - Usuario autenticado: " . ($usuario_actual ? json_encode($usuario_actual) : "NO"));
    
    // Si no hay autenticación, devolver error
    if (!$usuario_actual) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'No autenticado']);
        exit();
    }

    switch ($method) {
        case 'GET':
            if (empty($path_parts[0])) {
                // GET /api/proyectos - Obtener proyectos del usuario
                $rol = $usuario_actual['rol'];

                if ($rol === 'jefe_equipo') {
                    $proyectos = $proyecto->obtenerProyectosPorJefe($usuario_actual['dni']);
                } elseif ($rol === 'trabajador') {
                    $proyectos = $proyecto->obtenerProyectosPorTrabajador($usuario_actual['dni']);
                } else {
                    // Moderador o admin pueden ver todos
                    $proyectos = $proyecto->obtenerProyectosPorJefe($usuario_actual['dni']); // Temporal
                }

                echo json_encode(['success' => true, 'proyectos' => $proyectos]);

            } elseif ($path_parts[0] === 'miembros-disponibles' && isset($path_parts[1])) {
                // GET /api/proyectos/miembros-disponibles/{equipo_id}
                $equipo_id = $path_parts[1];
                $proyecto_id = $_GET['proyecto_id'] ?? null;

                $miembros = $proyecto->obtenerMiembrosEquipoDisponibles($equipo_id, $proyecto_id);
                echo json_encode(['success' => true, 'miembros' => $miembros]);

            } elseif (is_numeric($path_parts[0]) && isset($path_parts[1]) && $path_parts[1] === 'trabajadores') {
                // GET /api/proyectos/{id}/trabajadores
                $proyecto_id = $path_parts[0];
                $trabajadores = $proyecto->obtenerTrabajadoresProyecto($proyecto_id);
                echo json_encode(['success' => true, 'trabajadores' => $trabajadores]);
            }
            break;

        case 'POST':
            if (empty($path_parts[0])) {
                // POST /api/proyectos - Crear proyecto
                $input = json_decode(file_get_contents('php://input'), true);

                if (!$input) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Datos inválidos']);
                    exit();
                }

                // Generar código si no se proporciona
                if (!isset($input['codigo']) || empty($input['codigo'])) {
                    $input['codigo'] = $proyecto->generarCodigoProyecto();
                }

                // Agregar jefe_dni del usuario actual
                $input['jefe_dni'] = $usuario_actual['dni'];

                $resultado = $proyecto->crearProyecto($input);
                echo json_encode($resultado);

            } elseif (is_numeric($path_parts[0]) && isset($path_parts[1]) && $path_parts[1] === 'trabajadores') {
                // POST /api/proyectos/{id}/trabajadores - Asignar trabajadores
                $proyecto_id = $path_parts[0];
                $input = json_decode(file_get_contents('php://input'), true);

                if (!$input || !isset($input['trabajadores'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Datos inválidos']);
                    exit();
                }

                $proyecto->asignarTrabajadores($proyecto_id, $input['trabajadores']);
                echo json_encode(['success' => true, 'message' => 'Trabajadores asignados correctamente']);
            }
            break;

        case 'DELETE':
            if (is_numeric($path_parts[0]) && isset($path_parts[1]) && $path_parts[1] === 'trabajadores' && isset($path_parts[2])) {
                // DELETE /api/proyectos/{id}/trabajadores/{dni}
                $proyecto_id = $path_parts[0];
                $trabajador_dni = $path_parts[2];

                $resultado = $proyecto->removerAsignacion($proyecto_id, $trabajador_dni);
                if ($resultado) {
                    echo json_encode(['success' => true, 'message' => 'Asignación removida correctamente']);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Asignación no encontrada']);
                }
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
    }

} catch (Exception $e) {
    error_log("Error en API proyectos: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error interno del servidor',
        'message' => $e->getMessage()
    ]);
}
?>