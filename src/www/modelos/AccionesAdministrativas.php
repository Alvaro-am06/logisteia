<?php
require_once __DIR__ . '/Conexion.php';

/**
 * Clase AccionesAdministrativas
 * Registra y consulta acciones realizadas por administradores.
 */
class AccionesAdministrativas {
    private $db;

    public function __construct() {
        $this->db = Conexion::obtener();
    }

    /**
     * Registrar una acción administrativa.
     * @param string $administrador
     * @param string $accion
     * @param string|null $usuario_dni
     * @param string|null $motivo
     * @return bool
     */
    public function registrar($administrador, $accion, $usuario_dni = null, $motivo = null) {
        $stmt = $this->db->prepare("INSERT INTO acciones_administrativas (administrador, accion, usuario_dni, motivo) VALUES (?, ?, ?, ?)");
        return $stmt->execute(array($administrador, $accion, $usuario_dni, $motivo));
    }

    /**
     * Obtener todas las acciones.
     * @return array
     */
    public function obtenerTodos() {
        $stmt = $this->db->query("SELECT acciones_administrativas.id,acciones_administrativas.administrador,acciones_administrativas.accion,acciones_administrativas.usuario_dni,acciones_administrativas.motivo,acciones_administrativas.creado_en FROM acciones_administrativas ORDER BY creado_en DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener acciones por usuario (dni).
     * @param string $usuarioDni
     * @return array
     */
    public function obtenerPorUsuario($usuarioDni) {
        $stmt = $this->db->prepare("SELECT acciones_administrativas.id,acciones_administrativas.administrador,acciones_administrativas.accion,acciones_administrativas.motivo,acciones_administrativas.creado_en FROM acciones_administrativas WHERE usuario_dni = ? ORDER BY creado_en DESC");
        $stmt->execute(array($usuarioDni));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}