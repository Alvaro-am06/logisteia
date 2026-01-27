<?php
require_once 'ConexionBBDD.php';

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
        $stmt = $this->db->query("SELECT usuarios.dni, usuarios.email, usuarios.nombre, usuarios.rol, usuarios.estado, usuarios.telefono, usuarios.fecha_registro FROM usuarios ORDER BY nombre");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener usuario por DNI.
     * @param string $dni
     * @return array|false
     */
    public function obtenerPorDni($dni) {
        $stmt = $this->db->prepare("SELECT usuarios.dni, usuarios.email, usuarios.nombre, usuarios.rol, usuarios.estado, usuarios.telefono, usuarios.fecha_registro FROM usuarios WHERE dni = ?");
        $stmt->execute(array($dni));
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener usuario por email.
     * @param string $email
     * @return array|false
     */
    public function obtenerPorEmail($email) {
        $stmt = $this->db->prepare("SELECT usuarios.dni, usuarios.email, usuarios.nombre, usuarios.rol, usuarios.estado, usuarios.telefono, usuarios.fecha_registro FROM usuarios WHERE email = ?");
        $stmt->execute(array($email));
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Cambiar rol de usuario.
     * @param string $dni
     * @param string $rol
     * @return bool
     */
    public function cambiarRol($dni, $rol) {
        $stmt = $this->db->prepare("UPDATE usuarios SET rol = ? WHERE dni = ?");
        return $stmt->execute(array($rol, $dni));
    }

    /**
     * Eliminación lógica (marcar como eliminado).
     * @param string $dni
     * @return bool
     */
    public function eliminarLogico($dni) {
        $stmt = $this->db->prepare("UPDATE usuarios SET estado = 'eliminado' WHERE dni = ?");
        return $stmt->execute(array($dni));
    }

    /**
     * Activar usuario (cambiar a administrador y estado activo).
     * @param string $dni
     * @return bool
     */
    public function activar($dni) {
        $stmt = $this->db->prepare("UPDATE usuarios SET rol = 'administrador', estado = 'activo' WHERE dni = ?");
        return $stmt->execute(array($dni));
    }

    /**
     * Suspender usuario (cambiar a registrado y estado suspendido).
     * @param string $dni
     * @return bool
     */
    public function suspender($dni) {
        $stmt = $this->db->prepare("UPDATE usuarios SET rol = 'registrado', estado = 'suspendido' WHERE dni = ?");
        return $stmt->execute(array($dni));
    }

    /**
     * Eliminar usuario físicamente de la base de datos.
     * @param string $dni
     * @return bool
     */
    public function eliminar($dni) {
        // Eliminar primero las acciones administrativas asociadas
        $stmtAcciones = $this->db->prepare("DELETE FROM acciones_administrativas WHERE usuario_dni = ?");
        $stmtAcciones->execute(array($dni));
        // Ahora eliminar el usuario
        $stmt = $this->db->prepare("DELETE FROM usuarios WHERE dni = ?");
        return $stmt->execute(array($dni));
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
        $stmt = $this->db->prepare("INSERT INTO usuarios (dni,nombre,email,password,rol) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute(array($dni, $nombre, $email, $hashContrase, $rol));
    }
}