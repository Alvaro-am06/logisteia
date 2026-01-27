<?php
/**
 * Funciones helper globales para la aplicación
 */

/**
 * Maneja errores de base de datos
 */
function handleDatabaseError($message, $exception) {
    logError($message, $exception);
    sendJsonError($message, 500, APP_ENV === 'development' ? $exception->getMessage() : null);
}

/**
 * Maneja errores generales
 */
function handleError($statusCode, $message) {
    sendJsonError($message, $statusCode);
}

/**
 * Envía respuesta de éxito
 */
function handleSuccess($data = [], $message = '') {
    $response = ['success' => true];
    if (!empty($data)) {
        $response['data'] = $data;
    }
    if (!empty($message)) {
        $response['message'] = $message;
    }
    sendJsonSuccess($response);
}

/**
 * Envía una respuesta JSON de error y termina la ejecución
 * 
 * @param string $message Mensaje de error
 * @param int $code Código HTTP (400, 401, 500, etc.)
 * @param mixed $details Detalles adicionales (solo en desarrollo)
 * @return void
 */
function sendJsonError($message, $code = 400, $details = null) {
    http_response_code($code);
    $response = [
        'success' => false,
        'error' => $message
    ];
    
    // Solo incluir detalles en desarrollo
    if (APP_ENV === 'development' && APP_DEBUG && $details !== null) {
        $response['details'] = $details;
    }
    
    echo json_encode($response);
    exit();
}

/**
 * Envía una respuesta JSON de éxito y termina la ejecución
 * 
 * @param mixed $data Datos a enviar
 * @param int $code Código HTTP (200, 201, etc.)
 * @return void
 */
function sendJsonSuccess($data, $code = 200) {
    http_response_code($code);
    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
    exit();
}

/**
 * Sanitiza una cadena para prevenir XSS
 * 
 * @param string $input Cadena a sanitizar
 * @return string Cadena sanitizada
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Valida y sanitiza un email
 * 
 * @param string $email Email a validar
 * @return string|false Email sanitizado o false si es inválido
 */
function validateEmail($email) {
    $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : false;
}

/**
 * Registra un error en el log
 * 
 * @param string $message Mensaje de error
 * @param Exception|null $exception Excepción opcional
 * @return void
 */
function logError($message, $exception = null) {
    $logMessage = date('[Y-m-d H:i:s] ') . $message;
    
    if ($exception !== null) {
        $logMessage .= PHP_EOL . '  Exception: ' . $exception->getMessage();
        $logMessage .= PHP_EOL . '  Archivo: ' . $exception->getFile();
        $logMessage .= PHP_EOL . '  Línea: ' . $exception->getLine();
        $logMessage .= PHP_EOL . '  Stack trace: ' . PHP_EOL . $exception->getTraceAsString();
    }
    
    $logMessage .= PHP_EOL . str_repeat('-', 80) . PHP_EOL;
    
    // Asegurar que el directorio de logs existe
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    error_log($logMessage, 3, $logDir . '/app_errors.log');
}

/**
 * Registra información general en el log
 * 
 * @param string $message Mensaje a registrar
 * @return void
 */
function logInfo($message) {
    $logMessage = date('[Y-m-d H:i:s] [INFO] ') . $message . PHP_EOL;
    
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    error_log($logMessage, 3, $logDir . '/app_info.log');
}

/**
 * Genera datos calculados para un presupuesto
 */
function generarDatosPresupuesto($presupuesto) {
    $total = isset($presupuesto['total']) ? floatval($presupuesto['total']) : 0;
    $iva = $total * 0.21;
    $totalIVA = $total * 1.21;
    
    $validezDias = isset($presupuesto['validez_dias']) ? intval($presupuesto['validez_dias']) : 30;
    $notas = isset($presupuesto['notas']) ? $presupuesto['notas'] : '';
    
    return [
        'total' => number_format($total, 2, ',', '.'),
        'iva' => number_format($iva, 2, ',', '.'),
        'totalIVA' => number_format($totalIVA, 2, ',', '.'),
        'fecha' => date('d/m/Y', strtotime($presupuesto['fecha_creacion'])),
        'fechaValidez' => date('d/m/Y', strtotime($presupuesto['fecha_creacion'] . ' + ' . $validezDias . ' days')),
        'esWizard' => strpos($notas, 'PRESUPUESTO GENERADO DESDE CONFIGURADOR') !== false
    ];
}

