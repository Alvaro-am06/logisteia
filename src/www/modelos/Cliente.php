<?php
class Cliente {
    private $conn;
    private $table = "clientes";

    public $id;
    public $jefe_dni;
    public $nombre;
    public $empresa;
    public $email;
    public $telefono;
    public $direccion;
    public $cif_nif;
    public $notas;
    public $fecha_registro;
    public $activo;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function crear() {
        $query = "INSERT INTO " . $this->table . " (jefe_dni, nombre, empresa, email, telefono, direccion, cif_nif, notas, activo) 
                  VALUES (:jefe_dni, :nombre, :empresa, :email, :telefono, :direccion, :cif_nif, :notas, 1)";
        $stmt = $this->conn->prepare($query);
        $parametros = [
            ':jefe_dni' => $this->jefe_dni,
            ':nombre' => $this->nombre,
            ':empresa' => $this->empresa ?? null,
            ':email' => $this->email,
            ':telefono' => $this->telefono ?? null,
            ':direccion' => $this->direccion ?? null,
            ':cif_nif' => $this->cif_nif ?? null,
            ':notas' => $this->notas ?? null
        ];
        if ($stmt->execute($parametros)) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function obtenerTodos() {
        $query = "SELECT * FROM " . $this->table . " WHERE activo = 1 ORDER BY fecha_registro DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function obtenerPorId($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id AND activo = 1";
        $stmt = $this->conn->prepare($query);
        if ($stmt->execute([':id' => $id])) {
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $this->mapearDatos($row);
                return true;
            }
        }
        return false;
    }

    public function obtenerPorEmail($email) {
        $query = "SELECT * FROM " . $this->table . " WHERE email = :email AND activo = 1 LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->mapearDatos($row);
            return true;
        }
        return false;
    }

    public function obtenerPorJefe($jefe_dni) {
        $query = "SELECT * FROM " . $this->table . " WHERE jefe_dni = :jefe_dni AND activo = 1 ORDER BY fecha_registro DESC";
        $stmt = $this->conn->prepare($query);
        if ($stmt->execute([':jefe_dni' => $jefe_dni])) {
            return $stmt;
        }
        return null;
    }

    public function actualizar() {
        $query = "UPDATE " . $this->table . " 
                  SET nombre = :nombre, empresa = :empresa, email = :email, telefono = :telefono, 
                      direccion = :direccion, cif_nif = :cif_nif, notas = :notas
                  WHERE id = :id AND activo = 1";
        $stmt = $this->conn->prepare($query);
        $parametros = [
            ':nombre' => $this->nombre,
            ':empresa' => $this->empresa ?? null,
            ':email' => $this->email,
            ':telefono' => $this->telefono ?? null,
            ':direccion' => $this->direccion ?? null,
            ':cif_nif' => $this->cif_nif ?? null,
            ':notas' => $this->notas ?? null,
            ':id' => $this->id
        ];
        if ($stmt->execute($parametros)) {
            return true;
        }
        return false;
    }

    public function eliminar($id) {
        // Soft delete: marcar como inactivo
        $query = "UPDATE " . $this->table . " SET activo = 0 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        if ($stmt->execute([':id' => $id])) {
            return true;
        }
        return false;
    }

    public function buscar($termino) {
        $termino = "%{$termino}%";
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE activo = 1 AND (nombre LIKE :termino OR email LIKE :termino OR empresa LIKE :termino) 
                  ORDER BY fecha_registro DESC";
        $stmt = $this->conn->prepare($query);
        if ($stmt->execute([':termino' => $termino])) {
            return $stmt;
        }
        return null;
    }

    private function mapearDatos($row) {
        $this->id = $row['id'] ?? null;
        $this->jefe_dni = $row['jefe_dni'] ?? null;
        $this->nombre = $row['nombre'] ?? null;
        $this->empresa = $row['empresa'] ?? null;
        $this->email = $row['email'] ?? null;
        $this->telefono = $row['telefono'] ?? null;
        $this->direccion = $row['direccion'] ?? null;
        $this->cif_nif = $row['cif_nif'] ?? null;
        $this->notas = $row['notas'] ?? null;
        $this->fecha_registro = $row['fecha_registro'] ?? null;
        $this->activo = $row['activo'] ?? 1;
    }
}

