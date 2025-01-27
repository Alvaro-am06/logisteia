<?php
/**
 * Plantilla General del Sistema.
 * 
 * Proporciona la estructura HTML base para todas las vistas del sistema.
 * Incluye el encabezado con información del usuario y el contenido dinámico.
 * 
 * Variables esperadas antes de incluir este archivo:
 * - $titulo (string): Título de la página que se mostrará en el <title> y <h1>
 * - $contenido (string): HTML ya generado con el contenido principal de la vista
 * 
 */

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Obtener y sanitizar nombre del administrador
$admin = isset($_SESSION['admin_nombre']) ? htmlspecialchars($_SESSION['admin_nombre'], ENT_QUOTES, 'UTF-8') : null;

// Sanitizar título
$tituloSanitizado = htmlspecialchars($titulo ?? 'Sistema de Gestión', ENT_QUOTES, 'UTF-8');

// Destruye la sesión y redirige al usuario al inicio antes de cualquier salida HTML
if (isset($_GET['logout'])) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    session_destroy();
    header('Location: ../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $tituloSanitizado; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            line-height: 1.6;
            background: #f4f4f4;
        }
        header {
            background: #333;
            color: #fff;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        header h1 {
            margin: 0;
            font-size: 1.5em;
        }
        .user-info {
            color: #fff;
        }
        .user-info a {
            color: #fff;
            background: #d9534f;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 4px;
            margin-left: 15px;
        }
        .user-info a:hover {
            background: #c9302c;
        }
        main {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        table th {
            background: #333;
            color: #fff;
        }
        table tr:nth-child(even) {
            background: #f9f9f9;
        }
        .acciones a {
            margin-right: 8px;
            color: #337ab7;
            text-decoration: none;
        }
        .acciones a:hover {
            text-decoration: underline;
        }
        button, input[type="submit"] {
            background: #337ab7;
            color: #fff;
            border: none;
            padding: 8px 15px;
            cursor: pointer;
            border-radius: 3px;
        }
        button:hover, input[type="submit"]:hover {
            background: #286090;
        }
        input[type="text"], input[type="email"], input[type="password"] {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 3px;
            width: 100%;
            max-width: 400px;
        }
    </style>
</head>
<body>
    <header>
        <h1><?php echo $tituloSanitizado; ?></h1>
        <?php if ($admin): ?>
            <div class="user-info">
                Usuario: <strong><?php echo $admin; ?></strong>
                    <a href="?logout=1">Cerrar Sesión</a>
            </div>
        <?php endif; ?>
    </header>

    <main>
        <?php 
        echo $contenido; 
        ?>
    </main>
</body>
</html>

<?php
// Destruye la sesión y redirige al usuario al inicio
if (isset($_GET['logout'])) {
    session_start();
    session_destroy();
    header('Location: index.php');
    exit;
}
?>
