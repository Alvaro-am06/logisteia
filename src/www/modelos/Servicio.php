<?php
/**
 * Modelo Servicio.
 * 
 * Gestiona los servicios disponibles para configurar presupuestos.
 */
class Servicio {
    private $conn;
    private $table = "servicios";

    public $nombre;
    public $precio_base;
    public $descripcion;
    public $categoria_nombre;
    public $esta_activo;
    public $actualizado_en;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obtener todos los servicios activos.
     * @return PDOStatement
     */
    public function obtenerActivos() {
        $query = "SELECT * FROM " . $this->table . " WHERE esta_activo = 1 ORDER BY categoria_nombre, nombre";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Obtener todos los servicios.
     * @return PDOStatement
     */
    public function obtenerTodos() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY categoria_nombre, nombre";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Obtener un servicio por nombre.
     * @param string $nombre
     * @return bool
     */
    public function obtenerPorNombre($nombre) {
        $query = "SELECT * FROM " . $this->table . " WHERE nombre = :nombre";
        $stmt = $this->conn->prepare($query);
        
        if ($stmt->execute([':nombre' => $nombre])) {
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $this->nombre = $row['nombre'];
                $this->precio_base = $row['precio_base'];
                $this->descripcion = $row['descripcion'];
                $this->categoria_nombre = $row['categoria_nombre'];
                $this->esta_activo = $row['esta_activo'];
                $this->actualizado_en = $row['actualizado_en'];
                return true;
            }
        }
        return false;
    }

    /**
     * Crear un nuevo servicio.
     * @return bool
     */
    public function crear() {
        $query = "INSERT INTO " . $this->table . " (nombre, precio_base, descripcion, categoria_nombre, esta_activo) 
                  VALUES (:nombre, :precio_base, :descripcion, :categoria_nombre, :esta_activo)";
        $stmt = $this->conn->prepare($query);
        
        $parametros = [
            ':nombre' => $this->nombre,
            ':precio_base' => $this->precio_base,
            ':descripcion' => $this->descripcion,
            ':categoria_nombre' => $this->categoria_nombre,
            ':esta_activo' => $this->esta_activo ?? 1
        ];
        
        return $stmt->execute($parametros);
    }

    /**
     * Actualizar un servicio.
     * @return bool
     */
    public function actualizar() {
        $query = "UPDATE " . $this->table . " 
                  SET precio_base = :precio_base, 
                      descripcion = :descripcion, 
                      categoria_nombre = :categoria_nombre, 
                      esta_activo = :esta_activo,
                      actualizado_en = CURRENT_TIMESTAMP
                  WHERE nombre = :nombre";
        $stmt = $this->conn->prepare($query);
        
        $parametros = [
            ':nombre' => $this->nombre,
            ':precio_base' => $this->precio_base,
            ':descripcion' => $this->descripcion,
            ':categoria_nombre' => $this->categoria_nombre,
            ':esta_activo' => $this->esta_activo
        ];
        
        return $stmt->execute($parametros);
    }

    /**
     * Activar o desactivar un servicio.
     * @param string $nombre
     * @param int $activo
     * @return bool
     */
    public function cambiarEstado($nombre, $activo) {
        $query = "UPDATE " . $this->table . " SET esta_activo = :activo WHERE nombre = :nombre";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':activo' => $activo, ':nombre' => $nombre]);
    }
}
