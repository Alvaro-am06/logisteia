<?php
/**
 * API endpoint para login de administradores.
 * 
 * Recibe POST con JSON: {"email": "...", "password": "..." }
 * Devuelve JSON con success o error.
 */

// Cargar configuración centralizada
require_once __DIR__ . '/../config/config.php';

// Configurar CORS y headers
setupCors();
header('Content-Type: application/json');
handlePreflight();

try {
    require_once __DIR__ . '/../controladores/ControladorDeAutenticacion.php';
    
    $controller = new ControladorDeAutenticacion(); 
    $controller->apiLogin();
} catch (Exception $e) {
    // Registrar error en log
    logError('Error en endpoint de login', $e);
    
    // No exponer detalles en producción
    sendJsonError(
        'Error interno del servidor',
        500,
        APP_ENV === 'development' ? $e->getMessage() : null
    );
}