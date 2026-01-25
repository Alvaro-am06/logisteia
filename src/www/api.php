<?php
/**
 * API REST para conectar Angular con PHP y MySQL
 * Maneja todas las peticiones desde el frontend
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'controladores/UsuarioControlador.php';
require_once 'controladores/ControladorCliente.php';
require_once 'controladores/ControladorDeAutenticacion.php';

// Obtener la ruta de la petición
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

// Remover el prefijo /api si existe
$path = str_replace('/api', '', $path);
$path_parts = explode('/', trim($path, '/'));

// Obtener el método HTTP
$method = $_SERVER['REQUEST_METHOD'];

// Obtener datos del body para POST/PUT
$input = json_decode(file_get_contents('php://input'), true);

try {
    switch ($path_parts[0]) {
        case 'usuarios':
            $controller = new UsuarioControlador();

            if ($method === 'GET') {
                if (isset($path_parts[1])) {
                    // Obtener usuario específico
                    $usuario = $controller->obtenerUsuario($path_parts[1]);
                    echo json_encode($usuario);
                } else {
                    // Listar todos los usuarios
                    $usuarios = $controller->listarUsuarios();
                    echo json_encode($usuarios);
                }
            } elseif ($method === 'POST' && isset($path_parts[1])) {
                // Cambiar rol de usuario
                $dni = $path_parts[1];
                $operacion = $input['operacion'] ?? '';
                $motivo = $input['motivo'] ?? null;

                $resultado = $controller->cambiarRol($dni, $operacion, $motivo);
                echo json_encode(['success' => $resultado]);
            }
            break;

        case 'clientes':
            $controller = new ControladorCliente();

            if ($method === 'GET') {
                if (isset($path_parts[1])) {
                    // Obtener cliente específico
                    $cliente = $controller->obtenerCliente($path_parts[1]);
                    echo json_encode($cliente);
                } else {
                    // Listar todos los clientes
                    $clientes = $controller->listarClientes();
                    echo json_encode($clientes);
                }
            }
            break;

        case 'historial':
            require_once 'modelos/AccionesAdministrativas.php';
            $modelAcciones = new AccionesAdministrativas();

            if ($method === 'GET') {
                // Obtener todo el historial administrativo
                $historial = $modelAcciones->obtenerTodos();
                echo json_encode([
                    'success' => true,
                    'data' => $historial
                ]);
            }
            break;

        case 'auth':
            $controller = new ControladordeAutenticacion();

            if ($method === 'POST' && $path_parts[1] === 'login') {
                $email = $input['email'] ?? '';
                $password = $input['password'] ?? '';

                $resultado = $controller->procesarLoginAPI($email, $password);
                echo json_encode($resultado);
            } elseif ($method === 'POST' && $path_parts[1] === 'logout') {
                $controller->logout();
                echo json_encode(['success' => true]);
            }
            break;

        default:
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint no encontrado']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>