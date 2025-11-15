<?php
/**
 * Clase Conexion
 * Si la base de datos o tablas no existen, las crea automáticamente.
 */
class Conexion {
    private static $pdo = null;

    public static function obtener() {
        if (self::$pdo === null) {
            $host = '127.0.0.1';
            $db   = 'Logisteia';
            $user = 'root';
            $pass = '';
            $opts = array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            );

            try {
                // intento conectar a la BD
                $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
                self::$pdo = new PDO($dsn, $user, $pass, $opts);
            } catch (PDOException $e) {
                // si la BD no existe, la creamos y reconectamos
                if (stripos($e->getMessage(), 'Unknown database') !== false || stripos($e->getMessage(), '1049') !== false) {
                    $dsnNoDb = "mysql:host=$host;charset=utf8mb4";
                    $tmp = new PDO($dsnNoDb, $user, $pass, $opts);
                    $tmp->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
                    self::$pdo = new PDO($dsn, $user, $pass, $opts);
                } else {
                    throw $e;
                }
            }

            // crear tablas si no existen
            try {
                $schema = "
CREATE TABLE IF NOT EXISTS usuarios (
  dni VARCHAR(50) NOT NULL PRIMARY KEY,
  email VARCHAR(255) NOT NULL UNIQUE,
  nombre VARCHAR(255) NOT NULL,
  contrase VARCHAR(255) NOT NULL,
  rol ENUM('administrador','registrado') NOT NULL DEFAULT 'registrado',
  estado ENUM('activo','suspendido','eliminado') NOT NULL DEFAULT 'activo',
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS acciones_administrativas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  administrador VARCHAR(255) NOT NULL,
  accion VARCHAR(50) NOT NULL,
  usuario_dni VARCHAR(50),
  motivo TEXT NULL,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_acc_usuario FOREIGN KEY (usuario_dni) REFERENCES usuarios(dni) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
                self::$pdo->exec($schema);
            } catch (PDOException $e) {
                // si algo falla aquí, relanzamos para que se vea en desarrollo
                throw $e;
            }

            // insertar administrador por defecto si no existe
            try {
                $check = self::$pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = ? OR dni = ?");
                $check->execute(array('admin@example.com', '11111111A'));
                if ($check->fetchColumn() == 0) {
                    $hash = password_hash('admin123', PASSWORD_DEFAULT);
                    $ins = self::$pdo->prepare("INSERT INTO usuarios (dni,email,nombre,contrase,rol,estado) VALUES (?, ?, ?, ?, 'administrador', 'activo')");
                    $ins->execute(array('11111111A', 'admin@example.com', 'Administrador', $hash));

                    $log = self::$pdo->prepare("INSERT INTO acciones_administrativas (administrador, accion, usuario_dni, motivo) VALUES (?, 'crear', ?, ?)");
                    $log->execute(array('admin@example.com', '11111111A', 'Cuenta administrador inicial'));
                }
            } catch (PDOException $e) {
                // no crítico; relanzar para ver error en desarrollo
                throw $e;
            }
        }

        return self::$pdo;
    }
}