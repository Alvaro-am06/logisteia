<?php
/**
 * Plantilla general.
 * Variables esperadas antes de incluir:
 *   - $titulo   (string)
 *   - $contenido (string) HTML ya generado
 */
if (session_status() === PHP_SESSION_NONE) session_start();
$admin = isset($_SESSION['administrador_email']) ? $_SESSION['administrador_email'] : null;
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title><?php echo isset($titulo) ? htmlspecialchars($titulo) : 'Aplicación'; ?></title>
  <style>
    body { font-family: Arial, sans-serif; margin: 16px; }
    header, footer { margin-bottom: 12px; }
    table { border-collapse: collapse; width: 100%; }
    table td, table th { padding: 6px; border: 1px solid #ccc; text-align: left; }
    .acciones a { margin-right: 6px; }
  </style>
</head>
<body>
  <header>
    <h1><?php echo isset($titulo) ? htmlspecialchars($titulo) : 'Aplicación'; ?></h1>
    <?php if ($admin): ?>
      <p>Conectado como: <?php echo htmlspecialchars($admin); ?> | <a href="index.php?accion=logout">Cerrar sesión</a></p>
    <?php else: ?>
      <p><a href="index.php?accion=login">Simular login administrador</a></p>
    <?php endif; ?>
    <hr>
  </header>

  <main>
    <?php echo isset($contenido) ? $contenido : ''; ?>
  </main>

  <footer>
    <hr>
    <p>Sistema básico de gestión de usuarios — HU-03</p>
  </footer>
</body>
</html>