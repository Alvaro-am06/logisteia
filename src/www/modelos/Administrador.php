<?php
/**
 * Modelo Administrador.
 * 
 * Representa a un usuario administrador del sistema y gestiona
 * las operaciones relacionadas con la autenticación y consulta de administradores.
 */
class Administrador {
    // Conexión a la base de datos
    private $conn;
    // Nombre de la tabla en la base de datos
    private $table = "usuarios";
    // DNI del administrador
    public $dni;
    // Nombre completo del administrador
    public $nombre;
    // Email del administrador
    public $email;
    // Contraseña del administrador
    public $password;
    // Rol del usuario (administrador)
    public $rol;
    // Teléfono del administrador
    public $telefono;
    // Fecha de registro
    public $fecha_registro;

    /**
     * Constructor del modelo.
     * 
     * @param PDO $db Conexión a la base de datos
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Autentica a un administrador mediante email y contraseña.
     * 
     * Verifica que el email exista, que el rol sea 'administrador'
     * y que la contraseña coincida. Si la autenticación es exitosa,
     * carga los datos del administrador en las propiedades del objeto.
     * 
     * NOTA: Actualmente usa comparación directa de contraseñas.
     * En producción debería usar password_verify() con hash bcrypt.
     * 
     * @param string $email Email del administrador
     * @param string $password Contraseña en texto plano
     * @return bool true si la autenticación es exitosa, false en caso contrario
     */
    public function login($email, $password) {
        // Validar formato de email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        
        // Preparar consulta con parámetros para prevenir SQL injection
        $query = "SELECT * FROM " . $this->table . " WHERE email = :email AND rol = 'administrador'";
        $stmt = $this->conn->prepare($query);
        
        if ($stmt->execute([':email' => $email])) {
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Verificar contraseña usando hash bcrypt
                if (password_verify($password, $row['contrase'])) {
                    // Cargar datos del administrador
                    $this->dni = $row['dni'];
                    $this->nombre = $row['nombre'];
                    $this->email = $row['email'];
                    $this->password = $row['contrase'];
                    $this->rol = $row['rol'];
                    $this->telefono = $row['telefono'] ?? null;
                    $this->fecha_registro = $row['fecha_registro'];
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Obtiene un administrador por su DNI.
     * 
     * Busca en la base de datos un usuario con el DNI especificado
     * y rol 'administrador'. Si lo encuentra, carga sus datos en el objeto.
     * 
     * @param string $dni DNI del administrador a buscar
     * @return bool true si se encuentra el administrador, false en caso contrario
     */
    public function obtenerPorDni($dni) {
        // Validar formato de DNI (longitud básica)
        if (empty($dni) || strlen($dni) < 8) {
            return false;
        }
        
        // Preparar consulta con parámetros para prevenir SQL injection
        $query = "SELECT * FROM " . $this->table . " WHERE dni = :dni AND rol = 'administrador'";
        $stmt = $this->conn->prepare($query);
        
        if ($stmt->execute([':dni' => $dni])) {
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Cargar datos del administrador
                $this->dni = $row['dni'];
                $this->nombre = $row['nombre'];
                $this->email = $row['email'];
                $this->password = $row['contrase'];
                $this->rol = $row['rol'];
                $this->telefono = $row['telefono'] ?? null;
                $this->fecha_registro = $row['fecha_registro'];
                return true;
            }
        }
        
        return false;
    }
}
