<?php
/**
 * API para registro de nuevos usuarios.
 * 
 * Recibe datos de registro y crea un nuevo usuario en la base de datos.
 */

// Cargar dependencias
require_once __DIR__ . '/../modelos/ConexionBBDD.php';
require_once __DIR__ . '/../modelos/Cliente.php';
require_once __DIR__ . '/../controladores/ControladorDeAutenticacion.php';

// Configurar headers para CORS y JSON (IMPORTANTE: antes de cualquier output)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Manejar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit();
}

// Obtener datos del POST
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
        echo json_encode(['error' => "Campo requerido faltante: $field"]);
        exit();
    }
}

// Sanitizar y validar datos
$dni = trim($input['dni']);
$nombre = trim($input['nombre']);
$email = trim($input['email']);
$password = $input['password'];
$telefono = isset($input['telefono']) ? trim($input['telefono']) : null;

// Validar formato de email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Formato de email inválido']);
    exit();
}

// Validar longitud de contraseña
if (strlen($password) < 6) {
    http_response_code(400);
    echo json_encode(['error' => 'La contraseña debe tener al menos 6 caracteres']);
    exit();
}

// Inicializar controlador
$authController = new ControladorDeAutenticacion();

// Procesar registro
$result = $authController->procesarRegistro($dni, $nombre, $email, $password, $telefono);

if ($result['success']) {
    http_response_code(201);
    echo json_encode(['success' => true, 'message' => 'Usuario registrado exitosamente']);
} else {
    http_response_code(400);
    echo json_encode(['error' => $result['error']]);
}
?>