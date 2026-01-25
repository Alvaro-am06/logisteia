<?php
/**
 * Vista unificada para usuarios:
 * - listar (accion=listar)
 * - ver (accion=ver&dni=...)
 * - cambiar (accion=cambiar&dni=...&op=...)
 * - historial (accion=historial)
 *
 * Esta vista realiza la lógica mínima (usa modelos) y luego pasa $titulo/$contenido a plantilla.php.
 */

if (session_status() === PHP_SESSION_NONE) session_start();

// Cargar modelos
require_once __DIR__ . '/../modelos/Usuarios.php';
require_once __DIR__ . '/../modelos/AccionesAdministrativas.php';

$modelUsuarios = new Usuarios();
$modelAcciones = new AccionesAdministrativas();

// Acción solicitada
$accion = isset($_GET['accion']) ? $_GET['accion'] : 'listar';

// Protecciones: si no hay admin y la acción es administrativa, redirigir a ?accion=login
$protegidas = array('cambiar','historial');
if (in_array($accion, $protegidas) && empty($_SESSION['administrador_email'])) {
    header('Location: index.php?accion=login');
    exit;
}

// Procesar cambio antes de enviar salida (si corresponde)
if ($accion === 'cambiar' && isset($_GET['dni']) && isset($_GET['op'])) {
    $dni = $_GET['dni'];
    $op = $_GET['op'];
    $motivo = isset($_POST['motivo']) ? trim($_POST['motivo']) : null;
    $admin = $_SESSION['administrador_email'] ?? 'admin_simulado';

    if ($op === 'activar') {
        $modelUsuarios->cambiarEstado($dni, 'activo');
        $modelAcciones->registrar($admin, 'activar', $dni, $motivo);
    } elseif ($op === 'suspender') {
        $modelUsuarios->cambiarEstado($dni, 'suspendido');
        $modelAcciones->registrar($admin, 'suspender', $dni, $motivo);
    } elseif ($op === 'eliminar') {
        $modelUsuarios->eliminarLogico($dni);
        $modelAcciones->registrar($admin, 'eliminar', $dni, $motivo);
    }

    // Después de la operación, redirigir a la lista
    header('Location: index.php?accion=listar');
    exit;
}

