<?php
require_once __DIR__ . '/../modelos/ConexionBBDD.php';
require_once __DIR__ . '/../modelos/Cliente.php';
require_once __DIR__ . '/ControladorDeAutenticacion.php';

/**
 * Controlador para la gestión de clientes.
 * Métodos: listar, mostrarFormulario, guardar, eliminar, buscar.
 */
class ControladorCliente {
    private $db;
    private $cliente;

    /**
     * Constructor. Inicializa la sesión y el modelo Cliente.
     */
    public function __construct() {
        ControladordeAutenticacion::verificarSesion();
        $database = new Conexion();
        $this->db = $database->obtener();
        $this->cliente = new Cliente($this->db);
    }

    public function obtenerCliente($id) {
        return $this->cliente->obtenerPorDni($id);
    }

    public function listarClientes() {
        $stmt = $this->cliente->obtenerTodos();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lista todos los clientes y carga la vista de listado.
     */
    public function listar() {
        $stmt = $this->cliente->obtenerTodos();
        $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        include 'vistas/clientes/listado.php';
    }

    /**
     * Muestra el formulario para crear o editar un cliente.
     * @param string|null $dni DNI del cliente a editar (opcional)
     */
    public function mostrarFormulario($dni = null) {
        $clienteData = null;
        if ($dni) {
            if ($this->cliente->obtenerPorDni($dni)) {
                $clienteData = array(
                    'dni' => $this->cliente->dni,
                    'nombre' => $this->cliente->nombre,
                    'email' => $this->cliente->email,
                    'telefono' => $this->cliente->telefono
                );
            }
        }
        include '../vistas/clientes/formulario.php';
    }

    /**
     * Guarda los datos de un cliente (crear o actualizar).
     */
    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->cliente->dni = $_POST['dni'] ?? null;
            $this->cliente->nombre = $_POST['nombre'] ?? null;
            $this->cliente->email = $_POST['email'] ?? null;
            $this->cliente->telefono = $_POST['telefono'] ?? null;
            $this->cliente->contrase = $_POST['contrase'] ?? null;
            if (empty($this->cliente->nombre) || empty($this->cliente->email) || empty($this->cliente->telefono) || empty($this->cliente->contrase)) {
                $_SESSION['error'] = "Los campos DNI, nombre, email, teléfono y contraseña son obligatorios";
                header('Location: clientes.php?accion=nuevo');
                exit();
            }
            if (isset($_POST['dni']) && !empty($_POST['dni'])) {
                if ($this->cliente->actualizar()) {
                    $_SESSION['mensaje'] = "Cliente actualizado correctamente";
                } else {
                    $_SESSION['error'] = "Error al actualizar el cliente";
                }
            } else {
                if ($this->cliente->crear()) {
                    $_SESSION['mensaje'] = "Cliente registrado correctamente";
                } else {
                    $_SESSION['error'] = "Error al registrar el cliente";
                }
            }
            header('Location: clientes.php');
            exit();
        }
    }

    /**
     * Elimina un cliente por su DNI.
     * @param string $dni DNI del cliente a eliminar
     */
    public function eliminar($dni) {
        if ($this->cliente->eliminar($dni)) {
            $_SESSION['mensaje'] = "Cliente eliminado correctamente";
        } else {
            $_SESSION['error'] = "Error al eliminar el cliente";
        }
        header('Location: clientes.php');
        exit();
    }

    /**
     * Busca clientes por término y carga la vista de listado.
     */
    public function buscar() {
        $termino = $_GET['buscar'] ?? '';
        if (empty($termino)) {
            header('Location: clientes.php');
            exit();
        }
        $stmt = $this->cliente->buscar($termino);
        $clientes = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        include '../vistas/clientes/listado.php';
    }
}
