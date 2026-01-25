<?php
// Índice mínimo: solo enruta a la vista correspondiente.
// NOTA: las vistas deben requerir los modelos/controladores que necesiten.
if (session_status() === PHP_SESSION_NONE) session_start();

$accion = isset($_GET['accion']) ? $_GET['accion'] : 'listar';

// atajos de login/logout (solo desarrollo)
if ($accion === 'login') {
    $_SESSION['administrador_email'] = 'admin@example.com';
    header('Location: index.php?accion=listar');
    exit;
}
if ($accion === 'logout') {
    unset($_SESSION['administrador_email']);
    header('Location: index.php?accion=listar');
    exit;
}

// Mapa simple acción -> fichero de vista (todas las vistas viven en /vistas)
$mapa = array(
    'listar'   => 'vistas/usuarios.php',
    'ver'      => 'vistas/usuarios.php',
    'cambiar'  => 'vistas/usuarios.php',
    'historial'=> 'vistas/usuarios.php',
);

// seleccionar vista (por defecto lista)
$vistaRel = isset($mapa[$accion]) ? $mapa[$accion] : 'vistas/usuarios.php';
$vista = __DIR__ . '/' . $vistaRel;

if (file_exists($vista)) {
    include $vista;
} else {
    http_response_code(404);
    echo "Vista no encontrada: " . htmlspecialchars($vistaRel);
}