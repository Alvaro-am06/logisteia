<?php
/**
 * ARCHIVO: /src/www/login.php
 * COMMIT 10: Página de login
 */

require_once 'controladores/AuthController.php';

$controller = new AuthController();
$controller->mostrarLogin();
?>
