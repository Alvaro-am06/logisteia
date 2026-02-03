<?php
/**
 * API para obtener todos los proyectos/presupuestos (solo para moderadores)
 * 
 * GET: Obtiene todos los presupuestos del sistema
 */

// Cargar configuración centralizada
require_once __DIR__ . '/../../config/config.php';

// Configurar CORS
setupCors();

// Manejar preflight OPTIONS
if (handlePreflight()) {
    exit();
}

header('Content-Type: application/json');

require_once __DIR__ . '/../../modelos/ConexionBBDD.php';

// Solo permitir GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendJsonError('Método no permitido', 405);
}

try {
    $db = ConexionBBDD::obtener();
    
    // Verificar si la tabla proyectos existe
    try {
        $checkTable = $db->query("SHOW TABLES LIKE 'proyectos'");
        $tableExists = $checkTable->fetch() !== false;
    } catch (Exception $e) {
        $tableExists = false;
    }
    
    if (!$tableExists) {
        // Si la tabla no existe, devolver array vacío
        sendJsonSuccess([]);
        exit();
    }
    
    // Verificar qué columnas tiene la tabla
    try {
        $columns = $db->query("SHOW COLUMNS FROM proyectos")->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        sendJsonSuccess([]);
        exit();
    }
    
    // Verificar si existe la tabla presupuestos
    try {
        $checkPresupuestos = $db->query("SHOW TABLES LIKE 'presupuestos'");
        $presupuestosExists = $checkPresupuestos->fetch() !== false;
    } catch (Exception $e) {
        $presupuestosExists = false;
    }
    
    // Construir query dinámicamente según columnas disponibles
    $hasCodigoCol = in_array('codigo', $columns);
    $hasFechaFinReal = in_array('fecha_fin_real', $columns);
    $hasPrecioTotal = in_array('precio_total', $columns);
    $hasFechaCreacion = in_array('fecha_creacion', $columns);
    $hasPresupuestoNumero = in_array('presupuesto_numero', $columns);
    
    $codigoField = $hasCodigoCol ? 'p.codigo' : 'CONCAT("PROY-", p.id) as codigo';
    $fechaFinField = $hasFechaFinReal ? 'p.fecha_fin_real as fecha_fin' : 'NULL as fecha_fin';
    $orderBy = $hasFechaCreacion ? 'p.fecha_creacion DESC' : 'p.id DESC';
    
    // Construir el campo de precio según disponibilidad de presupuestos
    $precioField = 'COALESCE(';
    if ($presupuestosExists && $hasPresupuestoNumero) {
        $precioField .= 'pr.total, ';
    }
    if ($hasPrecioTotal) {
        $precioField .= 'p.precio_total, ';
    }
    $precioField .= '0) as precio_total';
    
    // Construir el LEFT JOIN solo si existe la tabla presupuestos
    $presupuestoJoin = '';
    if ($presupuestosExists && $hasPresupuestoNumero) {
        $presupuestoJoin = 'LEFT JOIN presupuestos pr ON p.presupuesto_numero = pr.numero_presupuesto';
    }
    
    // Consulta para obtener todos los proyectos con el precio del presupuesto
    $query = "SELECT 
                p.id,
                $codigoField,
                p.nombre,
                p.descripcion,
                u.nombre as jefe_nombre,
                c.nombre as cliente_nombre,
                p.estado,
                p.fecha_inicio,
                $fechaFinField,
                $precioField
              FROM proyectos p
              LEFT JOIN usuarios u ON p.jefe_dni = u.dni
              LEFT JOIN clientes c ON p.cliente_id = c.id
              $presupuestoJoin
              ORDER BY $orderBy";
    
    $stmt = $db->query($query);
    $proyectos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear datos
    foreach ($proyectos as &$proyecto) {
        // Formatear fecha_inicio solo si existe
        if (isset($proyecto['fecha_inicio']) && $proyecto['fecha_inicio']) {
            $proyecto['fecha_inicio'] = date('d/m/Y', strtotime($proyecto['fecha_inicio']));
        } else {
            $proyecto['fecha_inicio'] = 'Sin fecha';
        }
        
        $proyecto['precio_total'] = isset($proyecto['precio_total']) ? floatval($proyecto['precio_total']) : 0.0;
        $proyecto['id'] = intval($proyecto['id']);
        
        // Valores por defecto si son NULL
        if (empty($proyecto['cliente_nombre'])) {
            $proyecto['cliente_nombre'] = 'Sin cliente';
        }
        if (empty($proyecto['jefe_nombre'])) {
            $proyecto['jefe_nombre'] = 'Sin asignar';
        }
        if (empty($proyecto['descripcion'])) {
            $proyecto['descripcion'] = '';
        }
    }
    
    sendJsonSuccess($proyectos);
    
} catch (Exception $e) {
    logError('Error en moderador/proyectos.php', $e);
    sendJsonError('Error interno del servidor', 500);
}
