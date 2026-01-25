<?php
require_once __DIR__ . '/../modelos/Usuarios.php';
require_once __DIR__ . '/../modelos/AccionesAdministrativas.php';

/**
 * Controlador para gestión de usuarios (HU-03).
 * Rutas esperadas: listar, ver (dni), cambiar (dni + op), historial.
 */
class UsuarioControlador {
    private $modeloUsuario;
    private $modeloAccion;

    public function __construct() {
        $this->modeloUsuario = new Usuarios();
        $this->modeloAccion = new AccionesAdministrativas();
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    /**
     * Identificador del administrador actual (simulado en desarrollo).
     * @return string
     */
    private function administradorActual() {
        return $_SESSION['administrador_email'] ?? 'admin_simulado';
    }

    /** Mostrar lista de usuarios */
    public function listar() {
        $usuarios = $this->modeloUsuario->obtenerTodos();
        include __DIR__ . '/../vistas/usuarios.php';
    }

    /** Ver detalle y historial de un usuario por DNI */
    public function ver($dni) {
        $usuario = $this->modeloUsuario->obtenerPorDni($dni);
        $historial = $this->modeloAccion->obtenerPorUsuario($dni);
        include __DIR__ . '/../vistas/usuarios.php';
    }

    /**
     * Cambiar estado del usuario y registrar la acción.
     * $operacion: activar|suspender|eliminar
     */
    public function cambiarEstado($dni, $operacion, $motivo = null) {
        $admin = $this->administradorActual();
        if ($operacion === 'activar') {
            $this->modeloUsuario->cambiarEstado($dni, 'activo');
            $this->modeloAccion->registrar($admin, 'activar', $dni, $motivo);
        } elseif ($operacion === 'suspender') {
            $this->modeloUsuario->cambiarEstado($dni, 'suspendido');
            $this->modeloAccion->registrar($admin, 'suspender', $dni, $motivo);
        } elseif ($operacion === 'eliminar') {
            $this->modeloUsuario->eliminarLogico($dni);
            $this->modeloAccion->registrar($admin, 'eliminar', $dni, $motivo);
        }
        // Redirigir a la lista tras la operación
        header('Location: index.php?accion=listar');
        exit;
    }

    /** Mostrar historial completo */
    public function historial() {
        $h = $this->modeloAccion->obtenerTodos();
        include __DIR__ . '/../vistas/usuarios.php';
    }
}