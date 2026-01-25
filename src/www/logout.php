<?php
/**
 * ARCHIVO: /src/www/logout.php
 * COMMIT 10: Cerrar sesión
 */

require_once 'controladores/AuthController.php';

$controller = new AuthController();
$controller->logout();
?>
