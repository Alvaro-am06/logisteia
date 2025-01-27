<?php
/**
 * API endpoint para actualizar el perfil de usuario.
 * 
 * Recibe PUT con JSON: {"dni": "...", "nombre": "...", "email": "...", "telefono": "...", "password": "..."}
 * Devuelve JSON con success o error.
 */

// Cargar configuración centralizada
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/jwt.php';

// Configurar CORS y headers
setupCors();
header('Content-Type: application/json');
handlePreflight();

try {
    require_once __DIR__ . '/../modelos/ConexionBBDD.php';
    
    // Verificar que sea una petición PUT
    if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
        sendJsonError('Método no permitido', 405);
        exit();
    }

    // Obtener los datos JSON del cuerpo de la petición
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validar que se recibieron datos válidos
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['error' => 'JSON inválido']);
        http_response_code(400);
        exit();
    }

    // Extraer datos
    $dni = $input['dni'] ?? '';
    $nombre = $input['nombre'] ?? '';
    $email = $input['email'] ?? '';
    $telefono = $input['telefono'] ?? null;
    $password = $input['password'] ?? null;

    // Validar campos obligatorios
    if (empty($dni) || empty($nombre) || empty($email)) {
        echo json_encode(['error' => 'DNI, nombre y email son obligatorios']);
        http_response_code(400);
        exit();
    }

    // Validar formato de email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['error' => 'Formato de email inválido']);
        http_response_code(400);
        exit();
    }

    // Obtener conexión a la base de datos
    $database = new Conexion();
    $db = $database->obtener();

    // Verificar que el usuario existe
    $queryVerificar = "SELECT dni FROM usuarios WHERE dni = :dni LIMIT 1";
    $stmtVerificar = $db->prepare($queryVerificar);
    $stmtVerificar->execute([':dni' => $dni]);
    
    if ($stmtVerificar->rowCount() === 0) {
        echo json_encode(['error' => 'Usuario no encontrado']);
        http_response_code(404);
        exit();
    }

    // Verificar que el email no esté en uso por otro usuario
    $queryEmail = "SELECT dni FROM usuarios WHERE email = :email AND dni != :dni LIMIT 1";
    $stmtEmail = $db->prepare($queryEmail);
    $stmtEmail->execute([':email' => $email, ':dni' => $dni]);
    
    if ($stmtEmail->rowCount() > 0) {
        echo json_encode(['error' => 'El email ya está en uso por otro usuario']);
        http_response_code(409);
        exit();
    }

    // Construir la consulta de actualización
    if (!empty($password)) {
        // Si se proporciona contraseña, actualizarla también
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $query = "UPDATE usuarios 
                  SET nombre = :nombre, 
                      email = :email, 
                      telefono = :telefono, 
                      password = :password 
                  WHERE dni = :dni";
        
        $params = [
            ':dni' => $dni,
            ':nombre' => $nombre,
            ':email' => $email,
            ':telefono' => $telefono,
            ':password' => $hashedPassword
        ];
    } else {
        // Actualizar sin cambiar la contraseña
        $query = "UPDATE usuarios 
                  SET nombre = :nombre, 
                      email = :email, 
                      telefono = :telefono 
                  WHERE dni = :dni";
        
        $params = [
            ':dni' => $dni,
            ':nombre' => $nombre,
            ':email' => $email,
            ':telefono' => $telefono
        ];
    }

    $stmt = $db->prepare($query);
    
    if ($stmt->execute($params)) {
        // Obtener datos actualizados del usuario
        $queryUsuario = "SELECT dni, nombre, email, telefono, rol, estado, fecha_registro 
                         FROM usuarios 
                         WHERE dni = :dni 
                         LIMIT 1";
        $stmtUsuario = $db->prepare($queryUsuario);
        $stmtUsuario->execute([':dni' => $dni]);
        $usuarioActualizado = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'message' => 'Perfil actualizado correctamente',
            'data' => $usuarioActualizado
        ]);
        http_response_code(200);
    } else {
        echo json_encode(['error' => 'Error al actualizar el perfil']);
        http_response_code(500);
    }

} catch (Exception $e) {
    echo json_encode(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
    http_response_code(500);
}
