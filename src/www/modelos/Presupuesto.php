<?php
/**
 * Modelo Presupuesto.
 * 
 * Gestiona la creación y consulta de presupuestos con sus detalles.
 */
class Presupuesto {
    private $conn;
    private $table = "presupuestos";
    private $tableDetalle = "detalle_presupuesto";

    public $id_presupuesto;
    public $usuario_dni;
    public $numero_presupuesto;
    public $fecha_creacion;
    public $estado;
    public $validez_dias;
    public $total;
    public $notas;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Crear un nuevo presupuesto con sus detalles.
     * @param array $detalles Array de servicios con cantidad, precio y comentario
     * @return bool|int ID del presupuesto creado o false si falla
     */
    public function crear($detalles = []) {
        try {
            // Iniciar transacción
            $this->conn->beginTransaction();

            // Generar número de presupuesto único
            $this->numero_presupuesto = $this->generarNumeroPresupuesto();

            // Insertar presupuesto
            $query = "INSERT INTO " . $this->table . " 
                      (usuario_dni, numero_presupuesto, estado, validez_dias, total, notas) 
                      VALUES (:usuario_dni, :numero_presupuesto, :estado, :validez_dias, :total, :notas)";
            $stmt = $this->conn->prepare($query);
            
            $parametros = [
                ':usuario_dni' => $this->usuario_dni,
                ':numero_presupuesto' => $this->numero_presupuesto,
                ':estado' => $this->estado ?? 'borrador',
                ':validez_dias' => $this->validez_dias ?? 30,
                ':total' => $this->total,
                ':notas' => $this->notas
            ];
            
            if (!$stmt->execute($parametros)) {
                $this->conn->rollBack();
                return false;
            }

            $presupuesto_id = $this->conn->lastInsertId();

            // Insertar detalles del presupuesto
            if (!empty($detalles)) {
                $queryDetalle = "INSERT INTO " . $this->tableDetalle . " 
                                 (numero_presupuesto, presupuesto_id, servicio_nombre, cantidad, preci, comentario) 
                                 VALUES (:numero_presupuesto, :presupuesto_id, :servicio_nombre, :cantidad, :precio, :comentario)";
                $stmtDetalle = $this->conn->prepare($queryDetalle);

                foreach ($detalles as $detalle) {
                    $parametrosDetalle = [
                        ':numero_presupuesto' => $this->numero_presupuesto,
                        ':presupuesto_id' => $presupuesto_id,
                        ':servicio_nombre' => $detalle['servicio_nombre'],
                        ':cantidad' => $detalle['cantidad'],
                        ':precio' => $detalle['precio'],
                        ':comentario' => $detalle['comentario'] ?? null
                    ];
                    
                    if (!$stmtDetalle->execute($parametrosDetalle)) {
                        $this->conn->rollBack();
                        return false;
                    }
                }
            }

            // Confirmar transacción
            $this->conn->commit();
            return $presupuesto_id;

        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    /**
     * Obtener todos los presupuestos de un usuario.
     * @param string $dni
     * @return PDOStatement
     */
    public function obtenerPorUsuario($dni) {
        $query = "SELECT * FROM " . $this->table . " WHERE usuario_dni = :dni ORDER BY fecha_creacion DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':dni' => $dni]);
        return $stmt;
    }

    /**
     * Obtener un presupuesto por su número.
     * @param string $numero
     * @return bool
     */
    public function obtenerPorNumero($numero) {
        $query = "SELECT * FROM " . $this->table . " WHERE numero_presupuesto = :numero";
        $stmt = $this->conn->prepare($query);
        
        if ($stmt->execute([':numero' => $numero])) {
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $this->id_presupuesto = $row['id_presupuesto'];
                $this->usuario_dni = $row['usuario_dni'];
                $this->numero_presupuesto = $row['numero_presupuesto'];
                $this->fecha_creacion = $row['fecha_creacion'];
                $this->estado = $row['estado'];
                $this->validez_dias = $row['validez_dias'];
                $this->total = $row['total'];
                $this->notas = $row['notas'];
                return true;
            }
        }
        return false;
    }

    /**
     * Obtener detalles de un presupuesto.
     * @param string $numero_presupuesto
     * @return array
     */
    public function obtenerDetalles($numero_presupuesto) {
        $query = "SELECT * FROM " . $this->tableDetalle . " WHERE numero_presupuesto = :numero";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':numero' => $numero_presupuesto]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Actualizar estado de un presupuesto.
     * @param string $numero
     * @param string $estado
     * @return bool
     */
    public function actualizarEstado($numero, $estado) {
        $query = "UPDATE " . $this->table . " SET estado = :estado WHERE numero_presupuesto = :numero";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':estado' => $estado, ':numero' => $numero]);
    }

    /**
     * Generar número de presupuesto único.
     * Formato: PRES-YYYYMMDD-XXXX
     * @return string
     */
    private function generarNumeroPresupuesto() {
        $fecha = date('Ymd');
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE numero_presupuesto LIKE :patron";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':patron' => "PRES-{$fecha}-%"]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $consecutivo = str_pad($row['total'] + 1, 4, '0', STR_PAD_LEFT);
        return "PRES-{$fecha}-{$consecutivo}";
    }

    /**
     * Contar presupuestos de un usuario.
     * @param string $dni
     * @return int
     */
    public function contarPorUsuario($dni) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE usuario_dni = :dni";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':dni' => $dni]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] ?? 0;
    }
}
