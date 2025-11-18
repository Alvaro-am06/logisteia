<?php
/**
 * Modelo AccionesAdministrativas.
 * 
 * Gestiona el registro y consulta de acciones realizadas por administradores
 * sobre usuarios del sistema. Sirve como auditoría y trazabilidad de operaciones.
 * 
 */

require_once 'ConexionBBDD.php';

class AccionesAdministrativas {
    // Conexión a la base de datos
    private $db;

    /**
     * Constructor del modelo.
     * Inicializa la conexión a la base de datos.
     */
    public function __construct() {
        $database = new Conexion();
        $this->db = $database->obtener();
    }

    /**
     * Registra una acción administrativa en el sistema.
     * 
     * Almacena en el historial una acción realizada por un administrador
     * sobre un usuario específico, incluyendo la IP de origen y el motivo.
     * 
     * Tipos de acciones comunes: 'activar', 'suspender', 'eliminar', 'modificar'
     * 
     * @param string $administrador_dni DNI del administrador que realiza la acción
     * @param string $accion Tipo de acción realizada (activar, suspender, eliminar, etc.)
     * @param string|null $usuario_dni DNI del usuario sobre el que se realiza la acción
     * @param string|null $motivo Razón o justificación de la acción
     * @param string|null $ip_origen IP desde donde se realiza la acción (se obtiene automáticamente si es null)
     * @return bool true si el registro fue exitoso, false en caso contrario
     */
    public function registrar($administrador_dni, $accion, $usuario_dni = null, $motivo = null, $ip_origen = null) {
        // Validar que el administrador_dni no esté vacío
        if (empty($administrador_dni) || empty($accion)) {
            return false;
        }
        
        // Obtener IP automáticamente si no se proporciona
        if ($ip_origen === null) {
            $ip_origen = $_SERVER['REMOTE_ADDR'] ?? null;
        }
        
        // Sanitizar la IP
        if ($ip_origen !== null) {
            $ip_origen = filter_var($ip_origen, FILTER_VALIDATE_IP) ? $ip_origen : null;
        }
        
        // Preparar consulta con parámetros
        $query = "INSERT INTO acciones_administrativas (administrador_dni, accion, usuario_dni, motivo, ip_origen) 
                  VALUES (:admin, :accion, :usuario, :motivo, :ip)";
        $stmt = $this->db->prepare($query);
        
        return $stmt->execute([
            ':admin' => $administrador_dni,
            ':accion' => $accion,
            ':usuario' => $usuario_dni,
            ':motivo' => $motivo,
            ':ip' => $ip_origen
        ]);
    }

    /**
     * Obtiene todas las acciones administrativas registradas.
     * 
     * Retorna el historial completo de acciones administrativas
     * ordenadas por fecha de creación (más recientes primero).
     * 
     * @return array Array asociativo con todas las acciones administrativas
     */
    public function obtenerTodos() {
        $query = "SELECT * FROM acciones_administrativas ORDER BY creado_en DESC";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene las acciones realizadas sobre un usuario específico.
     * 
     * Retorna el historial de acciones administrativas que afectaron
     * a un usuario particular, ordenadas por fecha (más recientes primero).
     * 
     * @param string $usuario_dni DNI del usuario del que se quiere obtener el historial
     * @return array Array asociativo con las acciones sobre el usuario
     */
    public function obtenerPorUsuario($usuario_dni) {
        // Validar que el DNI no esté vacío
        if (empty($usuario_dni)) {
            return [];
        }
        
        $query = "SELECT * FROM acciones_administrativas WHERE usuario_dni = :dni ORDER BY creado_en DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':dni' => $usuario_dni]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene las acciones realizadas por un administrador específico.
     * 
     * Retorna el historial de acciones que un administrador ha realizado,
     * ordenadas por fecha (más recientes primero).
     * 
     * @param string $administrador_dni DNI del administrador del que se quiere obtener el historial
     * @return array Array asociativo con las acciones del administrador
     */
    public function obtenerPorAdministrador($administrador_dni) {
        // Validar que el DNI no esté vacío
        if (empty($administrador_dni)) {
            return [];
        }
        
        $query = "SELECT * FROM acciones_administrativas WHERE administrador_dni = :dni ORDER BY creado_en DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':dni' => $administrador_dni]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
