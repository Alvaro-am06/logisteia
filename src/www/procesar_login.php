<?php
require_once 'controladores/ControladorDeAutenticacion.php';

$controller = new ControladordeAutenticacion();
$controller->procesarLogin();
?>
