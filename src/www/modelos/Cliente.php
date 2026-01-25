<?php
class Cliente {
    private $conn;
    private $table = "usuarios";

    public $dni;
    public $email;
    public $nombre;
    public $contrase;
    public $telefono;
    public $fecha_registro;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function crear() {
        $query = "INSERT INTO " . $this->table . " (dni, email, nombre, contrase, rol, telefono) VALUES (:dni, :email, :nombre, :contrase, 'registrado', :telefono)";
        $stmt = $this->conn->prepare($query);
        $parametros = [
            ':dni' => $this->dni,
            ':email' => $this->email,
            ':nombre' => $this->nombre,
            ':contrase' => $this->contrase,
            ':telefono' => $this->telefono
        ];
        if ($stmt->execute($parametros)) {
            return true;
        }
        return false;
    }

    public function obtenerTodos() {
        $query = "SELECT * FROM " . $this->table . " WHERE rol = 'registrado' ORDER BY fecha_registro DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function obtenerPorDni($dni) {
        $query = "SELECT * FROM " . $this->table . " WHERE dni = :dni AND rol = 'registrado'";
        $stmt = $this->conn->prepare($query);
        if ($stmt->execute([':dni' => $dni])) {
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $this->dni = $row['dni'];
                $this->email = $row['email'];
                $this->nombre = $row['nombre'];
                $this->contrase = $row['contrase'];
                $this->telefono = $row['telefono'];
                $this->fecha_registro = $row['fecha_registro'];
                return true;
            }
        }
        return false;
    }

    public function actualizar() {
        $query = "UPDATE " . $this->table . " SET email = :email, nombre = :nombre, contrase = :contrase, telefono = :telefono WHERE dni = :dni AND rol = 'registrado'";
        $stmt = $this->conn->prepare($query);
        $parametros = [
            ':email' => $this->email,
            ':nombre' => $this->nombre,
            ':contrase' => $this->contrase,
            ':telefono' => $this->telefono,
            ':dni' => $this->dni
        ];
        if ($stmt->execute($parametros)) {
            return true;
        }
        return false;
    }

    public function eliminar($dni) {
        $query = "DELETE FROM " . $this->table . " WHERE dni = :dni AND rol = 'registrado'";
        $stmt = $this->conn->prepare($query);
        if ($stmt->execute([':dni' => $dni])) {
            return true;
        }
        return false;
    }

    public function buscar($termino) {
        $termino = "%{$termino}%";
        $query = "SELECT * FROM " . $this->table . " WHERE rol = 'registrado' AND (nombre LIKE :termino OR email LIKE :termino OR telefono LIKE :termino) ORDER BY fecha_registro DESC";
        $stmt = $this->conn->prepare($query);
        if ($stmt->execute([':termino' => $termino])) {
            return $stmt;
        }
        return null;
    }
}
