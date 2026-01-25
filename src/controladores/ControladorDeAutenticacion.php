<?php
require_once './modelos/ConexionBBDD.php';
require_once './modelos/Administrador.php';
require_once './modelos/Database.php';

class ControladordeAutenticacion {
    private $db;
    private $administrador;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->administrador = new Administrador($this->db);
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function mostrarLogin() {
        if (isset($_SESSION['admin_id'])) {
            header('Location: clientes.php');
            exit();
        }
        include '../vistas/auth/login.php';
    }

    public function procesarLogin() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $email = $_POST['email'];
            $password = $_POST['password'];
            if (empty($email) || empty($password)) {
                $_SESSION['error'] = "Por favor complete todos los campos";
                header('Location: login.php');
                exit();
            }
            if ($this->administrador->login($email, $password)) {
                $_SESSION['admin_id'] = $this->administrador->dni;
                $_SESSION['admin_nombre'] = $this->administrador->nombre;
                $_SESSION['admin_email'] = $this->administrador->email;
                header('Location: clientes.php');
                exit();
            } else {
                $_SESSION['error'] = "Credenciales incorrectas";
                header('Location: login.php');
                exit();
            }
        }
    }

    public function logout() {
        session_start();
        session_destroy();
        header('Location: login.php');
        exit();
    }

    public static function verificarSesion() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['admin_id'])) {
            header('Location: login.php');
            exit();
        }
    }
}
