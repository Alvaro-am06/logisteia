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
class Conexion {
    // Instancia única de la conexión PDO
    private static $pdo = null;

    /**
     * Obtiene la instancia única de conexión a la base de datos.
     * 
     * Si no existe una conexión activa, crea una nueva utilizando PDO.
     * Implementa el patrón Singleton para garantizar una sola conexión.
     * 
     * Configuración de conexión:
     * - Host: 127.0.0.1 (localhost)
     * - Base de datos: Logisteia
     * - Usuario: root
     * - Contraseña: (vacía por defecto en XAMPP)
     * - Charset: UTF-8
     * 
     * @return PDO Instancia de conexión PDO
     * @throws PDOException Si falla la conexión a la base de datos
     */
    public static function obtener() {
        if (self::$pdo === null) {
            try {
                // Configuración de conexión
                $host = '127.0.0.1';
                $db   = 'Logisteia'; 
                $user = 'root';
                $pass = '';
                
                // DSN (Data Source Name)
                $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
                
                // Opciones de PDO
                $opciones = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];
                
                // Crear conexión PDO
                self::$pdo = new PDO($dsn, $user, $pass, $opciones);
                
            } catch (PDOException $e) {
                // Log del error (en producción usar un sistema de logs apropiado)
                error_log("Error de conexión a BD: " . $e->getMessage());
                
                // Lanzar excepción para que sea manejada por el código que llama
                throw new PDOException("Error al conectar con la base de datos. Contacte al administrador.");
            }
        }
        
        return self::$pdo;
    }
}
