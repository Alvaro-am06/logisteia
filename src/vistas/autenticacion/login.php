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
                echo $_SESSION['error']; 
                unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['mensaje'])): ?>
        <div style="color: green; border: 1px solid green; padding: 10px; margin: 10px 0;">
            <?php 
                echo $_SESSION['mensaje']; 
                unset($_SESSION['mensaje']);
            ?>
        </div>
    <?php endif; ?>

    <form action="procesar_login.php" method="POST">
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

    <p><small>Usuario de prueba: admin@sistema.com | Contraseña: Admin123!</small></p>
</body>
</html>
