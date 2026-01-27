<?php
/**
 * API REST para gestión de clientes.
 * Endpoints:
 * - GET: Listar todos los clientes del jefe autenticado
 * - POST: Crear un nuevo cliente
 * - PUT: Actualizar cliente existente
 * - DELETE: Eliminar cliente
 */

// Cargar configuración centralizada
require_once __DIR__ . '/../config/config.php';

// Configurar CORS y headers
setupCors();
header('Content-Type: application/json; charset=UTF-8');
handlePreflight();

// Cargar dependencias
require_once __DIR__ . '/../modelos/ConexionBBDD.php';
require_once __DIR__ . '/../modelos/Cliente.php';
require_once __DIR__ . '/../modelos/AccionesAdministrativas.php';

// Inicializar conexión
try {
    $db = ConexionBBDD::obtener();
    $cliente = new Cliente($db);
} catch (Exception $e) {
    logError('Error de conexión en clientes.php', $e);
    sendJsonError('Error de conexión a la base de datos', 500);
}

// Procesar según el método HTTP
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Obtener jefe_dni de los headers
        // Los headers HTTP llegan como HTTP_X_USER_DNI (los guiones se convierten en guiones bajos)
        $jefe_dni = isset($_SERVER['HTTP_X_USER_DNI']) ? $_SERVER['HTTP_X_USER_DNI'] : null;
        
        // Si no está, intentar con variante en minúsculas (por si el servidor maneja diferente)
        if (!$jefe_dni && isset($_SERVER['HTTP_X_USER_DII'])) {
            $jefe_dni = $_SERVER['HTTP_X_USER_DII'];
        }
        
        if (!$jefe_dni) {
            ob_end_clean();
            http_response_code(401);
            echo json_encode(['error' => 'Usuario no autenticado. Se requiere X-User-DNI header']);
            exit();
        }

        // Listar clientes del jefe autenticado
        $stmt = $cliente->obtenerPorJefe($jefe_dni);
        if ($stmt) {
            $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode([
                'success' => true,
                'clientes' => $clientes
            ]);
        } else {
            ob_end_clean();
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener clientes']);
        }
        break;

    case 'POST':
        try {
            // Crear nuevo cliente
            $rawInput = file_get_contents('php://input');
            
            $input = json_decode($rawInput, true);
            
            if (!$input) {
                ob_end_clean();
                http_response_code(400);
                echo json_encode(['error' => 'Datos inválidos']);
                exit();
            }

            // Obtener jefe_dni de los headers (enviado por Angular)
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
            $required_fields = ['nombre', 'email'];
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
            $email = trim($input['email']);
            $empresa = isset($input['empresa']) ? trim($input['empresa']) : null;
            $telefono = isset($input['telefono']) ? trim($input['telefono']) : null;
            $direccion = isset($input['direccion']) ? trim($input['direccion']) : null;
            $cif_nif = isset($input['cif_nif']) ? trim($input['cif_nif']) : null;
            $notas = isset($input['notas']) ? trim($input['notas']) : null;

            // Validar formato email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                ob_end_clean();
                http_response_code(400);
                echo json_encode(['error' => 'Formato de email inválido']);
                exit();
            }

            // Verificar si ya existe un cliente con este email
            if ($cliente->obtenerPorEmail($email)) {
                ob_end_clean();
                http_response_code(409);
                echo json_encode(['error' => 'Ya existe un cliente con este email']);
                exit();
            }

            // Asignar datos al modelo
            $cliente->jefe_dni = $jefe_dni;
            $cliente->nombre = $nombre;
            $cliente->email = $email;
            $cliente->empresa = $empresa;
            $cliente->telefono = $telefono;
            $cliente->direccion = $direccion;
            $cliente->cif_nif = $cif_nif;
            $cliente->notas = $notas;

            // Crear cliente
            if ($cliente->crear()) {
                
                // Registrar acción administrativa si hay sesión
                session_start();
                if (isset($_SESSION['usuario_dni'])) {
                    try {
                        $accionAdmin = new AccionesAdministrativas();
                        $accionAdmin->registrar(
                            $_SESSION['usuario_dni'],
                            'Creación de cliente',
                            $cliente->id,
                            "Cliente creado: $nombre ($email)"
                        );
                    } catch (Exception $e) {
                        // No fallar si no se puede registrar la acción
                    }
                }

                ob_end_flush();
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'Cliente creado exitosamente',
                    'cliente' => [
                        'id' => $cliente->id,
                        'nombre' => $nombre,
                        'email' => $email,
                        'empresa' => $empresa,
                        'telefono' => $telefono
                    ]
                ]);
            } else {
                ob_end_clean();
                http_response_code(500);
                echo json_encode(['error' => 'Error al crear el cliente']);
            }
        } catch (Exception $e) {
            ob_end_clean();
            error_log("❌ POST clientes.php EXCEPTION: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'error' => 'Error al crear cliente',
                'details' => $e->getMessage()
            ]);
        }
        break;

    case 'PUT':
        // Actualizar cliente existente
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input || !isset($input['id'])) {
            sendJsonError('ID de cliente requerido', 400);
        }

        $clienteId = $input['id'];

        // Verificar que el cliente existe
        try {
            $query = "SELECT * FROM clientes WHERE id = :id AND activo = 1";
            $stmt = $db->prepare($query);
            $stmt->execute([':id' => $clienteId]);
            
            if ($stmt->rowCount() === 0) {
                sendJsonError('Cliente no encontrado', 404);
            }

            $clienteData = $stmt->fetch(PDO::FETCH_ASSOC);
            $cliente->id = $clienteData['id'];

            // Actualizar campos
            if (isset($input['nombre'])) {
                $cliente->nombre = trim($input['nombre']);
            }
            
            if (isset($input['email'])) {
                $email = validateEmail($input['email']);
                if (!$email) {
                    sendJsonError('Formato de email inválido', 400);
                }
                $cliente->email = $email;
            }
            
            if (isset($input['empresa'])) $cliente->empresa = sanitizeInput($input['empresa']);
            if (isset($input['telefono'])) $cliente->telefono = sanitizeInput($input['telefono']);
            if (isset($input['direccion'])) $cliente->direccion = sanitizeInput($input['direccion']);
            if (isset($input['cif_nif'])) $cliente->cif_nif = sanitizeInput($input['cif_nif']);
            if (isset($input['notas'])) $cliente->notas = sanitizeInput($input['notas']);

            if ($cliente->actualizar()) {
                // Registrar acción administrativa
                session_start();
                if (isset($_SESSION['usuario_dni'])) {
                    try {
                        $accionAdmin = new AccionesAdministrativas();
                        $accionAdmin->registrar(
                            $_SESSION['usuario_dni'],
                        'Actualización de cliente',
                        $cliente->id,
                        "Cliente actualizado: {$cliente->nombre}"
                    );
                } catch (Exception $e) {
                        logError('Error al registrar acción administrativa', $e);
                    }
                }

                sendJsonSuccess([
                    'message' => 'Cliente actualizado exitosamente',
                    'cliente' => $clienteData
                ]);
            } else {
                sendJsonError('Error al actualizar el cliente', 500);
            }
        } catch (Exception $e) {
            logError('Error en PUT clientes.php', $e);
            sendJsonError('Error al actualizar cliente', 500);
        }
        break;
    case 'DELETE':
        // Eliminar cliente - cif_nif viene en los parámetros de query
        $cif_nif = isset($_GET['cif_nif']) ? $_GET['cif_nif'] : null;
        
        if (!$cif_nif) {
            ob_end_clean();
            http_response_code(400);
            echo json_encode(['error' => 'CIF/NIF requerido']);
            exit();
        }

        // Verificar que el cliente existe - buscar por cif_nif
        $db = ConexionBBDD::obtener();
        $query = "SELECT * FROM clientes WHERE cif_nif = :cif_nif AND activo = 1";
        $stmt = $db->prepare($query);
        $stmt->execute([':cif_nif' => $cif_nif]);
        
        if ($stmt->rowCount() === 0) {
            ob_end_clean();
            http_response_code(404);
            echo json_encode(['error' => 'Cliente no encontrado']);
            exit();
        }

        $clienteData = $stmt->fetch(PDO::FETCH_ASSOC);
        $nombreCliente = $clienteData['nombre'];
        $idCliente = $clienteData['id'];

        // Eliminar cliente
        $deleteQuery = "DELETE FROM clientes WHERE cif_nif = :cif_nif";
        $deleteStmt = $db->prepare($deleteQuery);
        
        if ($deleteStmt->execute([':cif_nif' => $cif_nif])) {
            
            ob_end_clean();
            echo json_encode([
                'success' => true,
                'message' => 'Cliente eliminado exitosamente'
            ]);
        } else {
            ob_end_clean();
            http_response_code(500);
            echo json_encode(['error' => 'Error al eliminar el cliente']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        break;
}

// Limpiar buffer y enviar respuesta
ob_end_flush();
?>
