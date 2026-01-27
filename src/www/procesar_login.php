<?php
/**
 * ARCHIVO: /src/www/procesar_login.php
 * COMMIT 10: Procesamiento del login
 */

require_once 'controladores/AuthController.php';

$controller = new AuthController();
$controller->procesarLogin();
?>
