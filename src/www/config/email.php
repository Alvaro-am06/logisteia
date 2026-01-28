<?php
/**
 * Configuración de email usando PHPMailer
 */

// Cargar variables de entorno desde .env
$envFile = __DIR__ . '/../../../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// Definir APP_ENV como constante desde la variable de entorno
if (!defined('APP_ENV')) {
    define('APP_ENV', $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'production');
}

// Buscar autoload en diferentes ubicaciones posibles
$autoloadPaths = [
    '/app/vendor/autoload.php',                // En contenedor Docker (composer install en /app)
    __DIR__ . '/../../../vendor/autoload.php', // Raíz del proyecto
    __DIR__ . '/../vendor/autoload.php',       // Si está en src/www
    __DIR__ . '/../../vendor/autoload.php',    // Si está en src
];

$autoloadLoaded = false;
foreach ($autoloadPaths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $autoloadLoaded = true;
        break;
    }
}

if (!$autoloadLoaded) {
    throw new Exception('No se pudo encontrar el autoload de Composer. Ejecuta: composer install');
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Enviar email usando PHPMailer con configuración SMTP
 * 
 * @param string $destinatario Email del destinatario
 * @param string $nombreDestinatario Nombre del destinatario
 * @param string $asunto Asunto del email
 * @param string $mensajeHTML Contenido HTML del mensaje
 * @param string $remitente Email del remitente (opcional)
 * @param string $nombreRemitente Nombre del remitente (opcional)
 * @return bool True si se envió correctamente
 */
function enviarEmail($destinatario, $nombreDestinatario, $asunto, $mensajeHTML, $remitente = null, $nombreRemitente = null) {
    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Cambiar según tu proveedor
        $mail->SMTPAuth = true;
        $mail->Username = 'logisteiaa@gmail.com'; // Email oficial de Logisteia
        $mail->Password = getenv('GMAIL_APP_PASSWORD'); // Contraseña de aplicación de Gmail
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';

        // Remitente
        $emailRemitente = $remitente ?? 'logisteiaa@gmail.com';
        $nombreRemitenteEmail = $nombreRemitente ?? 'Logisteia';
        $mail->setFrom($emailRemitente, $nombreRemitenteEmail);

        // Destinatario
        $mail->addAddress($destinatario, $nombreDestinatario);

        // Contenido
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body = $mensajeHTML;
        $mail->AltBody = strip_tags($mensajeHTML);

        $mail->send();
        error_log("✅ Email enviado exitosamente a: $destinatario | Asunto: $asunto");
        return true;
    } catch (Exception $e) {
        error_log("❌ ERROR ENVIANDO EMAIL a: $destinatario");
        error_log("   Mensaje de error: {$mail->ErrorInfo}");
        error_log("   Excepción: " . $e->getMessage());
        error_log("   SMTP Host: {$mail->Host}");
        error_log("   SMTP Username: {$mail->Username}");
        error_log("   GMAIL_APP_PASSWORD configurado: " . (getenv('GMAIL_APP_PASSWORD') ? 'SÍ' : 'NO'));
        
        // En desarrollo, guardar en log como fallback
        if (APP_ENV === 'development') {
            $logFile = __DIR__ . '/../logs/emails.log';
            $logEntry = date('Y-m-d H:i:s') . " - Email NO enviado a: $destinatario\n";
            $logEntry .= "Asunto: $asunto\n";
            $logEntry .= "Error: {$mail->ErrorInfo}\n";
            $logEntry .= "Mensaje:\n$mensajeHTML\n";
            $logEntry .= str_repeat("=", 50) . "\n\n";
            file_put_contents($logFile, $logEntry, FILE_APPEND);
        }
        
        return false;
    }
}

/**
 * Enviar email en modo desarrollo (solo guarda en log sin enviar)
 */
function enviarEmailDev($destinatario, $asunto, $mensajeHTML, $remitente = null, $nombreRemitente = null) {
    $logFile = __DIR__ . '/../logs/emails.log';
    $logDir = dirname($logFile);

    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $emailRemitente = $remitente ?? 'logisteiaa@gmail.com';
    $nombreRemitenteEmail = $nombreRemitente ?? 'Logisteia';

    $logEntry = date('Y-m-d H:i:s') . " - [DEV] Email a: $destinatario\n";
    $logEntry .= "De: $nombreRemitenteEmail <$emailRemitente>\n";
    $logEntry .= "Asunto: $asunto\n";
    $logEntry .= "Mensaje:\n$mensajeHTML\n";
    $logEntry .= str_repeat("=", 50) . "\n\n";

    file_put_contents($logFile, $logEntry, FILE_APPEND);
    error_log("📧 [DEV] Email guardado en log para: $destinatario");
    
    return true;
}
