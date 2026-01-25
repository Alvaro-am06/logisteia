<?php
/**
 * Clase Conexion
 * Intenta conectar a la BD; si la BD no existe la crea y vuelve a conectar.
 */
class Conexion {
    private static $pdo = null;

    public static function obtener() {
        if (self::$pdo === null) {
            $host = '127.0.0.1';
            $db   = 'Logisteia';
            $user = 'root';
            $pass = '';
            $opts = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4");

            try {
                // intento normal (si la BD ya existe)
                $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
                self::$pdo = new PDO($dsn, $user, $pass, $opts);
            } catch (PDOException $e) {
                // si la BD no existe, la creamos y reconectamos
                if (stripos($e->getMessage(), 'Unknown database') !== false || stripos($e->getMessage(), '1049') !== false) {
                    // conectar sin seleccionar BD
                    $dsnNoDb = "mysql:host=$host;charset=utf8mb4";
                    $tmp = new PDO($dsnNoDb, $user, $pass, $opts);
                    $tmp->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    // volver a conectar a la BD recién creada
                    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
                    self::$pdo = new PDO($dsn, $user, $pass, $opts);
                } else {
                    // re-lanzar otros errores
                    throw $e;
                }
            }
        }
        return self::$pdo;
    }
}