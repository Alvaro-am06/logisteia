<?php
// Cargar configuración centralizada
require_once __DIR__ . '/../config/config.php';

// Configurar CORS y manejar preflight
setupCors();
handlePreflight();

header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../modelos/ConexionBBDD.php';
require_once __DIR__ . '/../config/jwt.php';

try {
    // Obtener datos JSON
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    // Validar datos requeridos
    if (!isset($data['email']) || !isset($data['nombre']) || !isset($data['dni']) || !isset($data['rol'])) {
        echo json_encode([
            'success' => false,
            'error' => 'Datos incompletos'
        ]);
        exit;
    }
    
    $email = trim($data['email']);
    $nombre = trim($data['nombre']);
    $dni = trim($data['dni']);
    $rol = trim($data['rol']);
    $telefono = isset($data['telefono']) ? trim($data['telefono']) : '';
    $password = isset($data['password']) && !empty($data['password']) ? trim($data['password']) : null;
    $picture = isset($data['picture']) ? trim($data['picture']) : '';
    
    // Validar rol
    if (!in_array($rol, ['trabajador', 'jefe_equipo'])) {
        echo json_encode([
            'success' => false,
            'error' => 'Rol no válido'
        ]);
        exit;
    }
    
    // Validar DNI (solo letras y números, 3-20 caracteres)
    if (!preg_match('/^[A-Za-z0-9]{3,20}$/', $dni)) {
        echo json_encode([
            'success' => false,
            'error' => 'DNI no válido. Debe contener solo letras y números (3-20 caracteres)'
        ]);
        exit;
    }
    
    // Conectar a la base de datos
    $db = ConexionBBDD::obtenerInstancia()->obtenerBBDD();
    
    // Verificar si el DNI ya existe
    $stmt = $db->prepare("SELECT COUNT(*) FROM usuarios WHERE dni = ?");
    $stmt->execute([$dni]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode([
            'success' => false,
            'error' => 'El DNI ya está registrado'
        ]);
        exit;
    }
    
    // Verificar si el email ya existe
    $stmt = $db->prepare("SELECT COUNT(*) FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode([
            'success' => false,
            'error' => 'El email ya está registrado'
        ]);
        exit;
    }
    
    // Preparar contraseña
    if ($password !== null) {
        // Encriptar contraseña si se proporcionó
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    } else {
        // Marcar como usuario de solo Google
        $passwordHash = 'google_auth';
    }
    
    // Crear el nuevo usuario
    $stmt = $db->prepare("
        INSERT INTO usuarios (dni, nombre, email, telefono, contrase, rol, estado, fecha_registro) 
        VALUES (?, ?, ?, ?, ?, ?, 'activo', NOW())
    ");
    
    $stmt->execute([$dni, $nombre, $email, $telefono, $passwordHash, $rol]);
    
    // Si es jefe de equipo, crear su equipo automáticamente
    if ($rol === 'jefe_equipo') {
        // Permitir que el usuario elija el nombre del equipo (si se proporciona)
        $nombreEquipo = isset($data['nombre_equipo']) && !empty(trim($data['nombre_equipo'])) 
            ? trim($data['nombre_equipo']) 
            : "Equipo de $nombre";
        
        $stmtEquipo = $db->prepare("
            INSERT INTO equipos (nombre, descripcion, jefe_dni, activo) 
            VALUES (?, ?, ?, 1)
        ");
        $stmtEquipo->execute([
            $nombreEquipo,
            "Equipo gestionado por $nombre",
            $dni
        ]);
    }
    
    // Enviar email de bienvenida
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
                                Tu cuenta ha sido creada exitosamente mediante Google. Estamos encantados de tenerte con nosotros.
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
                                    <tr>
                                        <td style='color: #666; font-size: 14px; padding: 8px 0;'><strong>DNI:</strong></td>
                                        <td style='color: #333; font-size: 14px; padding: 8px 0;'>$dni</td>
                                    </tr>
                                </table>
                            </div>
                            
                            <p style='color: #333; line-height: 1.6; margin: 20px 0;'>
                                Ya puedes iniciar sesión en la plataforma con tu cuenta de Google y comenzar a utilizar nuestros servicios de gestión de proyectos y presupuestos.
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
    } catch (Exception $e) {
        // Continuar aunque falle el envío del email
        error_log('Error al enviar email de bienvenida: ' . $e->getMessage());
    }
    
    // Registrar acción administrativa
    try {
        $stmtAccion = $db->prepare("
            INSERT INTO acciones_administrativas (tipo_accion, usuario_afectado_dni, descripcion, fecha_accion)
            VALUES ('creacion_cuenta_google', ?, ?, NOW())
        ");
        $descripcion = "Usuario registrado mediante Google: $nombre ($email) - Rol: $rol";
        $stmtAccion->execute([$dni, $descripcion]);
    } catch (Exception $e) {
        // Continuar aunque falle el registro de la acción
    }
    
    // Generar token JWT
    $token = generarTokenJWT([
        'dni' => $dni,
        'rol' => $rol,
        'nombre' => $nombre,
        'email' => $email
    ]);
    
    // Obtener datos adicionales según el rol (para consistencia con login normal)
    $datosAdicionales = [];
    
    if ($rol === 'jefe_equipo') {
        // Obtener el equipo recién creado
        $stmtEquipo = $db->prepare("SELECT id, nombre FROM equipos WHERE jefe_dni = ? LIMIT 1");
        $stmtEquipo->execute([$dni]);
        $equipo = $stmtEquipo->fetch(PDO::FETCH_ASSOC);
        
        if ($equipo) {
            $datosAdicionales['equipo_id'] = $equipo['id'];
            $datosAdicionales['equipo_nombre'] = $equipo['nombre'];
            $datosAdicionales['miembros_count'] = 0;
        } else {
            $datosAdicionales['equipo_nombre'] = 'Sin equipo';
            $datosAdicionales['miembros_count'] = 0;
        }
    }
    
    // Retornar datos del nuevo usuario
    $responseData = array_merge([
        'dni' => $dni,
        'nombre' => $nombre,
        'email' => $email,
        'telefono' => $telefono,
        'rol' => $rol,
        'estado' => 'activo',
        'fecha_registro' => date('Y-m-d H:i:s'),
        'token' => $token,
        'nuevo_usuario' => true
    ], $datosAdicionales);
    
    echo json_encode([
        'success' => true,
        'data' => $responseData
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
?>
