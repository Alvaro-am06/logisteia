<?php
/**
 * Modelo para manejar operaciones con proyectos
 */
class Proyecto {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Crear un nuevo proyecto con asignaciones de trabajadores
     * NOTA: Inserta en tabla presupuestos
     */
    public function crearProyecto($datos) {
        try {
            $this->conn->beginTransaction();

            // Generar número de presupuesto
            $numeroPresupuesto = $datos['codigo'] ?? $this->generarNumeroPresupuesto();

            // Insertar en presupuestos
            $sql = "INSERT INTO presupuestos (
                usuario_dni, numero_presupuesto, cliente_id, estado, validez_dias,
                total, notas, fecha_creacion
            ) VALUES (
                :usuario_dni, :numero_presupuesto, :cliente_id, 'borrador', 30,
                :total, :notas, NOW()
            )";

            $notas = "PROYECTO: " . ($datos['nombre'] ?? '') . "\n" .
                     "Descripción: " . ($datos['descripcion'] ?? '') . "\n" .
                     "Tecnologías: " . ($datos['tecnologias'] ?? '') . "\n" .
                     "Repositorio: " . ($datos['repositorio_github'] ?? '') . "\n" .
                     ($datos['notas'] ?? '');

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':usuario_dni' => $datos['jefe_dni'],
                ':numero_presupuesto' => $numeroPresupuesto,
                ':cliente_id' => $datos['cliente_id'] ?? null,
                ':total' => $datos['precio_total'] ?? 0,
                ':notas' => $notas
            ]);

            $proyecto_id = $this->conn->lastInsertId();

            // Asignar trabajadores si se proporcionaron
            if (isset($datos['trabajadores']) && is_array($datos['trabajadores'])) {
                $this->asignarTrabajadores($proyecto_id, $datos['trabajadores']);
            }

            $this->conn->commit();
            return ['success' => true, 'proyecto_id' => $proyecto_id];

        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    /**
     * Generar número de presupuesto único
     */
    public function generarNumeroPresupuesto() {
        $fecha = date('Ymd');
        $stmt = $this->conn->query("SELECT COUNT(*) + 1 as num FROM presupuestos WHERE DATE(fecha_creacion) = CURDATE()");
        $num = $stmt->fetchColumn();
        return "PRW-{$fecha}-" . str_pad($num, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Asignar trabajadores a un proyecto
     * NOTA: Usa proyecto_id para referir a id_presupuesto
     */
    public function asignarTrabajadores($proyecto_id, $trabajadores) {
        $sql = "INSERT INTO asignaciones_proyecto (proyecto_id, trabajador_dni, rol_asignado)
                VALUES (:proyecto_id, :trabajador_dni, :rol_asignado)
                ON DUPLICATE KEY UPDATE rol_asignado = VALUES(rol_asignado)";

        $stmt = $this->conn->prepare($sql);

        foreach ($trabajadores as $trabajador) {
            $stmt->execute([
                ':proyecto_id' => $proyecto_id,
                ':trabajador_dni' => $trabajador['dni'],
                ':rol_asignado' => $trabajador['rol'] ?? null
            ]);
        }
    }

    /**
     * Obtener proyectos de un jefe de equipo
     * NOTA: Usa tabla presupuestos como fuente de datos
     */
    public function obtenerProyectosPorJefe($jefe_dni) {
        $sql = "SELECT p.id_presupuesto as id, p.numero_presupuesto as codigo, p.usuario_dni as jefe_dni,
                       p.estado, p.total as precio_total, p.notas,
                       p.fecha_creacion,
                       u.nombre as jefe_nombre, u.email as jefe_email,
                       c.nombre as cliente_nombre
                FROM presupuestos p
                LEFT JOIN usuarios u ON p.usuario_dni = u.dni
                LEFT JOIN clientes c ON p.cliente_id = c.id
                WHERE p.usuario_dni = :jefe_dni
                ORDER BY p.fecha_creacion DESC";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':jefe_dni' => $jefe_dni]);
            $proyectos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Procesar notas para extraer nombre y descripción
            foreach ($proyectos as &$p) {
                $p['nombre'] = $this->extraerNombreDeNotas($p['notas']);
                $p['descripcion'] = $this->extraerDescripcionDeNotas($p['notas']);
            }
            
            return $proyectos;
        } catch (PDOException $e) {
            error_log('Error en obtenerProyectosPorJefe: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener proyectos asignados a un trabajador
     * NOTA: Usa tabla presupuestos como fuente de datos
     */
    public function obtenerProyectosPorTrabajador($trabajador_dni) {
        $sql = "SELECT p.id_presupuesto as id, p.numero_presupuesto as codigo, p.usuario_dni as jefe_dni,
                       p.estado, p.total as precio_total, p.notas,
                       p.fecha_creacion,
                       u.nombre as jefe_nombre, u.email as jefe_email,
                       c.nombre as cliente_nombre,
                       ap.rol_asignado
                FROM presupuestos p
                INNER JOIN asignaciones_proyecto ap ON p.id_presupuesto = ap.proyecto_id
                LEFT JOIN usuarios u ON p.usuario_dni = u.dni
                LEFT JOIN clientes c ON p.cliente_id = c.id
                WHERE ap.trabajador_dni = :trabajador_dni
                ORDER BY p.fecha_creacion DESC";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':trabajador_dni' => $trabajador_dni]);
            $proyectos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Procesar notas para extraer nombre y descripción
            foreach ($proyectos as &$p) {
                $p['nombre'] = $this->extraerNombreDeNotas($p['notas']);
                $p['descripcion'] = $this->extraerDescripcionDeNotas($p['notas']);
            }
            
            return $proyectos;
        } catch (PDOException $e) {
            error_log('Error en obtenerProyectosPorTrabajador: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener trabajadores asignados a un proyecto
     */
    public function obtenerTrabajadoresProyecto($proyecto_id) {
        $sql = "SELECT u.dni, u.nombre, u.email, u.rol, ap.rol_asignado, ap.fecha_asignacion
                FROM usuarios u
                INNER JOIN asignaciones_proyecto ap ON u.dni = ap.trabajador_dni
                WHERE ap.proyecto_id = :proyecto_id
                ORDER BY ap.fecha_asignacion";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':proyecto_id' => $proyecto_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener miembros disponibles de un equipo para asignar
     */
    public function obtenerMiembrosEquipoDisponibles($equipo_id, $proyecto_id = null) {
        $sql = "SELECT u.dni, u.nombre, u.email, u.rol
                FROM usuarios u
                INNER JOIN miembros_equipo me ON u.dni = me.usuario_dni
                WHERE me.equipo_id = :equipo_id
                AND u.estado = 'activo'";

        $params = [':equipo_id' => $equipo_id];

        // Excluir trabajadores ya asignados a este proyecto
        if ($proyecto_id) {
            $sql .= " AND u.dni NOT IN (
                SELECT trabajador_dni FROM asignaciones_proyecto
                WHERE proyecto_id = :proyecto_id
            )";
            $params[':proyecto_id'] = $proyecto_id;
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Remover asignación de trabajador
     */
    public function removerAsignacion($proyecto_id, $trabajador_dni) {
        $sql = "DELETE FROM asignaciones_proyecto
                WHERE proyecto_id = :proyecto_id AND trabajador_dni = :trabajador_dni";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':proyecto_id' => $proyecto_id,
            ':trabajador_dni' => $trabajador_dni
        ]);
    }

    /**
     * Generar código único para proyecto
     */
    public function generarCodigoProyecto() {
        $fecha = date('Ymd');
        $numero = rand(100, 999);
        return "PROJ-{$fecha}-{$numero}";
    }

    /**
     * Extraer nombre del proyecto de las notas
     */
    private function extraerNombreDeNotas($notas) {
        if (empty($notas)) {
            return 'Sin nombre';
        }
        
        // Buscar "PROYECTO: nombre"
        if (preg_match('/PROYECTO:\s*(.+?)(?:\n|$)/i', $notas, $matches)) {
            return trim($matches[1]);
        }
        
        // Si no hay formato, usar las primeras palabras
        $lineas = explode("\n", $notas);
        return substr(trim($lineas[0]), 0, 50) ?: 'Sin nombre';
    }

    /**
     * Extraer descripción del proyecto de las notas
     */
    private function extraerDescripcionDeNotas($notas) {
        if (empty($notas)) {
            return '';
        }
        
        // Buscar "Descripción: texto"
        if (preg_match('/Descripción:\s*(.+?)(?:\n|$)/i', $notas, $matches)) {
            return trim($matches[1]);
        }
        
        return '';
    }
}
?>