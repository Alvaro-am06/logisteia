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
        $mensajeHTML = "<html><body>
            <h2>¡Bienvenido a Logisteia, $nombre!</h2>
            <p>Tu cuenta ha sido creada exitosamente mediante Google.</p>
            <p><strong>Datos de acceso:</strong></p>
            <ul>
                <li>Email: $email</li>
                <li>Rol: $rol</li>
                <li>DNI: $dni</li>
            </ul>
            <p>Ya puedes iniciar sesión en la plataforma y comenzar a utilizar nuestros servicios.</p>
            <br>
            <p>Saludos,<br>Equipo Logisteia</p>
        </body></html>";
        
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
