<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);
/**
 * Página de inicio de sesión del sistema.
 * 
 * Muestra el formulario de login y redirige a usuarios autenticados
 * al panel de administración.
 * 
 */
session_start();

// Procesar login si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'controladores/ControladorDeAutenticacion.php';
    $controller = new ControladordeAutenticacion();
    $controller->procesarLogin();
    exit();
}

// Redirigir usuarios ya autenticados al panel de administración
if (isset($_SESSION['admin_id'])) {
    header('Location: vistas/panel_admin.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Gestión de Clientes</title>
</head>
<body>
    <h1>Sistema de Gestión de Clientes</h1>
    <h2>Iniciar Sesión</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <div style="color: red; border: 1px solid red; padding: 10px; margin: 10px 0;">
            <?php 
                // Sanitizar salida de mensaje de error
                echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); 
                unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['mensaje'])): ?>
        <div style="color: green; border: 1px solid green; padding: 10px; margin: 10px 0;">
            <?php 
                // Sanitizar salida de mensaje de éxito
                echo htmlspecialchars($_SESSION['mensaje'], ENT_QUOTES, 'UTF-8'); 
                unset($_SESSION['mensaje']);
            ?>
        </div>
    <?php endif; ?>

    <form action="index.php" method="POST">
        <div>
            <label for="email">Email:</label><br>
            <input type="email" id="email" name="email" required>
        </div>

        <div>
            <label for="password">Contraseña:</label><br>
            <input type="password" id="password" name="password" required>
        </div>

        <div>
            <button type="submit">Iniciar Sesión</button>
        </div>
    </form>

    <p><small>Usuario de ejemplo: admin@example.com | Contraseña: 1234</small></p>
</body>
</html>
<?php
echo json_encode(['success' => false, 'error' => 'Mensaje de error']);
?>
