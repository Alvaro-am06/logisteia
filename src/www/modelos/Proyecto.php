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
     * Inserta en tabla proyectos (NO presupuestos)
     */
    public function crearProyecto($datos) {
        try {
            $this->conn->beginTransaction();

            // Generar código de proyecto
            $codigo = $datos['codigo'] ?? $this->generarCodigoProyecto();

            // Convertir tecnologías a JSON si es array
            $tecnologias_json = null;
            if (isset($datos['tecnologias'])) {
                $tecnologias_json = is_array($datos['tecnologias']) 
                    ? json_encode($datos['tecnologias'], JSON_UNESCAPED_UNICODE)
                    : $datos['tecnologias'];
            }

            // Obtener equipo_id del jefe si no se proporcionó
            $equipo_id = $datos['equipo_id'] ?? null;
            if (!$equipo_id && isset($datos['jefe_dni'])) {
                $stmtEquipo = $this->conn->prepare("SELECT id FROM equipos WHERE jefe_dni = :jefe_dni LIMIT 1");
                $stmtEquipo->execute([':jefe_dni' => $datos['jefe_dni']]);
                $equipo = $stmtEquipo->fetch(PDO::FETCH_ASSOC);
                if ($equipo) {
                    $equipo_id = $equipo['id'];
                }
            }

            // Insertar en tabla proyectos
            $sql = "INSERT INTO proyectos (
                codigo, nombre, descripcion, jefe_dni, cliente_id, equipo_id, estado,
                fecha_inicio, precio_total, tecnologias, repositorio_github, notas
            ) VALUES (
                :codigo, :nombre, :descripcion, :jefe_dni, :cliente_id, :equipo_id, 'creado',
                :fecha_inicio, :precio_total, :tecnologias, :repositorio_github, :notas
            )";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':codigo' => $codigo,
                ':nombre' => $datos['nombre'] ?? 'Proyecto sin nombre',
                ':descripcion' => $datos['descripcion'] ?? null,
                ':jefe_dni' => $datos['jefe_dni'],
                ':cliente_id' => $datos['cliente_id'] ?? null,
                ':equipo_id' => $equipo_id,
                ':fecha_inicio' => $datos['fecha_inicio'] ?? date('Y-m-d'),
                ':precio_total' => $datos['precio_total'] ?? 0,
                ':tecnologias' => $tecnologias_json,
                ':repositorio_github' => $datos['repositorio_github'] ?? null,
                ':notas' => $datos['notas'] ?? null
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
     * Asignar trabajadores a un proyecto
     * NOTA: Usa proyecto_id para referir a id_presupuesto
     */
    public function asignarTrabajadores($proyecto_id, $trabajadores) {
        $sql = "INSERT INTO asignaciones_proyecto (proyecto_id, trabajador_dni, rol_asignado)
                VALUES (:proyecto_id, :trabajador_dni, :rol_asignado)
                ON DUPLICATE KEY UPDATE rol_asignado = VALUES(rol_asignado)";

        $stmt = $this->conn->prepare($sql);

        foreach ($trabajadores as $trabajador) {
            // Aceptar dni como string o como objeto con dni
            $dni = is_string($trabajador) ? $trabajador : ($trabajador['dni'] ?? null);
            $rol = is_array($trabajador) ? ($trabajador['rol'] ?? $trabajador['rol_asignado'] ?? 'trabajador') : 'trabajador';
            
            if (!$dni) continue;
            
            $stmt->execute([
                ':proyecto_id' => $proyecto_id,
                ':trabajador_dni' => $dni,
                ':rol_asignado' => $rol
            ]);
        }
    }

    /**
     * Obtener proyectos de un jefe de equipo
     * Usa tabla proyectos
     */
    public function obtenerProyectosPorJefe($jefe_dni) {
        $sql = "SELECT p.id, p.codigo, p.nombre, p.descripcion, p.jefe_dni,
                       p.estado, p.precio_total, p.tecnologias, p.repositorio_github, p.notas,
                       p.fecha_inicio, p.fecha_creacion,
                       u.nombre as jefe_nombre, u.email as jefe_email,
                       c.nombre as cliente_nombre, c.empresa as cliente_empresa,
                       e.nombre as equipo_nombre
                FROM proyectos p
                LEFT JOIN usuarios u ON p.jefe_dni = u.dni
                LEFT JOIN clientes c ON p.cliente_id = c.id
                LEFT JOIN equipos e ON p.equipo_id = e.id
                WHERE p.jefe_dni = :jefe_dni
                ORDER BY p.fecha_creacion DESC";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':jefe_dni' => $jefe_dni]);
            $proyectos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Decodificar tecnologías JSON
            foreach ($proyectos as &$p) {
                if ($p['tecnologias']) {
                    $p['tecnologias'] = json_decode($p['tecnologias'], true);
                }
            }
            
            return $proyectos;
        } catch (PDOException $e) {
            error_log('Error en obtenerProyectosPorJefe: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener proyectos asignados a un trabajador
     * Usa tabla proyectos
     */
    public function obtenerProyectosPorTrabajador($trabajador_dni) {
        $sql = "SELECT p.id, p.codigo, p.nombre, p.descripcion, p.jefe_dni,
                       p.estado, p.precio_total, p.tecnologias, p.repositorio_github, p.notas,
                       p.fecha_inicio, p.fecha_creacion,
                       u.nombre as jefe_nombre, u.email as jefe_email,
                       c.nombre as cliente_nombre, c.empresa as cliente_empresa,
                       ap.rol_asignado
                FROM proyectos p
                INNER JOIN asignaciones_proyecto ap ON p.id = ap.proyecto_id
                LEFT JOIN usuarios u ON p.jefe_dni = u.dni
                LEFT JOIN clientes c ON p.cliente_id = c.id
                WHERE ap.trabajador_dni = :trabajador_dni
                ORDER BY p.fecha_creacion DESC";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':trabajador_dni' => $trabajador_dni]);
            $proyectos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Decodificar tecnologías JSON
            foreach ($proyectos as &$p) {
                if ($p['tecnologias']) {
                    $p['tecnologias'] = json_decode($p['tecnologias'], true);
                }
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
        // Obtener todos los miembros del equipo que estén activos
        $sql = "SELECT u.dni, u.nombre, u.email, u.rol, me.rol_proyecto
                FROM usuarios u
                INNER JOIN miembros_equipo me ON u.dni = me.trabajador_dni
                WHERE me.equipo_id = :equipo_id
                AND u.estado = 'activo'
                AND me.activo = 1";

        $params = [':equipo_id' => $equipo_id];

        // Excluir trabajadores ya asignados a este proyecto
        if ($proyecto_id) {
            $sql .= " AND u.dni NOT IN (
                SELECT trabajador_dni FROM asignaciones_proyecto
                WHERE proyecto_id = :proyecto_id
            )";
            $params[':proyecto_id'] = $proyecto_id;
        }
        
        $sql .= " ORDER BY u.nombre";

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
}
?>