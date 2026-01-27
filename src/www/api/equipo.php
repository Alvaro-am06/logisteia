<?php
/**
 * API REST para gestión de equipos
 */

// Cargar configuración centralizada
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/jwt.php';

// Configurar CORS y headers
setupCors();
header('Content-Type: application/json');
handlePreflight();

require_once __DIR__ . '/../modelos/ConexionBBDD.php';

// Función para obtener conexión a la base de datos
function getConexion() {
    try {
        return ConexionBBDD::obtener();
    } catch(Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error de conexión a la base de datos']);
        exit;
    }
}

// Función para enviar email de bienvenida a equipo con enlace de confirmación
function enviarEmailBienvenida($emailDestinatario, $nombreDestinatario, $nombreEquipo, $jefeNombre, $jefeEmail, $token_invitacion) {
    try {
        require_once __DIR__ . '/../config/email.php';

        $asunto = "Invitación al equipo $nombreEquipo - Logisteia";
        $enlaceConfirmacion = "https://logisteia.com/api/confirmar-invitacion.php?token=$token_invitacion";

        $mensaje = "<html><body>
            <h2>¡Hola $nombreDestinatario!</h2>
            <p>Has sido invitado al equipo <strong>$nombreEquipo</strong> en la plataforma Logisteia.</p>
            <p><strong>Invitado por:</strong> $jefeNombre ($jefeEmail)</p>
            <p>Para aceptar la invitación y comenzar a colaborar en proyectos, haz clic en el siguiente enlace:</p>
            <p style='text-align: center; margin: 30px 0;'>
                <a href='$enlaceConfirmacion' style='background-color: #102a41; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;'>
                    Aceptar Invitación
                </a>
            </p>
            <p style='font-size: 12px; color: #666;'>O copia y pega este enlace en tu navegador:<br>
            <a href='$enlaceConfirmacion'>$enlaceConfirmacion</a></p>
            <br>
            <p>Saludos,<br>Equipo Logisteia</p>
        </body></html>";

        return enviarEmail($emailDestinatario, $nombreDestinatario, $asunto, $mensaje, 'logisteiaa@gmail.com', 'Equipo Logisteia');
    } catch (Exception $e) {
        error_log("❌ ERROR en enviarEmailBienvenida: " . $e->getMessage());
        return false;
    }
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Obtener miembros del equipo
        $usuario = verificarAutenticacion();

        if (!$usuario || $usuario['rol'] !== 'jefe_equipo') {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Solo los jefes de equipo pueden acceder a esta información']);
            exit;
        }

        try {
            $conn = getConexion();

            // Obtener información del equipo del jefe
            $stmt = $conn->prepare("
                SELECT e.id, e.nombre
                FROM equipos e
                INNER JOIN usuarios u ON e.jefe_dni = u.dni
                WHERE u.dni = ?
            ");
            $stmt->execute([$usuario['dni']]);
            $equipo = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$equipo) {
                echo json_encode(['success' => false, 'error' => 'No se encontró el equipo del jefe']);
                exit;
            }

            // Obtener miembros del equipo
            $stmt = $conn->prepare("
                SELECT
                    u.dni,
                    u.nombre,
                    u.email,
                    u.telefono,
                    em.rol_proyecto,
                    em.fecha_ingreso,
                    em.estado_invitacion,
                    u.estado as estado_usuario
                FROM miembros_equipo em
                INNER JOIN usuarios u ON em.trabajador_dni = u.dni
                WHERE em.equipo_id = ?
                ORDER BY em.fecha_ingreso DESC
            ");
            $stmt->execute([$equipo['id']]);
            $miembros = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'data' => [
                    'equipo' => $equipo,
                    'miembros' => $miembros
                ]
            ]);

        } catch(PDOException $e) {
            handleDatabaseError('Error al obtener los miembros del equipo', $e);
        }
        break;

    case 'POST':
        // Agregar miembro al equipo
        $usuario = verificarAutenticacion();

        if (!$usuario) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Usuario no autenticado']);
            exit;
        }
        

        if ($usuario['rol'] !== 'jefe_equipo') {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Solo los jefes de equipo pueden agregar miembros. Rol actual: ' . $usuario['rol']]);
            exit;
        }

        $rawInput = file_get_contents('php://input');
        $input = json_decode($rawInput, true);

        if (!isset($input['email_trabajador'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'El correo electrónico del trabajador es requerido']);
            exit;
        }

        $email_trabajador = trim($input['email_trabajador']);

        if (empty($email_trabajador)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'El correo electrónico del trabajador no puede estar vacío']);
            exit;
        }

        // Validar formato de email
        if (!filter_var($email_trabajador, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'El correo electrónico no tiene un formato válido']);
            exit;
        }

        try {
            $conn = getConexion();

            // Verificar que el jefe tenga un equipo
            $stmt = $conn->prepare("
                SELECT e.id, e.nombre
                FROM equipos e
                WHERE e.jefe_dni = ?
            ");
            $stmt->execute([$usuario['dni']]);
            $equipo = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$equipo) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'El jefe de equipo no tiene un equipo asignado']);
                exit;
            }

            // Verificar que el trabajador existe y es un trabajador registrado
            $stmt = $conn->prepare("
                SELECT u.dni, u.nombre, u.email, u.estado
                FROM usuarios u
                WHERE u.email = ?
            ");
            $stmt->execute([$email_trabajador]);
            $trabajador = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$trabajador) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Usuario no encontrado']);
                exit;
            }

            if ($trabajador['estado'] !== 'activo') {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'El usuario no está activo']);
                exit;
            }

            // Verificar si el trabajador ya está en el equipo
            $stmt = $conn->prepare("
                SELECT id, estado_invitacion FROM miembros_equipo
                WHERE equipo_id = ? AND trabajador_dni = ?
            ");
            $stmt->execute([$equipo['id'], $trabajador['dni']]);
            $existe = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existe) {
                if ($existe['estado_invitacion'] === 'aceptada') {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'El trabajador ya es miembro activo del equipo']);
                    exit;
                } else if ($existe['estado_invitacion'] === 'pendiente') {
                    // Permitir reenviar invitación
                } else {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'El trabajador ya tiene una invitación ' . $existe['estado_invitacion']]);
                    exit;
                }
            }

            // Asignar rol por defecto
            $rol_proyecto = 'Miembro del Equipo';

            // Generar token único para la invitación
            $token_invitacion = bin2hex(random_bytes(32));
            
            // Agregar o actualizar el miembro al equipo (reenvío de invitación)
            if ($existe) {
                // Reenviar invitación - actualizar fecha y token
                $stmt = $conn->prepare("
                    UPDATE miembros_equipo 
                    SET fecha_ingreso = NOW(), rol_proyecto = ?, token_invitacion = ?, estado_invitacion = 'pendiente'
                    WHERE equipo_id = ? AND trabajador_dni = ?
                ");
                $stmt->execute([$rol_proyecto, $token_invitacion, $equipo['id'], $trabajador['dni']]);
                $esReenvio = true;
            } else {
                // Nueva invitación
                $stmt = $conn->prepare("
                    INSERT INTO miembros_equipo (equipo_id, trabajador_dni, rol_proyecto, estado_invitacion, fecha_ingreso, token_invitacion)
                    VALUES (?, ?, ?, 'pendiente', NOW(), ?)
                ");
                $stmt->execute([$equipo['id'], $trabajador['dni'], $rol_proyecto, $token_invitacion]);
                $esReenvio = false;
            }

            // Enviar email de bienvenida con enlace de confirmación
            try {
                $emailEnviado = enviarEmailBienvenida(
                    $trabajador['email'],
                    $trabajador['nombre'],
                    $equipo['nombre'],
                    $usuario['nombre'],
                    $usuario['email'],
                    $token_invitacion
                );
            } catch (Exception $e) {
                error_log('Error enviando email: ' . $e->getMessage());
                $emailEnviado = false;
            }

            $mensajeAccion = $esReenvio ? 'Invitación reenviada' : 'Miembro agregado al equipo';
            
            if ($emailEnviado) {
                echo json_encode([
                    'success' => true,
                    'message' => $mensajeAccion . ' exitosamente. Se ha enviado un email de bienvenida.',
                    'data' => [
                        'trabajador' => $trabajador,
                        'equipo' => $equipo,
                        'rol_proyecto' => $rol_proyecto,
                        'reenvio' => $esReenvio
                    ]
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'message' => $mensajeAccion . ' exitosamente, pero no se pudo enviar el email.',
                    'data' => [
                        'trabajador' => $trabajador,
                        'equipo' => $equipo,
                        'rol_proyecto' => $rol_proyecto,
                        'reenvio' => $esReenvio
                    ]
                ]);
            }

        } catch(PDOException $e) {
            handleDatabaseError('Error al agregar miembro al equipo', $e);
        } catch(Exception $e) {
            error_log('Error general: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error inesperado: ' . $e->getMessage()]);
        }
        break;

    case 'DELETE':
        // Eliminar miembro del equipo
        $usuario = verificarAutenticacion();

        if (!$usuario || $usuario['rol'] !== 'jefe_equipo') {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Solo los jefes de equipo pueden eliminar miembros']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $trabajador_dni = isset($input['trabajador_dni']) ? trim($input['trabajador_dni']) : '';

        if (empty($trabajador_dni)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'El DNI del trabajador es requerido']);
            exit;
        }

        try {
            $conn = getConexion();

            // Obtener equipo del jefe
            $stmt = $conn->prepare("
                SELECT e.id, e.nombre
                FROM equipos e
                WHERE e.jefe_dni = ?
            ");
            $stmt->execute([$usuario['dni']]);
            $equipo = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$equipo) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'El jefe de equipo no tiene un equipo asignado']);
                exit;
            }

            // Eliminar miembro del equipo
            $stmt = $conn->prepare("
                DELETE FROM miembros_equipo
                WHERE equipo_id = ? AND trabajador_dni = ?
            ");
            $stmt->execute([$equipo['id'], $trabajador_dni]);

            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Miembro eliminado del equipo correctamente'
                ]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'El trabajador no pertenece a este equipo']);
            }

        } catch(PDOException $e) {
            error_log('Error al eliminar miembro: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error al eliminar el miembro del equipo']);
        }
        break;

    case 'PUT':
        // Actualizar información del equipo (nombre)
        $usuario = verificarAutenticacion();

        if (!$usuario || $usuario['rol'] !== 'jefe_equipo') {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Solo los jefes de equipo pueden actualizar el equipo']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['nombre']) || empty(trim($input['nombre']))) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'El nombre del equipo es requerido']);
            exit;
        }

        try {
            $conn = getConexion();

            // Verificar que el jefe tenga un equipo
            $stmt = $conn->prepare("
                SELECT e.id
                FROM equipos e
                WHERE e.jefe_dni = ?
            ");
            $stmt->execute([$usuario['dni']]);
            $equipo = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$equipo) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'No se encontró el equipo del jefe']);
                exit;
            }

            // Actualizar nombre del equipo
            $stmt = $conn->prepare("
                UPDATE equipos
                SET nombre = ?, descripcion = ?
                WHERE id = ?
            ");
            
            $descripcion = isset($input['descripcion']) ? $input['descripcion'] : "Equipo gestionado por {$usuario['nombre']}";
            
            $stmt->execute([
                trim($input['nombre']),
                $descripcion,
                $equipo['id']
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Nombre del equipo actualizado exitosamente',
                'data' => [
                    'equipo_id' => $equipo['id'],
                    'nombre' => trim($input['nombre']),
                    'descripcion' => $descripcion
                ]
            ]);

        } catch(PDOException $e) {
            error_log('Error al actualizar equipo: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error al actualizar el equipo: ' . $e->getMessage()]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Método no permitido']);
        break;
}