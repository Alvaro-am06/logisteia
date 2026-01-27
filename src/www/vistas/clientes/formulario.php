<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($clienteData) ? 'Editar' : 'Registrar'; ?> Cliente</title>
</head>
<body>
    <h1><?php echo isset($clienteData) ? 'Editar' : 'Registrar Nuevo'; ?> Cliente</h1>

    <p>
        <a href="clientes.php">← Volver al listado</a>
    </p>

    <?php if (isset($_SESSION['error'])): ?>
        <div style="color: red; border: 1px solid red; padding: 10px; margin: 10px 0;">
            <?php 
                echo $_SESSION['error']; 
                unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <form action="guardar_cliente.php" method="POST">
        <?php if (isset($clienteData)): ?>
            <input type="hidden" name="id" value="<?php echo $clienteData['id']; ?>">
        <?php endif; ?>

        <div>
            <label for="nombre">Nombre: *</label><br>
            <input type="text" id="nombre" name="nombre" required
                   value="<?php echo isset($clienteData) ? htmlspecialchars($clienteData['nombre']) : ''; ?>">
        </div>

        <div>
            <label for="correo">Correo Electrónico: *</label><br>
            <input type="email" id="correo" name="correo" required
                   value="<?php echo isset($clienteData) ? htmlspecialchars($clienteData['correo']) : ''; ?>">
        </div>

        <div>
            <label for="telefono">Teléfono: *</label><br>
            <input type="tel" id="telefono" name="telefono" required
                   value="<?php echo isset($clienteData) ? htmlspecialchars($clienteData['telefono']) : ''; ?>">
        </div>

        <div>
            <label for="empresa">Empresa:</label><br>
            <input type="text" id="empresa" name="empresa"
                   value="<?php echo isset($clienteData) ? htmlspecialchars($clienteData['empresa']) : ''; ?>">
        </div>

        <div>
            <label for="direccion">Dirección:</label><br>
            <textarea id="direccion" name="direccion" rows="3"><?php echo isset($clienteData) ? htmlspecialchars($clienteData['direccion']) : ''; ?></textarea>
        </div>

        <div>
            <button type="submit"><?php echo isset($clienteData) ? 'Actualizar' : 'Registrar'; ?> Cliente</button>
            <a href="clientes.php"><button type="button">Cancelar</button></a>
        </div>
    </form>

    <p><small>* Campos obligatorios</small></p>
</body>
</html>
