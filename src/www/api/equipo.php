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

        $mensaje = "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
</head>
<body style='margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;'>
    <table role='presentation' style='width: 100%; border-collapse: collapse; background-color: #f4f4f4;'>
        <tr>
            <td style='padding: 20px 0;' align='center'>
                <table role='presentation' style='width: 100%; max-width: 600px; border-collapse: collapse; background-color: #ffffff; box-shadow: 0 4px 6px rgba(0,0,0,0.1);'>
                    <!-- Header -->
                    <tr>
                        <td style='background: linear-gradient(135deg, #102a41 0%, #1a3f5e 100%); padding: 40px 30px; text-align: center;'>
                            <h1 style='margin: 0; color: #ffffff; font-size: 32px; font-weight: bold; letter-spacing: 2px;'>LOGISTEIA</h1>
                            <p style='margin: 10px 0 0 0; color: #ffffff; font-size: 14px; font-style: italic;'>Planifica con precisión. Ejecuta con control.</p>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style='padding: 40px 30px;'>
                            <h2 style='margin: 0 0 20px 0; color: #102a41; font-size: 24px;'>¡Hola $nombreDestinatario!</h2>
                            <p style='margin: 0 0 20px 0; color: #333333; font-size: 16px; line-height: 1.6;'>
                                Has sido invitado a formar parte del equipo <strong style='color: #102a41;'>$nombreEquipo</strong> en la plataforma Logisteia.
                            </p>
                            
                            <!-- Info Box -->
                            <div style='background-color: #f8f9fa; border-left: 4px solid #102a41; padding: 20px; margin: 20px 0;'>
                                <table style='width: 100%; border-collapse: collapse;'>
                                    <tr>
                                        <td style='padding: 8px 0; color: #666666; font-size: 14px;'><strong>Equipo:</strong></td>
                                        <td style='padding: 8px 0; color: #333333; font-size: 14px;'>$nombreEquipo</td>
                                    </tr>
                                    <tr>
                                        <td style='padding: 8px 0; color: #666666; font-size: 14px;'><strong>Invitado por:</strong></td>
                                        <td style='padding: 8px 0; color: #333333; font-size: 14px;'>$jefeNombre</td>
                                    </tr>
                                    <tr>
                                        <td style='padding: 8px 0; color: #666666; font-size: 14px;'><strong>Email del jefe:</strong></td>
                                        <td style='padding: 8px 0; color: #333333; font-size: 14px;'>$jefeEmail</td>
                                    </tr>
                                </table>
                            </div>
                            
                            <p style='margin: 30px 0 20px 0; color: #333333; font-size: 16px; line-height: 1.6;'>
                                Ahora formas parte de este equipo y podrás colaborar en proyectos. ¡Bienvenido!
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style='background-color: #f8f9fa; padding: 30px; text-align: center; border-top: 1px solid #e0e0e0;'>
                            <p style='margin: 0 0 10px 0; color: #102a41; font-size: 16px; font-weight: bold;'>LOGISTEIA</p>
                            <p style='margin: 0; color: #666666; font-size: 12px;'>Gestión profesional de proyectos</p>
                            <p style='margin: 15px 0 0 0; color: #999999; font-size: 11px;'>
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

        return enviarEmail($emailDestinatario, $nombreDestinatario, $asunto, $mensaje, 'logisteiaa@gmail.com', 'Equipo Logisteia');
    } catch (Exception $e) {
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
                    u.estado as estado_usuario,
                    em.activo,
                    em.estado_invitacion
                FROM miembros_equipo em
                INNER JOIN usuarios u ON em.trabajador_dni = u.dni
                WHERE em.equipo_id = ?
                AND em.activo = 1
                AND u.estado = 'activo'
                AND em.estado_invitacion = 'aceptada'
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
                SELECT id FROM miembros_equipo
                WHERE equipo_id = ? AND trabajador_dni = ? AND activo = 1
            ");
            $stmt->execute([$equipo['id'], $trabajador['dni']]);
            $existe = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existe) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'El trabajador ya es miembro del equipo']);
                exit;
            }

            // Asignar rol por defecto
            $rol_proyecto = 'Miembro del Equipo';

            // Generar token único para la invitación
            $token_invitacion = bin2hex(random_bytes(32));
            
            // Agregar miembro al equipo con estado_invitacion aceptada
            $stmt = $conn->prepare("
                INSERT INTO miembros_equipo (equipo_id, trabajador_dni, rol_proyecto, fecha_ingreso, activo, estado_invitacion)
                VALUES (?, ?, ?, NOW(), 1, 'aceptada')
            ");
            $stmt->execute([$equipo['id'], $trabajador['dni'], $rol_proyecto]);
            $esReenvio = false;

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
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error al actualizar el equipo: ' . $e->getMessage()]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Método no permitido']);
        break;
}