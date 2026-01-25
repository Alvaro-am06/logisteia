<?php
/**
 * Clase Conexión a Base de Datos.
 * 
 * Implementa el patrón Singleton para gestionar una única instancia de conexión
 * PDO a la base de datos MySQL.
 * 
 * Características:
 * - Conexión única (Singleton) para optimizar recursos
 * - PDO con prepared statements para seguridad
 * - Modo de errores con excepciones para mejor debugging
 * - Charset UTF-8 para soporte de caracteres especiales
 * 
 */
class ConexionBBDD {
    // Instancia única de la conexión
    private static $instancia = null;
    private $pdo;

    public function __construct() {
        if (self::$instancia === null) {
            self::$instancia = $this;
            $this->initializeConnection();
        }
    }

    private function initializeConnection() {
        try {
            // Cargar configuración desde config.php (que carga .env)
            if (!defined('DB_HOST')) {
                require_once __DIR__ . '/../config/config.php';
            }
            
            // Usar constantes definidas desde variables de entorno
            $host = DB_HOST;
            $db   = DB_NAME;
            $user = DB_USER;
            $pass = DB_PASS;
            
            // DSN (Data Source Name)
            $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
            
            // Opciones de PDO
            $opciones = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            // Crear conexión PDO
            $this->pdo = new PDO($dsn, $user, $pass, $opciones);
            
        } catch (PDOException $e) {
            // Log del error (en producción usar un sistema de logs apropiado)
            error_log("Error de conexión a BD: " . $e->getMessage());
            
            // Lanzar excepción para que sea manejada por el código que llama
            throw new PDOException("Error al conectar con la base de datos. Contacte al administrador.");
        }
    }

    /**
     * Obtiene la instancia única de conexión a la base de datos.
     * 
     * @return ConexionBBDD Instancia de la conexión
     */
    public static function obtenerInstancia() {
        if (self::$instancia === null) {
            self::$instancia = new ConexionBBDD();
        }
        return self::$instancia;
    }

    /**
     * Obtiene la conexión PDO directamente (método estático para compatibilidad).
     * 
     * @return PDO Instancia de conexión PDO
     */
    public static function obtener() {
        return self::obtenerInstancia()->obtenerBBDD();
    }

    /**
     * Obtiene la conexión PDO.
     * 
     * @return PDO Instancia de conexión PDO
     */
    public function obtenerBBDD() {
        return $this->pdo;
    }
}

// Alias para compatibilidad con código existente
class_alias('ConexionBBDD', 'Conexion');
