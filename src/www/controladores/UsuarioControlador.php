<?php
require_once 'modelos/Usuarios.php';
require_once 'modelos/AccionesAdministrativas.php';

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

    /** Mostrar lista de usuarios */
    public function listar() {
        try {
            $usuarios = $this->modeloUsuario->obtenerTodos();
        } catch (Exception $e) {
            $usuarios = [];
            $_SESSION['error'] = 'Error al obtener usuarios: ' . $e->getMessage();
        }
        include 'vistas/usuarios.php';
    }

    /** Ver detalle y historial de un usuario por DNI */
    public function ver($dni) {
        try {
            $usuario = $this->modeloUsuario->obtenerPorDni($dni);
        } catch (Exception $e) {
            $usuario = false;
            $_SESSION['error'] = 'Error al obtener usuario: ' . $e->getMessage();
        }
        $historial = $this->modeloAccion->obtenerPorUsuario($dni);
        include 'vistas/usuarios.php';
    }

    /**
     * Cambiar rol del usuario y registrar la acción.
     * $operacion: activar|suspender|eliminar
     */
    public function cambiarRol($dni, $operacion, $motivo = null) {
        $admin = $_SESSION['administrador_email'] ?? 'desconocido';
        try {
            if ($operacion === 'activar') {
                $this->modeloUsuario->activar($dni);
                $this->modeloAccion->registrar($admin, 'activar', $dni, $motivo);
            } elseif ($operacion === 'suspender') {
                $this->modeloUsuario->suspender($dni);
                $this->modeloAccion->registrar($admin, 'suspender', $dni, $motivo);
            } elseif ($operacion === 'eliminar') {
                $this->modeloUsuario->eliminarLogico($dni);
                $this->modeloAccion->registrar($admin, 'eliminar', $dni, $motivo);
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al cambiar rol: ' . $e->getMessage();
        }
        // Redirigir a la lista tras la operación
        header('Location: index.php?accion=listar');
        exit;
    }

    /** Mostrar historial completo */
    public function historial() {
        $h = $this->modeloAccion->obtenerTodos();
        include 'vistas/usuarios.php';
    }
}
