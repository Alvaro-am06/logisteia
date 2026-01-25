<?php
/**
 * Panel de Administración.
 * 
 * Punto de entrada principal para el área de administración del sistema.
 * Verifica la autenticación del usuario y enruta las peticiones a las vistas correspondientes.
 * 
 * Acciones soportadas:
 * - listar: Muestra el listado de usuarios
 * - ver: Muestra el detalle de un usuario específico
 * - cambiar: Permite activar/suspender/eliminar un usuario
 * - historial: Muestra el historial de acciones administrativas
 * 
 */

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación - redirigir a login si no está autenticado
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit();
}

// Obtener y sanitizar la acción solicitada
$accion = filter_input(INPUT_GET, 'accion', FILTER_SANITIZE_SPECIAL_CHARS) ?? 'listar';

// Validar que la acción sea válida usando whitelist
$accionesValidas = ['listar', 'ver', 'cambiar', 'historial'];
if (!in_array($accion, $accionesValidas)) {
    $accion = 'listar'; // Acción por defecto si no es válida
}

// Mapa de acciones a vistas (todas apuntan a usuarios.php por ahora)
$mapaVistas = [
    'listar'    => 'usuarios.php',
    'ver'       => 'usuarios.php',
    'cambiar'   => 'usuarios.php',
    'historial' => 'usuarios.php',
];

// Seleccionar vista según la acción
$vistaRelativa = $mapaVistas[$accion];
$rutaVista = $vistaRelativa;

// Incluir la vista si existe, o mostrar error 404
if (file_exists($rutaVista)) {
    include $rutaVista;
} else {
    http_response_code(404);
    echo "Vista no encontrada: " . htmlspecialchars($vistaRelativa, ENT_QUOTES, 'UTF-8');
}
