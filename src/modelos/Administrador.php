<?php
class Administrador {
    private $conn;
    private $table = "usuarios";

    public $dni;
    public $nombre;
    public $email;
    public $contrase;
    public $rol;
    public $telefono;
    public $fecha_registro;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login($email, $password) {
        $query = "SELECT * FROM " . $this->table . " WHERE email = :email AND rol = 'administrador'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $row['contrase'])) {
                $this->dni = $row['dni'];
                $this->nombre = $row['nombre'];
                $this->email = $row['email'];
                $this->contrase = $row['contrase'];
                $this->rol = $row['rol'];
                $this->telefono = $row['telefono'];
                $this->fecha_registro = $row['fecha_registro'];
                return true;
            }
        }
        return false;
    }

    public function obtenerPorDni($dni) {
        $query = "SELECT * FROM " . $this->table . " WHERE dni = :dni AND rol = 'administrador'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':dni', $dni);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->dni = $row['dni'];
            $this->nombre = $row['nombre'];
            $this->email = $row['email'];
            $this->contrase = $row['contrase'];
            $this->rol = $row['rol'];
            $this->telefono = $row['telefono'];
            $this->fecha_registro = $row['fecha_registro'];
            return true;
        }
        return false;
    }
}
