<?php
/**
 * Sistema de rate limiting para prevenir ataques de fuerza bruta
 */

/**
 * Verifica y registra intentos de login
 * 
 * @param string $identifier Identificador único (email, IP, etc.)
 * @return bool true si puede intentar login, false si está bloqueado
 */
function verificarRateLimitLogin($identifier) {
    $sessionKey = 'login_attempts_' . md5($identifier);
    $timeKey = 'login_first_attempt_' . md5($identifier);
    
    if (!isset($_SESSION)) {
        session_start();
    }
    
    $currentTime = time();
    $attempts = $_SESSION[$sessionKey] ?? 0;
    $firstAttempt = $_SESSION[$timeKey] ?? $currentTime;
    
    // Calcular tiempo transcurrido desde el primer intento
    $timeElapsed = $currentTime - $firstAttempt;
    $timeoutSeconds = LOGIN_TIMEOUT_MINUTES * 60;
    
    // Si ha pasado el tiempo de timeout, resetear contadores
    if ($timeElapsed > $timeoutSeconds) {
        $_SESSION[$sessionKey] = 0;
        $_SESSION[$timeKey] = $currentTime;
        return true;
    }
    
    // Si se excedió el máximo de intentos
    if ($attempts >= MAX_LOGIN_ATTEMPTS) {
        $minutosRestantes = ceil(($timeoutSeconds - $timeElapsed) / 60);
        return false;
    }
    
    return true;
}

/**
 * Registra un intento de login fallido
 * 
 * @param string $identifier Identificador único
 * @return int Intentos restantes
 */
function registrarIntentoFallido($identifier) {
    $sessionKey = 'login_attempts_' . md5($identifier);
    $timeKey = 'login_first_attempt_' . md5($identifier);
    
    if (!isset($_SESSION)) {
        session_start();
    }
    
    $currentTime = time();
    
    if (!isset($_SESSION[$sessionKey])) {
        $_SESSION[$sessionKey] = 0;
        $_SESSION[$timeKey] = $currentTime;
    }
    
    $_SESSION[$sessionKey]++;
    
    return MAX_LOGIN_ATTEMPTS - $_SESSION[$sessionKey];
}

/**
 * Resetea los intentos de login (después de un login exitoso)
 * 
 * @param string $identifier Identificador único
 */
function resetearIntentosLogin($identifier) {
    $sessionKey = 'login_attempts_' . md5($identifier);
    $timeKey = 'login_first_attempt_' . md5($identifier);
    
    if (!isset($_SESSION)) {
        session_start();
    }
    
    unset($_SESSION[$sessionKey]);
    unset($_SESSION[$timeKey]);
}

/**
 * Obtiene el número de intentos restantes
 * 
 * @param string $identifier Identificador único
 * @return int Intentos restantes
 */
function obtenerIntentosRestantes($identifier) {
    $sessionKey = 'login_attempts_' . md5($identifier);
    
    if (!isset($_SESSION)) {
        session_start();
    }
    
    $attempts = $_SESSION[$sessionKey] ?? 0;
    return max(0, MAX_LOGIN_ATTEMPTS - $attempts);
}

/**
 * Obtiene el tiempo restante de bloqueo en minutos
 * 
 * @param string $identifier Identificador único
 * @return int Minutos restantes de bloqueo (0 si no está bloqueado)
 */
function obtenerTiempoBloqueo($identifier) {
    $timeKey = 'login_first_attempt_' . md5($identifier);
    
    if (!isset($_SESSION)) {
        session_start();
    }
    
    if (!isset($_SESSION[$timeKey])) {
        return 0;
    }
    
    $currentTime = time();
    $firstAttempt = $_SESSION[$timeKey];
    $timeElapsed = $currentTime - $firstAttempt;
    $timeoutSeconds = LOGIN_TIMEOUT_MINUTES * 60;
    
    $remainingSeconds = $timeoutSeconds - $timeElapsed;
    
    return $remainingSeconds > 0 ? ceil($remainingSeconds / 60) : 0;
}
