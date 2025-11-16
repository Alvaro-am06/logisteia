<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Clientes</title>
</head>
<body>
    <h1>Gestión de Clientes</h1>
    
    <p>Bienvenido, <strong><?php echo $_SESSION['admin_nombre']; ?></strong> | 
       <a href="logout.php">Cerrar Sesión</a>
    </p>

    <?php if (isset($_SESSION['mensaje'])): ?>
        <div style="color: green; border: 1px solid green; padding: 10px; margin: 10px 0;">
            <?php 
                echo $_SESSION['mensaje']; 
                unset($_SESSION['mensaje']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div style="color: red; border: 1px solid red; padding: 10px; margin: 10px 0;">
            <?php 
                echo $_SESSION['error']; 
                unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <div>
        <a href="clientes.php?accion=nuevo">
            <button>Registrar Nuevo Cliente</button>
        </a>
    </div>

    <div style="margin: 20px 0;">
        <form action="clientes.php" method="GET">
            <input type="hidden" name="accion" value="buscar">
            <input type="text" name="buscar" placeholder="Buscar por nombre, correo o empresa..." 
                   value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>">
            <button type="submit">Buscar</button>
            <?php if (isset($_GET['buscar'])): ?>
                <a href="clientes.php"><button type="button">Limpiar</button></a>
            <?php endif; ?>
        </form>
    </div>

    <h2>Listado de Clientes</h2>

    <?php if (isset($clientes) && count($clientes) > 0): ?>
        <table border="1" cellpadding="10" cellspacing="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Teléfono</th>
                    <th>Empresa</th>
                    <th>Registrado por</th>
                    <th>Fecha Registro</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clientes as $cliente): ?>
                    <tr>
                        <td><?php echo $cliente['id']; ?></td>
                        <td><?php echo htmlspecialchars($cliente['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($cliente['correo']); ?></td>
                        <td><?php echo htmlspecialchars($cliente['telefono']); ?></td>
                        <td><?php echo $cliente['empresa'] ? htmlspecialchars($cliente['empresa']) : '-'; ?></td>
                        <td><?php echo htmlspecialchars($cliente['nombre_admin']); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($cliente['fecha_registro'])); ?></td>
                        <td>
                            <a href="clientes.php?accion=editar&id=<?php echo $cliente['id']; ?>">Editar</a> |
                            <a href="clientes.php?accion=eliminar&id=<?php echo $cliente['id']; ?>" 
                               onclick="return confirm('¿Está seguro de eliminar este cliente?')">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p>Total de clientes: <?php echo count($clientes); ?></p>
    <?php else: ?>
        <p>No se encontraron clientes.</p>
    <?php endif; ?>
</body>
</html>
