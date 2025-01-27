<?php
/**
 * API endpoint para login de administradores.
 * 
 * Recibe POST con JSON: {"email": "...", "password": "..."}
 * Devuelve JSON con success o error.
 */

require_once '../controladores/ControladorDeAutenticacion.php';

$controller = new ControladordeAutenticacion();
$controller->apiLogin();
?>