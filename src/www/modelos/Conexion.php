<?php
<?php
/**
 * Clase Conexion
 * Proporciona una instancia PDO reutilizable.
 */
class Conexion {
    /** @var PDO|null $pdo Instancia PDO compartida */
    private static $pdo = null;

    /**
     * Obtener instancia PDO
     * Ajusta $db, $user, $pass según tu entorno XAMPP/phpMyAdmin.
     *
     * @return PDO
     * @throws PDOException
     */
    public static function obtener() {
        if (self::$pdo === null) {
            $host = '127.0.0.1';
            $db   = 'Logisteia'; // AJUSTAR
            $user = 'root';
            $pass = '';
            $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
            $opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
            self::$pdo = new PDO($dsn, $user, $pass, $opts);
        }
        return self::$pdo;
    }
}