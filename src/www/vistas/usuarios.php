<?php
/**
 * Vista Unificada de Gestión de Usuarios.
 * 
 * Esta vista maneja todas las operaciones relacionadas con usuarios:
 * - Listar todos los usuarios del sistema
 * - Ver detalle de un usuario específico
 * - Cambiar estado de usuario (activar/suspender/eliminar)
 * - Ver historial de acciones administrativas
 * 
 * Parámetros GET esperados:
 * - accion: Tipo de operación (listar|ver|cambiar|historial)
 * - dni: DNI del usuario (requerido para ver y cambiar)
 * - op: Operación a realizar (activar|suspender|eliminar, requerido para cambiar)
 * 
 * Parámetros POST:
 * - motivo: Justificación de la acción (opcional)
 * 
 */

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cargar modelos necesarios
require_once '../modelos/Usuarios.php';
require_once '../modelos/AccionesAdministrativas.php';

$modelUsuarios = new Usuarios();
$modelAcciones = new AccionesAdministrativas();

// Obtener y sanitizar la acción solicitada
$accion = filter_input(INPUT_GET, 'accion', FILTER_SANITIZE_SPECIAL_CHARS) ?? 'listar';

// Validar que la acción sea válida
$accionesValidas = ['listar', 'ver', 'cambiar', 'historial'];
if (!in_array($accion, $accionesValidas)) {
    $accion = 'listar';
}

// Proteger acciones administrativas: verificar autenticación
$accionesProtegidas = ['cambiar', 'historial'];
if (in_array($accion, $accionesProtegidas) && empty($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit;
}

// Procesar cambio de estado de usuario (activar/suspender/eliminar)
if ($accion === 'cambiar' && isset($_GET['dni']) && isset($_GET['op'])) {
    // Sanitizar y validar DNI
    $dni = filter_input(INPUT_GET, 'dni', FILTER_SANITIZE_SPECIAL_CHARS);
    if (empty($dni)) {
        header('Location: panel_admin.php?accion=listar');
        exit;
    }
    
    // Sanitizar y validar operación
    $op = filter_input(INPUT_GET, 'op', FILTER_SANITIZE_SPECIAL_CHARS);
    $operacionesValidas = ['activar', 'suspender', 'eliminar'];
    if (!in_array($op, $operacionesValidas)) {
        header('Location: panel_admin.php?accion=listar');
        exit;
    }
    
    // Obtener motivo (sanitizado)
    $motivo = filter_input(INPUT_POST, 'motivo', FILTER_SANITIZE_SPECIAL_CHARS);
    $motivo = !empty($motivo) ? trim($motivo) : null;
    
    // Obtener administrador de la sesión
    $admin = $_SESSION['admin_id'] ?? null;
    
    if ($admin) {
        // Ejecutar la operación solicitada
        if ($op === 'activar') {
            $modelUsuarios->activar($dni);
            $modelAcciones->registrar($admin, 'activar', $dni, $motivo);
        } elseif ($op === 'suspender') {
            $modelUsuarios->suspender($dni);
            $modelAcciones->registrar($admin, 'suspender', $dni, $motivo);
        } elseif ($op === 'eliminar') {
            // Registrar ANTES de eliminar para evitar problemas de clave foránea
            $modelAcciones->registrar($admin, 'eliminar', $dni, $motivo);
            $modelUsuarios->eliminar($dni);
        }
    }

    // Redirigir a la lista después de la operación
    header('Location: panel_admin.php?accion=listar');
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
      <tr><th>DNI</th><th>Nombre</th><th>Email</th><th>Rol</th><th>Acciones</th></tr>
      <?php foreach ($usuarios as $u): ?>
        <tr>
          <td><?php echo htmlspecialchars($u['dni'] ?? ''); ?></td>
          <td><?php echo htmlspecialchars($u['nombre'] ?? ''); ?></td>
          <td><?php echo htmlspecialchars($u['email'] ?? ''); ?></td>
          <td><?php echo htmlspecialchars($u['rol'] ?? ''); ?></td>
          <td class="acciones">
            <a href="panel_admin.php?accion=ver&dni=<?php echo urlencode($u['dni']); ?>">Ver</a> |
            <a href="panel_admin.php?accion=cambiar&dni=<?php echo urlencode($u['dni']); ?>&op=activar" onclick="return confirm('Activar usuario?')">Activar</a> |
            <a href="panel_admin.php?accion=cambiar&dni=<?php echo urlencode($u['dni']); ?>&op=suspender" onclick="return confirm('Suspender usuario?')">Suspender</a> |
            <a href="panel_admin.php?accion=cambiar&dni=<?php echo urlencode($u['dni']); ?>&op=eliminar" onclick="return confirm('Eliminar usuario?')">Eliminar</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
    <p><a href="panel_admin.php?accion=historial">Ver historial administrativo</a></p>
    <?php
    $contenido = ob_get_clean();
    $titulo = 'Usuarios registrados';

} elseif ($accion === 'ver' && isset($_GET['dni'])) {
    // Sanitizar y validar DNI
    $dni = filter_input(INPUT_GET, 'dni', FILTER_SANITIZE_SPECIAL_CHARS);
    
    if (empty($dni)) {
        // DNI no válido, redirigir a listado
        header('Location: panel_admin.php?accion=listar');
        exit;
    }
    
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
      </ul>

      <h3>Acciones</h3>
      <form method="post" action="panel_admin.php?accion=cambiar&dni=<?php echo urlencode($usuario['dni']); ?>&op=activar">
        <input type="text" name="motivo" placeholder="Motivo (opcional)">
        <button type="submit">Activar</button>
      </form>

      <form method="post" action="panel_admin.php?accion=cambiar&dni=<?php echo urlencode($usuario['dni']); ?>&op=suspender" style="margin-top:6px">
        <input type="text" name="motivo" placeholder="Motivo (opcional)">
        <button type="submit">Suspender</button>
      </form>

      <form method="post" action="panel_admin.php?accion=cambiar&dni=<?php echo urlencode($usuario['dni']); ?>&op=eliminar" style="margin-top:6px">
        <input type="text" name="motivo" placeholder="Motivo (opcional)">
        <button type="submit" onclick="return confirm('¿Eliminar usuario?')">Eliminar</button>
      </form>

      <h3>Historial de este usuario</h3>
      <?php if (empty($historial)): ?>
        <p>No hay acciones registradas.</p>
      <?php else: ?>
        <ul>
          <?php foreach ($historial as $h): ?>
            <li><?php echo htmlspecialchars($h['creado_en']); ?> — <?php echo htmlspecialchars($h['administrador_dni']); ?> — <?php echo htmlspecialchars($h['accion']); ?> — <?php echo htmlspecialchars($h['motivo']); ?></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    <?php endif; ?>
    <p><a href="panel_admin.php?accion=listar">Volver a lista</a></p>
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
            <td><?php echo htmlspecialchars($row['administrador_dni']); ?></td>
            <td><?php echo htmlspecialchars($row['accion']); ?></td>
            <td><?php echo htmlspecialchars($row['usuario_dni']); ?></td>
            <td><?php echo htmlspecialchars($row['motivo']); ?></td>
          </tr>
        <?php endforeach; ?>
      </table>
    <?php endif; ?>
    <p><a href="panel_admin.php?accion=listar">Volver a lista</a></p>
    <?php
    $contenido = ob_get_clean();
    $titulo = 'Historial administrativo';

} else {
    // Acción desconocida -> mensaje simple
    $titulo = 'Acción';
    $contenido = '<p>Acción no reconocida.</p>';
}

// Finalmente incluir la plantilla (render)
include 'plantilla.php';