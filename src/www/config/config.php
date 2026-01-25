<?php
/**
 * Archivo de configuración centralizado para la aplicación
 * 
 * Define constantes y configuraciones que se usan en toda la aplicación.
 * Para producción, ajustar los valores según el entorno.
 */

// Headers CORS completamente abiertos para desarrollo
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-User-DNI, X-User-Rol, X-User-Nombre, X-User-Email, X-Auth-Token, Origin, Accept, X-Requested-With');
header('Access-Control-Allow-Credentials: false');

// Manejar OPTIONS inmediatamente
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

// Cargar autoloader de Composer
// En Docker, el vendor está en /app/vendor
// En desarrollo local, está en la raíz del proyecto
$vendorPath = '/app/vendor/autoload.php';
if (!file_exists($vendorPath)) {
    $vendorPath = __DIR__ . '/../../../vendor/autoload.php';
}
require_once $vendorPath;

// Cargar variables de entorno (desde la raíz del proyecto)
try {
    // En Docker, el .env está en /app
    $envPath = '/app';
    if (!file_exists($envPath . '/.env')) {
        $envPath = __DIR__ . '/../../..';
    }
    $dotenv = Dotenv\Dotenv::createImmutable($envPath);
    $dotenv->load();
} catch (Exception $e) {
    // Si falla, usar valores por defecto
    error_log('Error cargando .env: ' . $e->getMessage());
}

require_once __DIR__ . '/helpers.php';

// ==========================================
// Configuración de Base de Datos
// ==========================================

define('DB_HOST', $_ENV['DB_HOST'] ?? '127.0.0.1');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'Logisteia');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');
define('DB_CHARSET', $_ENV['DB_CHARSET'] ?? 'utf8mb4');

// ==========================================
// Configuración del entorno
// ==========================================

define('APP_ENV', $_ENV['APP_ENV'] ?? 'development');
define('APP_DEBUG', filter_var($_ENV['APP_DEBUG'] ?? true, FILTER_VALIDATE_BOOLEAN));
define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost');

// ==========================================
// Configuración JWT
// ==========================================

define('JWT_SECRET', $_ENV['JWT_SECRET'] ?? 'CHANGE_THIS_SECRET_KEY_IN_PRODUCTION');
define('JWT_EXPIRATION', (int)($_ENV['JWT_EXPIRATION'] ?? 3600)); // 1 hora por defecto

// ==========================================
// Configuración de Errores
// ==========================================

if (APP_ENV === 'production') {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', __DIR__ . '/../logs/php_errors.log');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', APP_DEBUG ? '1' : '0');
    ini_set('log_errors', '1');
    ini_set('error_log', __DIR__ . '/../logs/php_errors.log');
}

// ==========================================
// Configuración de CORS
// ==========================================

// Leer orígenes permitidos desde .env
$allowedOriginsString = $_ENV['ALLOWED_ORIGINS'] ?? 'http://localhost:4200,http://localhost,http://127.0.0.1:4200';
$allowedOrigins = array_filter(array_map('trim', explode(',', $allowedOriginsString)));

define('ALLOWED_ORIGINS', $allowedOrigins);

// ==========================================
// Configuración de Seguridad
// ==========================================

define('MAX_LOGIN_ATTEMPTS', (int)($_ENV['MAX_LOGIN_ATTEMPTS'] ?? 5));
define('LOGIN_TIMEOUT_MINUTES', (int)($_ENV['LOGIN_TIMEOUT_MINUTES'] ?? 15));

// ==========================================
// Funciones helper
// ==========================================

/**
 * Configura los headers CORS basados en el origen de la petición
 * 
 * @return void
 */
function setupCors() {
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    
    // Si se ejecuta desde CLI, salir inmediatamente
    if (php_sapi_name() === 'cli') {
        return;
    }
    
    // Si no hay origin en la petición, intentar construirlo desde otros headers
    if (empty($origin)) {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if (!empty($referer)) {
            $parsedUrl = parse_url($referer);
            $origin = ($parsedUrl['scheme'] ?? 'http') . '://' . ($parsedUrl['host'] ?? 'localhost');
            if (isset($parsedUrl['port'])) {
                $origin .= ':' . $parsedUrl['port'];
            }
        }
    }
    
    // Verificar si el origen está en la lista de permitidos
    if (!empty($origin) && in_array($origin, ALLOWED_ORIGINS)) {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Credentials: true');
    } elseif (APP_ENV === 'development') {
        // En desarrollo, permitir localhost:4200 por defecto
        $defaultOrigin = !empty($origin) ? $origin : 'http://localhost:4200';
        header('Access-Control-Allow-Origin: ' . $defaultOrigin);
        header('Access-Control-Allow-Credentials: true');
    }
    
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-User-DNI, X-User-Rol, X-User-Nombre, X-User-Email, X-Auth-Token, Origin, Accept');
    header('Access-Control-Max-Age: 86400'); // Cache preflight por 24 horas
}

/**
 * Maneja peticiones OPTIONS (preflight)
 * Debe llamarse DESPUÉS de setupCors()
 * 
 * @return void
 */
function handlePreflight() {
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204); // No Content es más apropiado para OPTIONS
        exit();
    }
}

// La función logError está definida en helpers.php - no duplicar aquí

/**
 * Verifica la autenticación del usuario mediante headers personalizados
 * 
 * Por ahora usa una verificación simplificada hasta implementar JWT completo
 * 
 * @return array|null Datos del usuario autenticado o null si no está autenticado
 */
function verificarAutenticacion() {
    // Obtener headers personalizados directamente desde $_SERVER
    $userDni = $_SERVER['HTTP_X_USER_DNI'] ?? '';
    
    // Si se ejecuta desde CLI, retornar null
    if (php_sapi_name() === 'cli') {
        return null;
    }
    
    error_log(' HTTP_X_USER_DNI desde $_SERVER: ' . $userDni);
    error_log(' Todos los headers HTTP: ' . json_encode(array_filter($_SERVER, function($key) {
        return strpos($key, 'HTTP_') === 0;
    }, ARRAY_FILTER_USE_KEY)));
    
    if (empty($userDni)) {
        error_log(' X-User-Dni está vacío');
        return null;
    }
    
    // Verificar que el usuario existe en la base de datos y está activo
    try {
        $conn = ConexionBBDD::obtener();
        $stmt = $conn->prepare("SELECT dni, nombre, email, rol, estado FROM usuarios WHERE dni = ?");
        $stmt->execute([$userDni]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$usuario || $usuario['estado'] !== 'activo') {
            return null;
        }
        
        return [
            'dni' => $usuario['dni'],
            'rol' => $usuario['rol'],
            'nombre' => $usuario['nombre'],
            'email' => $usuario['email']
        ];
    } catch (Exception $e) {
        error_log('Error en verificarAutenticacion: ' . $e->getMessage());
        return null;
    }
}
