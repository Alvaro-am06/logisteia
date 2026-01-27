<?php
/**
 * Modelo PresupuestoWizard.
 * 
 * Gestiona presupuestos generados desde el configurador paso a paso.
 */
class PresupuestoWizard {
    private $conn;
    private $table = "presupuestos";

    // Propiedades del presupuesto
    public $id;
    public $usuario_dni;
    public $numero_presupuesto;
    public $nombre_proyecto;
    public $descripcion_proyecto;
    public $cliente_nombre;
    public $cliente_email;
    public $categoria_principal;
    public $tiempo_estimado;
    public $presupuesto_aproximado;
    public $tecnologias_seleccionadas;
    public $fecha_inicio;
    public $plazo_entrega;
    public $prioridad;
    public $notas_adicionales;
    public $estado;
    public $fecha_creacion;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Crear un nuevo presupuesto desde el wizard.
     * @return bool|int ID del presupuesto creado o false si falla
     */
    public function crear() {
        try {
            // Generar número de presupuesto único
            $this->numero_presupuesto = $this->generarNumeroPresupuesto();

            // Convertir presupuesto_aproximado a número
            $total = 0;
            if ($this->presupuesto_aproximado) {
                // Extraer números del formato "1.000€ - 5.000€" o "3000-5000"
                preg_match('/[\d.,]+/', $this->presupuesto_aproximado, $matches);
                if (!empty($matches)) {
                    $total = floatval(str_replace(['.', ','], ['', '.'], $matches[0]));
                }
            }

            // Convertir array de tecnologías a JSON para las notas
            $tecnologias_json = is_array($this->tecnologias_seleccionadas) 
                ? json_encode($this->tecnologias_seleccionadas, JSON_UNESCAPED_UNICODE) 
                : $this->tecnologias_seleccionadas;

            // Preparar notas completas con toda la información del wizard
            $notas = "PRESUPUESTO GENERADO DESDE CONFIGURADOR\n\n";
            $notas .= "Proyecto: " . $this->nombre_proyecto . "\n";
            $notas .= "Descripción: " . $this->descripcion_proyecto . "\n";
            $notas .= "Cliente: " . $this->cliente_nombre . "\n";
            if ($this->cliente_email) {
                $notas .= "Email Cliente: " . $this->cliente_email . "\n";
            }
            $notas .= "Categoría: " . $this->categoria_principal . "\n";
            $notas .= "Tiempo estimado: " . $this->tiempo_estimado . "\n";
            $notas .= "Presupuesto: " . $this->presupuesto_aproximado . "\n";
            $notas .= "Tecnologías: " . $tecnologias_json . "\n";
            $notas .= "Fecha inicio: " . $this->fecha_inicio . "\n";
            $notas .= "Plazo entrega: " . $this->plazo_entrega . "\n";
            $notas .= "Prioridad: " . ($this->prioridad ?? 'media') . "\n";
            if ($this->notas_adicionales) {
                $notas .= "\nNotas adicionales: " . $this->notas_adicionales;
            }

            // Insertar presupuesto SIN cliente_id ni proyecto_id (quedan en NULL)
            $query = "INSERT INTO " . $this->table . " 
                      (usuario_dni, numero_presupuesto, estado, validez_dias, total, notas) 
                      VALUES 
                      (:usuario_dni, :numero_presupuesto, :estado, :validez_dias, :total, :notas)";
            
            $stmt = $this->conn->prepare($query);
            
            $parametros = [
                ':usuario_dni' => $this->usuario_dni,
                ':numero_presupuesto' => $this->numero_presupuesto,
                ':estado' => $this->estado ?? 'borrador',
                ':validez_dias' => 30,
                ':total' => $total,
                ':notas' => $notas
            ];
            
            if ($stmt->execute($parametros)) {
                return $this->conn->lastInsertId();
            }
            
            return false;

        } catch (Exception $e) {
            error_log("Error en PresupuestoWizard::crear: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crear o buscar un cliente por nombre.
     * @return int|bool ID del cliente o false si falla
     */
    private function crearOBuscarCliente() {
        // Buscar si ya existe un cliente con ese nombre para este usuario
        $query = "SELECT id FROM clientes WHERE jefe_dni = :jefe_dni AND nombre = :nombre LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':jefe_dni' => $this->usuario_dni,
            ':nombre' => $this->cliente_nombre
        ]);
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['id'];
        }

        // Si no existe, crear nuevo cliente
        $query = "INSERT INTO clientes (jefe_dni, nombre, email, activo) 
                  VALUES (:jefe_dni, :nombre, :email, 1)";
        $stmt = $this->conn->prepare($query);
        
        // Generar email temporal si no tenemos uno
        $email = strtolower(str_replace(' ', '.', $this->cliente_nombre)) . '@cliente.temp';
        
        if ($stmt->execute([
            ':jefe_dni' => $this->usuario_dni,
            ':nombre' => $this->cliente_nombre,
            ':email' => $email
        ])) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }

