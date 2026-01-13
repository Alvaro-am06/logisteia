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
            // Buscar usuario por email directamente en la tabla usuarios
            $query = "SELECT dni, email, nombre, contrase, rol, estado, telefono 
                      FROM usuarios 
                      WHERE email = :email 
                      LIMIT 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([':email' => $email]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verificar si existe el usuario
            if (!$usuario) {
                echo json_encode(['error' => 'Credenciales incorrectas']);
                http_response_code(401);
                return;
            }

            // Verificar el estado del usuario
            if ($usuario['estado'] === 'baneado') {
                echo json_encode(['error' => 'Tu cuenta ha sido suspendida temporalmente']);
                http_response_code(403);
                return;
            }

            if ($usuario['estado'] === 'eliminado') {
                echo json_encode(['error' => 'Esta cuenta ha sido eliminada']);
                http_response_code(403);
                return;
            }

            // Verificar la contraseña
            if (!password_verify($password, $usuario['contrase'])) {
                echo json_encode(['error' => 'Credenciales incorrectas']);
                http_response_code(401);
                return;
            }

            // Login exitoso - preparar datos según el rol
            $datosRespuesta = [
                'dni' => $usuario['dni'],
                'nombre' => $usuario['nombre'],
                'email' => $usuario['email'],
                'telefono' => $usuario['telefono'],
                'rol' => $usuario['rol'],
                'estado' => $usuario['estado']
            ];

            // Si es trabajador, agregar información de equipos
            if ($usuario['rol'] === 'trabajador') {
                $queryEquipos = "SELECT COUNT(*) as total FROM miembros_equipo 
                                 WHERE trabajador_dni = :dni AND activo = 1";
                $stmtEquipos = $this->db->prepare($queryEquipos);
                $stmtEquipos->execute([':dni' => $usuario['dni']]);
                $equipos = $stmtEquipos->fetch(PDO::FETCH_ASSOC);
                $datosRespuesta['equipos_count'] = $equipos['total'] ?? 0;
            }

            // Si es jefe de equipo, agregar información de su equipo y proyectos
            if ($usuario['rol'] === 'jefe_equipo') {
                // Obtener equipo del jefe
                $queryEquipo = "SELECT id, nombre FROM equipos 
                                WHERE jefe_dni = :dni AND activo = 1 LIMIT 1";
                $stmtEquipo = $this->db->prepare($queryEquipo);
                $stmtEquipo->execute([':dni' => $usuario['dni']]);
                $equipo = $stmtEquipo->fetch(PDO::FETCH_ASSOC);
                
                if ($equipo) {
                    $datosRespuesta['equipo_id'] = $equipo['id'];
                    $datosRespuesta['equipo_nombre'] = $equipo['nombre'];

                    // Contar miembros del equipo
                    $queryMiembros = "SELECT COUNT(*) as total FROM miembros_equipo 
                                      WHERE equipo_id = :equipo_id AND activo = 1";
                    $stmtMiembros = $this->db->prepare($queryMiembros);
                    $stmtMiembros->execute([':equipo_id' => $equipo['id']]);
                    $miembros = $stmtMiembros->fetch(PDO::FETCH_ASSOC);
                    $datosRespuesta['miembros_count'] = $miembros['total'] ?? 0;

                    // Contar proyectos
                    $queryProyectos = "SELECT 
                                        COUNT(*) as total,
                                        SUM(CASE WHEN estado = 'en_proceso' THEN 1 ELSE 0 END) as en_proceso,
                                        SUM(CASE WHEN estado = 'finalizado' THEN 1 ELSE 0 END) as finalizados
                                       FROM proyectos 
                                       WHERE jefe_dni = :dni";
                    $stmtProyectos = $this->db->prepare($queryProyectos);
                    $stmtProyectos->execute([':dni' => $usuario['dni']]);
                    $proyectos = $stmtProyectos->fetch(PDO::FETCH_ASSOC);
                    $datosRespuesta['proyectos_total'] = $proyectos['total'] ?? 0;
                    $datosRespuesta['proyectos_en_proceso'] = $proyectos['en_proceso'] ?? 0;
                    $datosRespuesta['proyectos_finalizados'] = $proyectos['finalizados'] ?? 0;
                }
            }

            // Si es moderador, agregar estadísticas globales del sistema
            if ($usuario['rol'] === 'moderador') {
                try {
                    // Total de usuarios por rol
                    $queryUsuarios = "SELECT 
                                        COUNT(*) as total,
                                        SUM(CASE WHEN rol = 'jefe_equipo' THEN 1 ELSE 0 END) as jefes,
                                        SUM(CASE WHEN rol = 'trabajador' THEN 1 ELSE 0 END) as trabajadores,
                                        SUM(CASE WHEN estado = 'baneado' THEN 1 ELSE 0 END) as baneados,
                                        SUM(CASE WHEN estado = 'eliminado' THEN 1 ELSE 0 END) as eliminados
                                      FROM usuarios WHERE rol != 'moderador'";
                    $stmtUsuarios = $this->db->query($queryUsuarios);
                    $usuarios = $stmtUsuarios->fetch(PDO::FETCH_ASSOC);
                    $datosRespuesta['usuarios_total'] = $usuarios['total'] ?? 0;
                    $datosRespuesta['usuarios_jefes'] = $usuarios['jefes'] ?? 0;
                    $datosRespuesta['usuarios_trabajadores'] = $usuarios['trabajadores'] ?? 0;
                    $datosRespuesta['usuarios_baneados'] = $usuarios['baneados'] ?? 0;
                    $datosRespuesta['usuarios_eliminados'] = $usuarios['eliminados'] ?? 0;
                } catch (Exception $e) {
                    $datosRespuesta['usuarios_total'] = 0;
                }

                try {
                    // Total de equipos
                    $queryEquipos = "SELECT COUNT(*) as total FROM equipos WHERE activo = 1";
                    $equiposTotal = $this->db->query($queryEquipos)->fetch(PDO::FETCH_ASSOC);
                    $datosRespuesta['equipos_total'] = $equiposTotal['total'] ?? 0;
                } catch (Exception $e) {
                    $datosRespuesta['equipos_total'] = 0;
                }

                try {
                    // Total de proyectos (solo si la tabla existe)
                    $queryProyectos = "SELECT 
                                        COUNT(*) as total,
                                        SUM(CASE WHEN estado = 'planificacion' THEN 1 ELSE 0 END) as planificacion,
                                        SUM(CASE WHEN estado = 'en_proceso' THEN 1 ELSE 0 END) as en_proceso,
                                        SUM(CASE WHEN estado = 'finalizado' THEN 1 ELSE 0 END) as finalizados,
                                        SUM(CASE WHEN estado = 'cancelado' THEN 1 ELSE 0 END) as cancelados
                                       FROM proyectos";
                    $proyectosStats = $this->db->query($queryProyectos)->fetch(PDO::FETCH_ASSOC);
                    $datosRespuesta['proyectos_total'] = $proyectosStats['total'] ?? 0;
                    $datosRespuesta['proyectos_planificacion'] = $proyectosStats['planificacion'] ?? 0;
                    $datosRespuesta['proyectos_en_proceso'] = $proyectosStats['en_proceso'] ?? 0;
                    $datosRespuesta['proyectos_finalizados'] = $proyectosStats['finalizados'] ?? 0;
                    $datosRespuesta['proyectos_cancelados'] = $proyectosStats['cancelados'] ?? 0;
                } catch (Exception $e) {
                    $datosRespuesta['proyectos_total'] = 0;
                    $datosRespuesta['proyectos_planificacion'] = 0;
                    $datosRespuesta['proyectos_en_proceso'] = 0;
                    $datosRespuesta['proyectos_finalizados'] = 0;
                    $datosRespuesta['proyectos_cancelados'] = 0;
                }

                try {
                    // Baneos activos
                    $queryBaneos = "SELECT COUNT(*) as total FROM historial_baneos WHERE activo = 1";
                    $baneosActivos = $this->db->query($queryBaneos)->fetch(PDO::FETCH_ASSOC);
                    $datosRespuesta['baneos_activos'] = $baneosActivos['total'] ?? 0;
                } catch (Exception $e) {
                    $datosRespuesta['baneos_activos'] = 0;
                }

                try {
                    // Últimas acciones administrativas
                    $queryAcciones = "SELECT COUNT(*) as total FROM acciones_administrativas 
                                      WHERE fecha_hora >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                    $accionesRecientes = $this->db->query($queryAcciones)->fetch(PDO::FETCH_ASSOC);
                    $datosRespuesta['acciones_ultima_semana'] = $accionesRecientes['total'] ?? 0;
                } catch (Exception $e) {
                    $datosRespuesta['acciones_ultima_semana'] = 0;
                }
            }

            echo json_encode([
                'success' => true,
                'data' => $datosRespuesta
            ]);
            http_response_code(200);
            return;

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
     * Si es jefe de equipo, crea automáticamente su equipo.
     * 
     * @param string $dni DNI del usuario
     * @param string $nombre Nombre completo
     * @param string $email Correo electrónico
     * @param string $password Contraseña
     * @param string|null $telefono Teléfono (opcional)
     * @param string $rol Rol del usuario: 'trabajador' o 'jefe_equipo'
     * @return array Resultado del registro
     */
    public function procesarRegistro($dni, $nombre, $email, $password, $telefono = null, $rol = 'trabajador') {
        try {
            // Validar rol
            if (!in_array($rol, ['trabajador', 'jefe_equipo'])) {
                return ['success' => false, 'error' => 'Rol inválido'];
            }

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

            // Hash de la contraseña
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insertar usuario directamente con el rol correcto
            $query = "INSERT INTO usuarios (dni, email, nombre, contrase, rol, telefono, estado) 
                      VALUES (:dni, :email, :nombre, :contrase, :rol, :telefono, 'activo')";
            
            $stmt = $this->db->prepare($query);
            $resultado = $stmt->execute([
                ':dni' => $dni,
                ':email' => $email,
                ':nombre' => $nombre,
                ':contrase' => $hashedPassword,
                ':rol' => $rol,
                ':telefono' => $telefono
            ]);

            if (!$resultado) {
                return ['success' => false, 'error' => 'Error al crear el usuario'];
            }

            // Si es jefe de equipo, crear su equipo automáticamente
            if ($rol === 'jefe_equipo') {
                $nombreEquipo = "Equipo de $nombre";
                $queryEquipo = "INSERT INTO equipos (nombre, descripcion, jefe_dni, activo) 
                                VALUES (:nombre, :descripcion, :jefe_dni, 1)";
                
                $stmtEquipo = $this->db->prepare($queryEquipo);
                $stmtEquipo->execute([
                    ':nombre' => $nombreEquipo,
                    ':descripcion' => "Equipo gestionado por $nombre",
                    ':jefe_dni' => $dni
                ]);
            }

            return ['success' => true, 'rol' => $rol];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Error interno del servidor: ' . $e->getMessage()];
        }
    }
}