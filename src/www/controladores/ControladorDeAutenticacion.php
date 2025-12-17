<?php
/**
 * Controlador de Autenticación.
 * 
 * Gestiona el proceso de inicio y cierre de sesión de administradores,
 * validación de credenciales y control de acceso.
 */

// Cargar dependencias con rutas absolutas usando __DIR__
// __DIR__ contiene la ruta absoluta del directorio actual
require_once __DIR__ . '/../modelos/ConexionBBDD.php';
require_once __DIR__ . '/../modelos/Usuarios.php';
require_once __DIR__ . '/../modelos/Administrador.php';
require_once __DIR__ . '/../modelos/Cliente.php';

class ControladorDeAutenticacion {
    // Conexión a la base de datos
    private $db;

    // Modelos
    private $administrador;
    private $registrado;

    /**
     * Constructor del controlador.
     * Inicializa la conexión a la base de datos y el modelo de administrador.
     */
    public function __construct() {
        $database = new Conexion();
        $this->db = $database->obtener();
        $this->administrador = new Administrador($this->db);
        $this->registrado = new Cliente($this->db);
        
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
     * API endpoint para login.
     * Devuelve JSON para integración con frontend SPA (Angular).
     * 
     * Este método valida las credenciales y devuelve una respuesta JSON
     * con el resultado de la autenticación.
     */
    public function apiLogin() {
        // Manejar preflight OPTIONS para CORS
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            return;
        }

        // Verificar que sea una petición POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['error' => 'Método no permitido']);
            http_response_code(405);
            return;
        }

        // Obtener los datos JSON del cuerpo de la petición
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validar que se recibieron datos válidos
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['error' => 'JSON inválido']);
            http_response_code(400);
            return;
        }

        // Extraer email y password del JSON
        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';

        // Validar que ambos campos estén presentes
        if (empty($email) || empty($password)) {
            echo json_encode(['error' => 'Email y contraseña requeridos']);
            http_response_code(400);
            return;
        }

        try {
            // PASO 1: Intentar login como administrador
            if ($this->administrador->login($email, $password)) {
                // Login exitoso como administrador
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'dni' => $this->administrador->dni,
                        'nombre' => $this->administrador->nombre,
                        'email' => $this->administrador->email,
                        'rol' => 'administrador' // Agregar el rol para que el frontend sepa qué tipo de usuario es
                    ]
                ]);
                http_response_code(200);
                return; // Importante: salir del método aquí
            }

            // PASO 2: Si no es admin, intentar login como cliente/registrado
            // Primero buscamos si existe un usuario con ese email
            if ($this->registrado->obtenerPorEmail($email)) {
                // Usuario encontrado, ahora verificamos la contraseña
                if (password_verify($password, $this->registrado->contrase)) {
                    // Login exitoso como cliente
                    $proyectosCreados = $this->registrado->contarProyectosCreados($this->registrado->dni);
                    $proyectosCompletados = $this->registrado->contarProyectosCompletados($this->registrado->dni);
                    echo json_encode([
                        'success' => true,
                        'data' => [
                            'dni' => $this->registrado->dni,
                            'nombre' => $this->registrado->nombre,
                            'email' => $this->registrado->email,
                            'rol' => 'registrado',
                            'proyectos_creados' => $proyectosCreados,
                            'proyectos_completados' => $proyectosCompletados
                        ]
                    ]);
                    http_response_code(200);
                    return; // Salir del método
                } else {
                    // Contraseña incorrecta para el cliente
                    echo json_encode(['error' => 'Credenciales incorrectas']);
                    http_response_code(401);
                    return;
                }
            } else {
                // No se encontró ningún usuario con ese email (ni admin ni cliente)
                echo json_encode(['error' => 'Credenciales incorrectas']);
                http_response_code(401);
                return;
            }

        } catch (Exception $e) {
            // Capturar cualquier error de base de datos o excepción
            echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
            http_response_code(500);
            return;
        }
    }

    /**
     * Procesa el registro de un nuevo usuario.
     * 
     * Valida los datos y crea un nuevo usuario registrado.
     * 
     * @param string $dni DNI del usuario
     * @param string $nombre Nombre completo
     * @param string $email Correo electrónico
     * @param string $password Contraseña
     * @param string|null $telefono Teléfono (opcional)
     * @return array Resultado del registro
     */
    public function procesarRegistro($dni, $nombre, $email, $password, $telefono = null) {
        try {
            // Verificar si el email ya existe
            $this->registrado->email = $email;
            if ($this->registrado->obtenerPorEmail($email)) {
                return ['success' => false, 'error' => 'El email ya está registrado'];
            }

            // Verificar si el DNI ya existe
            $this->registrado->dni = $dni;
            if ($this->registrado->obtenerPorDni($dni)) {
                return ['success' => false, 'error' => 'El DNI ya está registrado'];
            }

            // Asignar datos al modelo
            $this->registrado->dni = $dni;
            $this->registrado->nombre = $nombre;
            $this->registrado->email = $email;
            $this->registrado->telefono = $telefono;

            // Hash de la contraseña
            $this->registrado->contrase = password_hash($password, PASSWORD_DEFAULT);

            // Crear usuario
            if ($this->registrado->crear()) {
                return ['success' => true];
            } else {
                return ['success' => false, 'error' => 'Error al crear el usuario'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Error interno del servidor: ' . $e->getMessage()];
        }
    }
}