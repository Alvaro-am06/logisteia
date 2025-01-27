<?php
class Conexion {
    private static $pdo = null;

    public static function obtener() {
        if (self::$pdo === null) {
            $host = '127.0.0.1';
            $db   = 'Logisteia'; 
            $user = 'root';
            $pass = '';
            $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
            $opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
            self::$pdo = new PDO($dsn, $user, $pass, $opts);
        }
        return self::$pdo;
    }
}