    /**
     * Crear un proyecto basado en los datos del wizard.
     * @param int $cliente_id
     * @return int|bool ID del proyecto o false si falla
     */
    private function crearProyecto($cliente_id) {
        // Generar código único para el proyecto
        $codigo = 'PRY-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        // Convertir tecnologías a JSON
        $tecnologias_json = is_array($this->tecnologias_seleccionadas) 
            ? json_encode($this->tecnologias_seleccionadas, JSON_UNESCAPED_UNICODE) 
            : $this->tecnologias_seleccionadas;

        $query = "INSERT INTO proyectos 
                  (codigo, nombre, descripcion, jefe_dni, cliente_id, estado, fecha_inicio, 
                   tecnologias, notas, precio_total) 
                  VALUES 
                  (:codigo, :nombre, :descripcion, :jefe_dni, :cliente_id, :estado, :fecha_inicio,
                   :tecnologias, :notas, :precio_total)";
        
        $stmt = $this->conn->prepare($query);
        
        // Preparar notas del proyecto
        $notas_proyecto = "Proyecto creado desde configurador de presupuestos\n";
        $notas_proyecto .= "Categoría: " . $this->categoria_principal . "\n";
        $notas_proyecto .= "Tiempo estimado: " . $this->tiempo_estimado . "\n";
        $notas_proyecto .= "Plazo entrega: " . $this->plazo_entrega . "\n";
        $notas_proyecto .= "Prioridad: " . ($this->prioridad ?? 'media');
        if ($this->notas_adicionales) {
            $notas_proyecto .= "\n\n" . $this->notas_adicionales;
        }

        // Calcular precio total estimado
        $precio_total = 0;
        if ($this->presupuesto_aproximado) {
            preg_match('/[\d.,]+/', $this->presupuesto_aproximado, $matches);
            if (!empty($matches)) {
                $precio_total = floatval(str_replace(['.', ','], ['', '.'], $matches[0]));
            }
        }

        if ($stmt->execute([
            ':codigo' => $codigo,
            ':nombre' => $this->nombre_proyecto,
            ':descripcion' => $this->descripcion_proyecto,
            ':jefe_dni' => $this->usuario_dni,
            ':cliente_id' => $cliente_id,
            ':estado' => 'creado',
            ':fecha_inicio' => $this->fecha_inicio,
            ':tecnologias' => $tecnologias_json,
            ':notas' => $notas_proyecto,
            ':precio_total' => $precio_total
        ])) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }

    /**
     * Obtener todos los presupuestos de un usuario.
     * @param string $dni
     * @return PDOStatement
     */
    public function obtenerPorUsuario($dni) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE usuario_dni = :dni 
                  ORDER BY fecha_creacion DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':dni' => $dni]);
        return $stmt;
    }

    /**
     * Obtener un presupuesto por su número.
     * @param string $numero
     * @return array|bool
     */
    public function obtenerPorNumero($numero) {
        $query = "SELECT * FROM " . $this->table . " WHERE numero_presupuesto = :numero";
        $stmt = $this->conn->prepare($query);
        
        if ($stmt->execute([':numero' => $numero])) {
            if ($stmt->rowCount() > 0) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
        }
        return false;
    }

    /**
     * Actualizar estado de un presupuesto.
     * @param string $numero
     * @param string $estado
     * @return bool
     */
    public function actualizarEstado($numero, $estado) {
        $query = "UPDATE " . $this->table . " 
                  SET estado = :estado 
                  WHERE numero_presupuesto = :numero";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':estado' => $estado,
            ':numero' => $numero
        ]);
    }

    /**
     * Eliminar un presupuesto.
     * @param string $numero
     * @return bool
     */
    public function eliminar($numero) {
        $query = "DELETE FROM " . $this->table . " WHERE numero_presupuesto = :numero";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':numero' => $numero]);
    }

    /**
     * Generar número de presupuesto único.
     * Formato: PRW-YYYYMMDD-XXXX
     * @return string
     */
    private function generarNumeroPresupuesto() {
        $fecha = date('Ymd');
        $prefijo = "PRW-{$fecha}-";
        
        // Buscar el último número del día
        $query = "SELECT numero_presupuesto FROM " . $this->table . " 
                  WHERE numero_presupuesto LIKE :prefijo 
                  ORDER BY numero_presupuesto DESC LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':prefijo' => $prefijo . '%']);
        
        $consecutivo = 1;
        if ($stmt->rowCount() > 0) {
            $ultimo = $stmt->fetch(PDO::FETCH_ASSOC);
            $numero_actual = intval(substr($ultimo['numero_presupuesto'], -4));
            $consecutivo = $numero_actual + 1;
        }
        
        return $prefijo . str_pad($consecutivo, 4, '0', STR_PAD_LEFT);
    }
}
