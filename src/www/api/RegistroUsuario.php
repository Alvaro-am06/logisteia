<?php
/**
 * API para registro de nuevos usuarios.
 * 
 * Recibe datos de registro y crea un nuevo usuario en la base de datos.
 */

// Cargar configuración centralizada
require_once __DIR__ . '/../config/config.php';

// Cargar dependencias
require_once __DIR__ . '/../modelos/ConexionBBDD.php';
require_once __DIR__ . '/../modelos/Cliente.php';
require_once __DIR__ . '/../controladores/ControladorDeAutenticacion.php';

// Configurar CORS y headers
setupCors();
header('Content-Type: application/json');
handlePreflight();

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
$dni = sanitizeInput($input['dni']);
$nombre = sanitizeInput($input['nombre']);
$email = validateEmail($input['email']);
$password = $input['password']; // No sanitizar contraseña, se hará hash
$telefono = isset($input['telefono']) ? sanitizeInput($input['telefono']) : null;
$rol = isset($input['rol']) ? sanitizeInput($input['rol']) : 'trabajador';

// Validar DNI (formato básico español: 8 dígitos + letra)
if (!preg_match('/^[0-9]{8}[A-Z]$/i', $dni)) {
    sendJsonError('Formato de DNI inválido. Debe ser 8 dígitos seguidos de una letra');
}

// Validar nombre (solo letras, espacios y algunos caracteres)
if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{2,100}$/', $nombre)) {
    sendJsonError('Nombre inválido. Solo se permiten letras y espacios (2-100 caracteres)');
}

// Validar email
if ($email === false) {
    sendJsonError('Formato de email inválido');
}

// Validar rol
if (!in_array($rol, ['trabajador', 'jefe_equipo'])) {
    sendJsonError('Rol inválido. Debe ser "trabajador" o "jefe_equipo"');
}

// Validar longitud y complejidad de contraseña
if (strlen($password) < 8) {
    sendJsonError('La contraseña debe tener al menos 8 caracteres');
}

// Validar complejidad de contraseña (al menos una letra y un número)
if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d).+$/', $password)) {
    sendJsonError('La contraseña debe contener al menos una letra y un número');
}

// Inicializar controlador
$authController = new ControladorDeAutenticacion();

// Procesar registro con rol
try {
    $result = $authController->procesarRegistro($dni, $nombre, $email, $password, $telefono, $rol);

    if ($result['success']) {
        // Enviar email de bienvenida
        require_once __DIR__ . '/../config/email.php';
        
        $asunto = "Bienvenido a Logisteia";
        $mensajeHTML = "<html><body>
            <h2>¡Bienvenido a Logisteia, $nombre!</h2>
            <p>Tu cuenta ha sido creada exitosamente.</p>
            <p><strong>Datos de acceso:</strong></p>
            <ul>
                <li>Email: $email</li>
                <li>Rol: $rol</li>
            </ul>
            <p>Ya puedes iniciar sesión en la plataforma y comenzar a utilizar nuestros servicios.</p>
            <br>
            <p>Saludos,<br>Equipo Logisteia</p>
        </body></html>";
        
        enviarEmail($email, $nombre, $asunto, $mensajeHTML, 'logisteiaa@gmail.com', 'Equipo Logisteia');
        
        sendJsonSuccess([
            'message' => 'Usuario registrado exitosamente. Se ha enviado un email de bienvenida.',
            'rol' => $rol
        ], 201);
    } else {
        sendJsonError($result['error']);
    }
} catch (Exception $e) {
    logError('Error en registro de usuario', $e);
    sendJsonError(
        'Error al procesar el registro',
        500,
        APP_ENV === 'development' ? $e->getMessage() : null
    );
}
?>