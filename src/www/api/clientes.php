<?php
/**
 * API REST para gestión de clientes.
 * Endpoints:
 * - GET: Listar todos los clientes
 * - POST: Crear un nuevo cliente
 * - PUT: Actualizar cliente existente
 * - DELETE: Eliminar cliente
 */

// Cargar dependencias
require_once __DIR__ . '/../modelos/ConexionBBDD.php';
require_once __DIR__ . '/../modelos/Cliente.php';
require_once __DIR__ . '/../modelos/AccionesAdministrativas.php';

// Configurar headers CORS y JSON
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=UTF-8');

// Manejar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

// Inicializar conexión
try {
    $database = new Conexion();
    $db = $database->obtener();
    $cliente = new Cliente($db);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit();
}

// Procesar según el método HTTP
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Listar clientes o obtener uno específico
        if (isset($_GET['dni'])) {
            $dni = $_GET['dni'];
            if ($cliente->obtenerPorDni($dni)) {
                echo json_encode([
                    'success' => true,
                    'cliente' => [
                        'dni' => $cliente->dni,
                        'nombre' => $cliente->nombre,
                        'email' => $cliente->email,
                        'telefono' => $cliente->telefono,
                        'fecha_registro' => $cliente->fecha_registro
                    ]
                ]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Cliente no encontrado']);
            }
        } else {
            // Listar todos los clientes
            $stmt = $cliente->obtenerTodos();
            $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode([
                'success' => true,
                'clientes' => $clientes
            ]);
        }
        break;

    case 'POST':
        // Crear nuevo cliente
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            http_response_code(400);
            echo json_encode(['error' => 'Datos inválidos']);
            exit();
        }

        // Validar campos requeridos
        $required_fields = ['dni', 'nombre', 'email', 'password'];
        foreach ($required_fields as $field) {
            if (!isset($input[$field]) || empty(trim($input[$field]))) {
                http_response_code(400);
                echo json_encode(['error' => "Campo requerido: $field"]);
                exit();
            }
        }

        // Sanitizar datos
        $dni = trim($input['dni']);
        $nombre = trim($input['nombre']);
        $email = trim($input['email']);
        $password = $input['password'];
        $telefono = isset($input['telefono']) ? trim($input['telefono']) : '';

        // Validar formato DNI (8 números + letra)
        if (!preg_match('/^\d{8}[A-Z]$/', $dni)) {
            http_response_code(400);
            echo json_encode(['error' => 'Formato de DNI inválido. Debe ser 8 números seguidos de una letra mayúscula']);
            exit();
        }

        // Validar formato email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['error' => 'Formato de email inválido']);
            exit();
        }

        // Validar longitud contraseña
        if (strlen($password) < 6) {
            http_response_code(400);
            echo json_encode(['error' => 'La contraseña debe tener al menos 6 caracteres']);
            exit();
        }

        // Verificar si ya existe el DNI
        if ($cliente->obtenerPorDni($dni)) {
            http_response_code(409);
            echo json_encode(['error' => 'Ya existe un cliente con este DNI']);
            exit();
        }

        // Verificar si ya existe el email
        if ($cliente->obtenerPorEmail($email)) {
            http_response_code(409);
            echo json_encode(['error' => 'Ya existe un cliente con este email']);
            exit();
        }

        // Hashear contraseña
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Asignar datos al modelo
        $cliente->dni = $dni;
        $cliente->nombre = $nombre;
        $cliente->email = $email;
        $cliente->contrase = $hashedPassword;
        $cliente->telefono = $telefono;

        // Crear cliente
        if ($cliente->crear()) {
            // Registrar acción administrativa si hay sesión
            session_start();
            if (isset($_SESSION['usuario_dni'])) {
                try {
                    $accionAdmin = new AccionesAdministrativas();
                    $accionAdmin->registrar(
                        $_SESSION['usuario_dni'],
                        'Registro de cliente',
                        $dni,
                        "Cliente registrado: $nombre"
                    );
                } catch (Exception $e) {
                    // No fallar si no se puede registrar la acción
                }
            }

            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Cliente registrado exitosamente',
                'cliente' => [
                    'dni' => $dni,
                    'nombre' => $nombre,
                    'email' => $email
                ]
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al crear el cliente']);
        }
        break;

    case 'PUT':
        // Actualizar cliente existente
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['dni'])) {
            http_response_code(400);
            echo json_encode(['error' => 'DNI requerido']);
            exit();
        }

        $dni = $input['dni'];
        
        // Verificar que el cliente existe
        if (!$cliente->obtenerPorDni($dni)) {
            http_response_code(404);
            echo json_encode(['error' => 'Cliente no encontrado']);
            exit();
        }

        // Actualizar campos
        if (isset($input['nombre'])) $cliente->nombre = trim($input['nombre']);
        if (isset($input['email'])) {
            $email = trim($input['email']);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(['error' => 'Formato de email inválido']);
                exit();
            }
            $cliente->email = $email;
        }
        if (isset($input['telefono'])) $cliente->telefono = trim($input['telefono']);
        if (isset($input['password']) && !empty($input['password'])) {
            if (strlen($input['password']) < 6) {
                http_response_code(400);
                echo json_encode(['error' => 'La contraseña debe tener al menos 6 caracteres']);
                exit();
            }
            $cliente->contrase = password_hash($input['password'], PASSWORD_DEFAULT);
        }

        if ($cliente->actualizar()) {
            // Registrar acción administrativa
            session_start();
            if (isset($_SESSION['usuario_dni'])) {
                try {
                    $accionAdmin = new AccionesAdministrativas();
                    $accionAdmin->registrar(
                        $_SESSION['usuario_dni'],
                        'Actualización de cliente',
                        $dni,
                        "Cliente actualizado: {$cliente->nombre}"
                    );
                } catch (Exception $e) {
                    // No fallar si no se puede registrar la acción
                }
            }

            echo json_encode([
                'success' => true,
                'message' => 'Cliente actualizado exitosamente'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al actualizar el cliente']);
        }
        break;

    case 'DELETE':
        // Eliminar cliente
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['dni'])) {
            http_response_code(400);
            echo json_encode(['error' => 'DNI requerido']);
            exit();
        }

        $dni = $input['dni'];
        
        // Verificar que el cliente existe
        if (!$cliente->obtenerPorDni($dni)) {
            http_response_code(404);
            echo json_encode(['error' => 'Cliente no encontrado']);
            exit();
        }

        $nombreCliente = $cliente->nombre;

        if ($cliente->eliminar($dni)) {
            // Registrar acción administrativa
            session_start();
            if (isset($_SESSION['usuario_dni'])) {
                try {
                    $accionAdmin = new AccionesAdministrativas();
                    $accionAdmin->registrar(
                        $_SESSION['usuario_dni'],
                        'Eliminación de cliente',
                        $dni,
                        "Cliente eliminado: $nombreCliente"
                    );
                } catch (Exception $e) {
                    // No fallar si no se puede registrar la acción
                }
            }

            echo json_encode([
                'success' => true,
                'message' => 'Cliente eliminado exitosamente'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al eliminar el cliente']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        break;
}
?>
