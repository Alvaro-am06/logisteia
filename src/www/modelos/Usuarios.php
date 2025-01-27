<?php
<?php
require_once __DIR__ . '/Conexion.php';

/**
 * Clase Usuarios
 * Operaciones básicas para la tabla usuarios (dni como PK).
 */
class Usuarios {
    // Conexión PDO
    private $db;

    public function __construct() {
        $this->db = Conexion::obtener();
    }

    /**
     * Obtener todos los usuarios (información básica).
     * @return array
     */
    public function obtenerTodos() {
        $stmt = $this->db->query("SELECT dni,email,nombre,rol,estado,creado_en FROM usuarios ORDER BY nombre");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener usuario por DNI.
     * @param string $dni
     * @return array|false
     */
    public function obtenerPorDni($dni) {
        $stmt = $this->db->prepare("SELECT dni,email,nombre,rol,estado,creado_en FROM usuarios WHERE dni = ?");
        $stmt->execute(array($dni));
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Cambiar estado de usuario (activo/suspendido/eliminado).
     * @param string $dni
     * @param string $estado
     * @return bool
     */
    public function cambiarEstado($dni, $estado) {
        $stmt = $this->db->prepare("UPDATE usuarios SET estado = ? WHERE dni = ?");
        return $stmt->execute(array($estado, $dni));
    }

    /**
     * Eliminación lógica (marcar como eliminado).
     * @param string $dni
     * @return bool
     */
    public function eliminarLogico($dni) {
        return $this->cambiarEstado($dni, 'eliminado');
    }

    /**
     * Crear usuario.
     * @param string $dni
     * @param string $nombre
     * @param string $email
     * @param string $hashContrase
     * @param string $rol
     * @return bool
     */
    public function crearUsuario($dni, $nombre, $email, $hashContrase, $rol = 'registrado') {
        $stmt = $this->db->prepare("INSERT INTO usuarios (dni,nombre,email,contrase,rol) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute(array($dni, $nombre, $email, $hashContrase, $rol));
    }
}