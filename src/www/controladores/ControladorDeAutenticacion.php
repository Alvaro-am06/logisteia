<?php
/**
 * Controlador de Autenticación.
 * 
 * Gestiona el proceso de inicio y cierre de sesión de administradores,
 * validación de credenciales y control de acceso.
 * 
 */

require_once 'modelos/ConexionBBDD.php';
require_once 'modelos/Administrador.php';

class ControladordeAutenticacion {
    // Conexión a la base de datos
    private $db;

    // Modelo de administrador
    private $administrador;

    /**
     * Constructor del controlador.
     * Inicializa la conexión a la base de datos y el modelo de administrador.
     */
    public function __construct() {
        $database = new Conexion();
        $this->db = $database->obtener();
        $this->administrador = new Administrador($this->db);
        
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Procesa el formulario de inicio de sesión.
     * 
     * Valida las credenciales del usuario contra la base de datos.
     * Si son correctas, crea una sesión y redirige al panel de administración.
     * Si son incorrectas o hay errores, redirige al formulario con mensaje de error.
     * 
     * @return void
     */
    public function procesarLogin() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Sanitizar y validar entradas
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'] ?? '';
            
            // Validar formato de email
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error'] = "Por favor ingrese un email válido";
                header('Location: index.php');
                exit();
            }
            
            // Validar que la contraseña no esté vacía
            if (empty($password)) {
                $_SESSION['error'] = "Por favor complete todos los campos";
                header('Location: index.php');
                exit();
            }
            
            // Intentar autenticar al usuario
            if ($this->administrador->login($email, $password)) {
                // Regenerar ID de sesión para prevenir session fixation
                session_regenerate_id(true);
                
                // Almacenar datos del administrador en la sesión
                $_SESSION['admin_id'] = $this->administrador->dni;
                $_SESSION['admin_nombre'] = $this->administrador->nombre;
                $_SESSION['admin_email'] = $this->administrador->email;
                
                header('Location: vistas/panel_admin.php');
                exit();
            } else {
                $_SESSION['error'] = "Credenciales incorrectas";
                header('Location: index.php');
                exit();
            }
        }
    }

    /**
     * Verifica que exista una sesión activa de administrador.
     * 
     * Si no hay sesión activa, redirige al formulario de login.
     * Este método debe ser llamado en páginas que requieren autenticación.
     * 
     * @return void
     */
    public static function verificarSesion() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['admin_id'])) {
            header('Location: ../index.php');
            exit();
        }
    }

    /**
     * Procesa el login desde API (devuelve JSON)
     * 
     * @param string $email Email del administrador
     * @param string $password Contraseña
     * @return array Resultado del login en formato JSON
     */
    public function procesarLoginAPI($email, $password) {
        // Validar formato de email
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'error' => 'Por favor ingrese un email válido'
            ];
        }
        
        // Validar que la contraseña no esté vacía
        if (empty($password)) {
            return [
                'success' => false,
                'error' => 'Por favor complete todos los campos'
            ];
        }
        
        // Intentar autenticar al usuario
        if ($this->administrador->login($email, $password)) {
            // Regenerar ID de sesión para prevenir session fixation
            session_regenerate_id(true);
            
            // Almacenar datos del administrador en la sesión
            $_SESSION['admin_id'] = $this->administrador->dni;
            $_SESSION['admin_nombre'] = $this->administrador->nombre;
            $_SESSION['admin_email'] = $this->administrador->email;
            
            return [
                'success' => true,
                'data' => [
                    'id' => $this->administrador->dni,
                    'nombre' => $this->administrador->nombre,
                    'email' => $this->administrador->email
                ]
            ];
        } else {
            return [
                'success' => false,
                'error' => 'Credenciales incorrectas'
            ];
        }
    }

    /**
     * Cerrar sesión (API)
     */
    public function logout() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();
    }
}
