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
        // Intentar enviar email de bienvenida (no bloquear si falla)
        try {
            require_once __DIR__ . '/../config/email.php';
            
            $asunto = "Bienvenido a Logisteia";
            $mensajeHTML = "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Bienvenido a Logisteia</title>
</head>
<body style='margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;'>
    <table width='100%' cellpadding='0' cellspacing='0' style='background-color: #f4f4f4; padding: 20px;'>
        <tr>
            <td align='center'>
                <table width='600' cellpadding='0' cellspacing='0' style='background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);'>
                    <!-- Header -->
                    <tr>
                        <td style='background: linear-gradient(135deg, #102a41 0%, #1a3f5e 100%); padding: 40px; text-align: center;'>
                            <h1 style='color: #ffffff; margin: 0; font-size: 32px; letter-spacing: 2px;'>LOGISTEIA</h1>
                            <p style='color: #ffffff; margin: 10px 0 0 0; font-size: 14px; opacity: 0.9; font-style: italic;'>Planifica con precisión. Ejecuta con control.</p>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style='padding: 40px;'>
                            <h2 style='color: #102a41; margin: 0 0 20px 0; font-size: 24px;'>¡Bienvenido, $nombre!</h2>
                            
                            <p style='color: #333; line-height: 1.6; margin: 0 0 15px 0;'>
                                Tu cuenta ha sido creada exitosamente. Estamos encantados de tenerte con nosotros.
                            </p>
                            
                            <div style='background: #f8f9fa; border-left: 4px solid #102a41; padding: 20px; margin: 25px 0;'>
                                <h3 style='color: #102a41; margin: 0 0 15px 0; font-size: 16px; text-transform: uppercase; letter-spacing: 0.5px;'>Datos de tu cuenta</h3>
                                <table width='100%' cellpadding='8' cellspacing='0'>
                                    <tr>
                                        <td style='color: #666; font-size: 14px; padding: 8px 0;'><strong>Email:</strong></td>
                                        <td style='color: #333; font-size: 14px; padding: 8px 0;'>$email</td>
                                    </tr>
                                    <tr>
                                        <td style='color: #666; font-size: 14px; padding: 8px 0;'><strong>Rol:</strong></td>
                                        <td style='color: #333; font-size: 14px; padding: 8px 0;'>$rol</td>
                                    </tr>
                                </table>
                            </div>
                            
                            <p style='color: #333; line-height: 1.6; margin: 20px 0;'>
                                Ya puedes iniciar sesión en la plataforma y comenzar a utilizar nuestros servicios de gestión de proyectos y presupuestos.
                            </p>
                            
                            <div style='text-align: center; margin: 30px 0;'>
                                <a href='https://logisteia.com/login' style='display: inline-block; background: #102a41; color: #ffffff; text-decoration: none; padding: 12px 30px; border-radius: 6px; font-weight: 600; font-size: 16px;'>
                                    Iniciar Sesión
                                </a>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style='background: #f8f9fa; padding: 25px; text-align: center; border-top: 1px solid #dee2e6;'>
                            <p style='color: #666; margin: 0; font-size: 14px;'>
                                <strong>Logisteia</strong> - Gestión de Proyectos y Presupuestos
                            </p>
                            <p style='color: #999; margin: 10px 0 0 0; font-size: 12px;'>
                                Este es un mensaje automático, por favor no respondas a este correo.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>";
            
            enviarEmail($email, $nombre, $asunto, $mensajeHTML, 'logisteiaa@gmail.com', 'Equipo Logisteia');
        } catch (Exception $emailError) {
            // Log del error pero no detener el proceso
            logError('Error al enviar email de bienvenida', $emailError);
        }
        
        sendJsonSuccess([
            'message' => 'Usuario registrado exitosamente.',
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