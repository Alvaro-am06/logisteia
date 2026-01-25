<?php
header('Content-Type: text/html; charset=UTF-8');

require_once '../config/config.php';

// Función para obtener conexión a la base de datos
function getConexion() {
    try {
        $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        handleDatabaseError('Error de conexión a la base de datos', $e);
    }
}

$mensaje = "";
$tipoMensaje = "";

if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $_GET['token'];

    try {
        $conn = getConexion();

        // Buscar la invitación por token
        $stmt = $conn->prepare("
            SELECT me.id, me.equipo_id, me.trabajador_dni, me.estado_invitacion,
                   u.nombre as trabajador_nombre, e.nombre as equipo_nombre
            FROM miembros_equipo me
            INNER JOIN usuarios u ON me.trabajador_dni = u.dni
            INNER JOIN equipos e ON me.equipo_id = e.id
            WHERE me.token_invitacion = ? AND me.activo = 1
        ");
        $stmt->execute([$token]);
        $invitacion = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($invitacion) {
            if ($invitacion['estado_invitacion'] === 'pendiente') {
                // Actualizar el estado de la invitación a aceptada
                $stmt = $conn->prepare("
                    UPDATE miembros_equipo
                    SET estado_invitacion = 'aceptada'
                    WHERE id = ?
                ");
                $stmt->execute([$invitacion['id']]);

                $mensaje = "¡Felicitaciones! Has confirmado exitosamente tu participación en el equipo '{$invitacion['equipo_nombre']}'.";
                $tipoMensaje = "success";
            } elseif ($invitacion['estado_invitacion'] === 'aceptada') {
                $mensaje = "Esta invitación ya ha sido aceptada anteriormente.";
                $tipoMensaje = "info";
            } elseif ($invitacion['estado_invitacion'] === 'rechazada') {
                $mensaje = "Esta invitación fue rechazada anteriormente.";
                $tipoMensaje = "warning";
            }
        } else {
            $mensaje = "Token de invitación inválido o expirado.";
            $tipoMensaje = "error";
        }

    } catch(PDOException $e) {
        $mensaje = "Error al procesar la invitación.";
        $tipoMensaje = "error";
    }
} else {
    $mensaje = "Token de invitación no proporcionado.";
    $tipoMensaje = "error";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Invitación - Logisteia</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            max-width: 500px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 40px;
            text-align: center;
            margin: 20px;
        }

        .logo {
            font-size: 2.5em;
            font-weight: bold;
            color: #102a41;
            margin-bottom: 20px;
        }

        .message {
            font-size: 1.2em;
            margin: 30px 0;
            line-height: 1.6;
        }

        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        .info { color: #17a2b8; }

        .icon {
            font-size: 4em;
            margin-bottom: 20px;
        }

        .success .icon { color: #28a745; }
        .error .icon { color: #dc3545; }
        .warning .icon { color: #ffc107; }
        .info .icon { color: #17a2b8; }

        .button {
            display: inline-block;
            padding: 12px 30px;
            background: #102a41;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .button:hover {
            background: #0f2336;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 0.9em;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">Logisteia</div>

        <div class="message <?php echo $tipoMensaje; ?>">
            <?php if ($tipoMensaje === 'success'): ?>
                <div class="icon">✅</div>
            <?php elseif ($tipoMensaje === 'error'): ?>
                <div class="icon">❌</div>
            <?php elseif ($tipoMensaje === 'warning'): ?>
                <div class="icon">⚠️</div>
            <?php else: ?>
                <div class="icon">ℹ️</div>
            <?php endif; ?>

            <?php echo $mensaje; ?>
        </div>

        <a href="http://localhost/logisteia/src/www/index.php" class="button">
            Ir a Logisteia
        </a>

        <div class="footer">
            <p>Si tienes alguna pregunta, contacta con tu jefe de equipo.</p>
        </div>
    </div>
</body>
</html>