// Preparar datos para render según la acción
$contenido = '';
if ($accion === 'listar') {
    $usuarios = $modelUsuarios->obtenerTodos();
    ob_start();
    ?>
    <h2>Usuarios registrados</h2>
    <table>
      <tr><th>DNI</th><th>Nombre</th><th>Email</th><th>Rol</th><th>Estado</th><th>Acciones</th></tr>
      <?php foreach ($usuarios as $u): ?>
        <tr>
          <td><?php echo htmlspecialchars($u['dni'] ?? ''); ?></td>
          <td><?php echo htmlspecialchars($u['nombre'] ?? ''); ?></td>
          <td><?php echo htmlspecialchars($u['email'] ?? ''); ?></td>
          <td><?php echo htmlspecialchars($u['rol'] ?? ''); ?></td>
          <td><?php echo htmlspecialchars($u['estado'] ?? ''); ?></td>
          <td class="acciones">
            <a href="index.php?accion=ver&dni=<?php echo urlencode($u['dni']); ?>">Ver</a> |
            <a href="index.php?accion=cambiar&dni=<?php echo urlencode($u['dni']); ?>&op=activar" onclick="return confirm('Activar usuario?')">Activar</a> |
            <a href="index.php?accion=cambiar&dni=<?php echo urlencode($u['dni']); ?>&op=suspender" onclick="return confirm('Suspender usuario?')">Suspender</a> |
            <a href="index.php?accion=cambiar&dni=<?php echo urlencode($u['dni']); ?>&op=eliminar" onclick="return confirm('Eliminar usuario?')">Eliminar</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
    <p><a href="index.php?accion=historial">Ver historial administrativo</a></p>
    <?php
    $contenido = ob_get_clean();
    $titulo = 'Usuarios registrados';

} elseif ($accion === 'ver' && isset($_GET['dni'])) {
    $dni = $_GET['dni'];
    $usuario = $modelUsuarios->obtenerPorDni($dni);
    $historial = $modelAcciones->obtenerPorUsuario($dni);

    ob_start();
    ?>
    <h2>Detalle usuario <?php echo htmlspecialchars($usuario['dni'] ?? ''); ?></h2>
    <?php if (!$usuario): ?>
      <p>Usuario no encontrado.</p>
    <?php else: ?>
      <ul>
        <li>Nombre: <?php echo htmlspecialchars($usuario['nombre']); ?></li>
        <li>Email: <?php echo htmlspecialchars($usuario['email']); ?></li>
        <li>Rol: <?php echo htmlspecialchars($usuario['rol']); ?></li>
        <li>Estado: <?php echo htmlspecialchars($usuario['estado']); ?></li>
        <li>Creado: <?php echo htmlspecialchars($usuario['creado_en']); ?></li>
      </ul>

      <h3>Acciones</h3>
      <form method="post" action="index.php?accion=cambiar&dni=<?php echo urlencode($usuario['dni']); ?>&op=activar">
        <input type="text" name="motivo" placeholder="Motivo (opcional)">
        <button type="submit">Activar</button>
      </form>

      <form method="post" action="index.php?accion=cambiar&dni=<?php echo urlencode($usuario['dni']); ?>&op=suspender" style="margin-top:6px">
        <input type="text" name="motivo" placeholder="Motivo (opcional)">
        <button type="submit">Suspender</button>
      </form>

      <form method="post" action="index.php?accion=cambiar&dni=<?php echo urlencode($usuario['dni']); ?>&op=eliminar" style="margin-top:6px">
        <input type="text" name="motivo" placeholder="Motivo (opcional)">
        <button type="submit" onclick="return confirm('¿Eliminar usuario?')">Eliminar</button>
      </form>

      <h3>Historial de este usuario</h3>
      <?php if (empty($historial)): ?>
        <p>No hay acciones registradas.</p>
      <?php else: ?>
        <ul>
          <?php foreach ($historial as $h): ?>
            <li><?php echo htmlspecialchars($h['creado_en']); ?> — <?php echo htmlspecialchars($h['administrador']); ?> — <?php echo htmlspecialchars($h['accion']); ?> — <?php echo htmlspecialchars($h['motivo']); ?></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    <?php endif; ?>
    <p><a href="index.php?accion=listar">Volver a lista</a></p>
    <?php
    $contenido = ob_get_clean();
    $titulo = 'Detalle usuario';

} elseif ($accion === 'historial') {
    $h = $modelAcciones->obtenerTodos();
    ob_start();
    ?>
    <h2>Historial administrativo (todas las acciones)</h2>
    <?php if (empty($h)): ?>
      <p>No hay registros.</p>
    <?php else: ?>
      <table>
        <tr><th>Fecha</th><th>Administrador</th><th>Acción</th><th>DNI Usuario</th><th>Motivo</th></tr>
        <?php foreach ($h as $row): ?>
          <tr>
            <td><?php echo htmlspecialchars($row['creado_en']); ?></td>
            <td><?php echo htmlspecialchars($row['administrador']); ?></td>
            <td><?php echo htmlspecialchars($row['accion']); ?></td>
            <td><?php echo htmlspecialchars($row['usuario_dni']); ?></td>
            <td><?php echo htmlspecialchars($row['motivo']); ?></td>
          </tr>
        <?php endforeach; ?>
      </table>
    <?php endif; ?>
    <p><a href="index.php?accion=listar">Volver a lista</a></p>
    <?php
    $contenido = ob_get_clean();
    $titulo = 'Historial administrativo';

} else {
    // Acción desconocida -> mensaje simple
    $titulo = 'Acción';
    $contenido = '<p>Acción no reconocida.</p>';
}

// Finalmente incluir la plantilla (render)
include __DIR__ . '/plantilla.php';