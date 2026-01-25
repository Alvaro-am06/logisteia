<?php
/**
 * API REST para gestión de proyectos.
 * Endpoints:
 * - GET: Listar todos los proyectos del jefe autenticado
 * - POST: Crear un nuevo proyecto
 * - PUT: Actualizar proyecto existente
 * - DELETE: Eliminar proyecto
 */

// Cargar configuración centralizada
require_once __DIR__ . '/../config/config.php';

// Configurar CORS y headers
setupCors();
header('Content-Type: application/json; charset=UTF-8');
handlePreflight();

// Cargar dependencias
require_once __DIR__ . '/../modelos/ConexionBBDD.php';
require_once __DIR__ . '/../modelos/Proyecto.php';
require_once __DIR__ . '/../modelos/AccionesAdministrativas.php';

// Inicializar conexión
try {
    $db = ConexionBBDD::obtener();
    $proyecto = new Proyecto($db);
} catch (Exception $e) {
    logError('Error de conexión en proyectos.php', $e);
    sendJsonError('Error de conexión a la base de datos', 500);
}

// Procesar según el método HTTP
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Obtener jefe_dni de los headers
        $jefe_dni = isset($_SERVER['HTTP_X_USER_DNI']) ? $_SERVER['HTTP_X_USER_DNI'] : null;
        
        if (!$jefe_dni && isset($_SERVER['HTTP_X_USER_DII'])) {
            $jefe_dni = $_SERVER['HTTP_X_USER_DII'];
        }
        
        if (!$jefe_dni) {
            ob_end_clean();
            http_response_code(401);
            echo json_encode(['error' => 'Usuario no autenticado. Se requiere X-User-DNI header']);
            exit();
        }

        // Listar proyectos del jefe autenticado
        $proyectos = $proyecto->obtenerProyectosPorJefe($jefe_dni);
        if ($proyectos !== false) {
            echo json_encode([
                'success' => true,
                'proyectos' => $proyectos
            ]);
        } else {
            ob_end_clean();
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener proyectos']);
        }
        break;

    case 'POST':
        try {
            // Crear nuevo proyecto
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);
            
            if (!$input) {
                ob_end_clean();
                http_response_code(400);
                echo json_encode(['error' => 'Datos inválidos']);
                exit();
            }

            // Obtener jefe_dni de los headers
            $jefe_dni = isset($_SERVER['HTTP_X_USER_DNI']) ? $_SERVER['HTTP_X_USER_DNI'] : null;
            if (!$jefe_dni && isset($_SERVER['HTTP_X_USER_DII'])) {
                $jefe_dni = $_SERVER['HTTP_X_USER_DII'];
            }
            
            if (!$jefe_dni) {
                ob_end_clean();
                http_response_code(401);
                echo json_encode(['error' => 'Usuario no autenticado. Se requiere X-User-DNI header']);
                exit();
            }

            // Validar campos requeridos
            $required_fields = ['nombre', 'descripcion', 'cliente_id'];
            foreach ($required_fields as $field) {
                if (!isset($input[$field]) || empty(trim($input[$field]))) {
                    ob_end_clean();
                    http_response_code(400);
                    echo json_encode(['error' => "Campo requerido: $field"]);
                    exit();
                }
            }

            // Sanitizar datos
            $nombre = trim($input['nombre']);
            $descripcion = trim($input['descripcion']);
            $cliente_id = intval($input['cliente_id']);
            $tecnologias = isset($input['tecnologias']) ? $input['tecnologias'] : [];
            $fecha_inicio = isset($input['fecha_inicio']) ? trim($input['fecha_inicio']) : null;
            $notas = isset($input['notas']) ? trim($input['notas']) : null;

            // Preparar datos para el modelo
            $datos = [
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'cliente_id' => $cliente_id,
                'jefe_dni' => $jefe_dni,
                'tecnologias' => $tecnologias,
                'fecha_inicio' => $fecha_inicio,
                'notas' => $notas,
                'precio_total' => isset($input['precio_total']) ? floatval($input['precio_total']) : 0
            ];

            // Crear proyecto usando el método del modelo
            $resultado = $proyecto->crearProyecto($datos);

            if ($resultado && isset($resultado['proyecto_id'])) {
                // Registrar acción administrativa si hay sesión
                session_start();
                if (isset($_SESSION['usuario_dni'])) {
                    try {
                        $accionAdmin = new AccionesAdministrativas();
                        $accionAdmin->registrar(
                            $_SESSION['usuario_dni'],
                            'Creación de proyecto',
                            $resultado['proyecto_id'],
                            "Proyecto creado: $nombre (Cliente ID: $cliente_id)"
                        );
                    } catch (Exception $e) {
                        // No fallar si no se puede registrar la acción
                    }
                }

                ob_end_flush();
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'Proyecto creado exitosamente',
                    'proyecto' => [
                        'id' => $resultado['proyecto_id'],
                        'nombre' => $nombre,
                        'descripcion' => $descripcion,
                        'cliente_id' => $cliente_id
                    ]
                ]);
            } else {
                ob_end_clean();
                http_response_code(500);
                echo json_encode(['error' => 'Error al crear el proyecto']);
            }
        } catch (Exception $e) {
            ob_end_clean();
            error_log("❌ POST proyectos.php EXCEPTION: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'error' => 'Error al crear proyecto',
                'details' => $e->getMessage()
            ]);
        }
        break;

    case 'PUT':
        // Actualizar proyecto existente
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input || !isset($input['id'])) {
            sendJsonError('ID de proyecto requerido', 400);
        }

        $proyectoId = $input['id'];

        // Obtener jefe_dni de los headers
        $jefe_dni = isset($_SERVER['HTTP_X_USER_DNI']) ? $_SERVER['HTTP_X_USER_DNI'] : null;
        
        if (!$jefe_dni) {
            ob_end_clean();
            http_response_code(401);
            echo json_encode(['error' => 'Usuario no autenticado']);
            exit();
        }

        // Actualizar estado si se proporciona
        if (isset($input['estado'])) {
            $sql = "UPDATE proyectos SET estado = :estado, fecha_actualizacion = NOW() WHERE id = :id AND jefe_dni = :jefe_dni";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':estado' => $input['estado'],
                ':id' => $proyectoId,
                ':jefe_dni' => $jefe_dni
            ]);

            if ($stmt->rowCount() > 0) {
                // Si se finalizó el proyecto, obtener presupuesto_total para el dashboard
                $presupuesto_total = 0;
                if ($input['estado'] === 'finalizado') {
                    $sqlPresupuesto = "SELECT presupuesto_total FROM proyectos WHERE id = :id";
                    $stmtPresupuesto = $db->prepare($sqlPresupuesto);
                    $stmtPresupuesto->execute([':id' => $proyectoId]);
                    $proyecto = $stmtPresupuesto->fetch(PDO::FETCH_ASSOC);
                    $presupuesto_total = $proyecto['presupuesto_total'] ?? 0;
                }
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Proyecto actualizado correctamente',
                    'presupuesto_total' => $presupuesto_total
                ]);
            } else {
                ob_end_clean();
                http_response_code(404);
                echo json_encode(['error' => 'Proyecto no encontrado o no tienes permisos']);
            }
        } else {
            ob_end_clean();
            http_response_code(400);
            echo json_encode(['error' => 'No se proporcionaron campos para actualizar']);
        }
        break;

    case 'DELETE':
        // Eliminar proyecto
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input || !isset($input['id'])) {
            sendJsonError('ID de proyecto requerido', 400);
        }

        $proyectoId = $input['id'];

        // Obtener jefe_dni de los headers
        $jefe_dni = isset($_SERVER['HTTP_X_USER_DNI']) ? $_SERVER['HTTP_X_USER_DNI'] : null;
        
        if (!$jefe_dni) {
            ob_end_clean();
            http_response_code(401);
            echo json_encode(['error' => 'Usuario no autenticado']);
            exit();
        }

        try {
            // Eliminar proyecto (solo si es del jefe autenticado)
            $sql = "DELETE FROM proyectos WHERE id = :id AND jefe_dni = :jefe_dni";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':id' => $proyectoId,
                ':jefe_dni' => $jefe_dni
            ]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'Proyecto eliminado correctamente']);
            } else {
                ob_end_clean();
                http_response_code(404);
                echo json_encode(['error' => 'Proyecto no encontrado o no tienes permisos']);
            }
        } catch (Exception $e) {
            ob_end_clean();
            http_response_code(500);
            echo json_encode(['error' => 'Error al eliminar proyecto: ' . $e->getMessage()]);
        }
        break;

    default:
        sendJsonError('Método no permitido', 405);
}
