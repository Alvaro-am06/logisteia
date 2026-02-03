-- =====================================================
-- MIGRACIÓN: Agregar campo presupuesto_numero a proyectos
-- =====================================================
-- Fecha: 2026-02-03
-- Descripción: Agrega el campo presupuesto_numero y fecha_fin_real
--              para enlazar proyectos con presupuestos
-- =====================================================

USE logisteia;

-- Verificar si existe la columna presupuesto_numero
SET @column_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'logisteia' 
    AND TABLE_NAME = 'proyectos' 
    AND COLUMN_NAME = 'presupuesto_numero'
);

-- Agregar columna presupuesto_numero si no existe
SET @sql = IF(@column_exists = 0,
    'ALTER TABLE proyectos ADD COLUMN presupuesto_numero VARCHAR(255) DEFAULT NULL COMMENT "Número del presupuesto asociado" AFTER equipo_id',
    'SELECT "La columna presupuesto_numero ya existe" AS mensaje'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar si existe la columna fecha_fin_real
SET @column_exists_fin = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'logisteia' 
    AND TABLE_NAME = 'proyectos' 
    AND COLUMN_NAME = 'fecha_fin_real'
);

-- Agregar columna fecha_fin_real si no existe
SET @sql_fin = IF(@column_exists_fin = 0,
    'ALTER TABLE proyectos ADD COLUMN fecha_fin_real DATE DEFAULT NULL COMMENT "Fecha real de finalización" AFTER fecha_fin_estimada',
    'SELECT "La columna fecha_fin_real ya existe" AS mensaje'
);

PREPARE stmt_fin FROM @sql_fin;
EXECUTE stmt_fin;
DEALLOCATE PREPARE stmt_fin;

-- Crear índice para presupuesto_numero si no existe
SET @index_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = 'logisteia' 
    AND TABLE_NAME = 'proyectos' 
    AND INDEX_NAME = 'proyectos_presupuesto_numero_index'
);

SET @sql_index = IF(@index_exists = 0,
    'ALTER TABLE proyectos ADD KEY proyectos_presupuesto_numero_index (presupuesto_numero)',
    'SELECT "El índice proyectos_presupuesto_numero_index ya existe" AS mensaje'
);

PREPARE stmt_index FROM @sql_index;
EXECUTE stmt_index;
DEALLOCATE PREPARE stmt_index;

-- Verificar cambios
SELECT 
    'Migración completada' AS status,
    COUNT(*) AS total_proyectos,
    SUM(CASE WHEN presupuesto_numero IS NOT NULL THEN 1 ELSE 0 END) AS con_presupuesto,
    SUM(CASE WHEN presupuesto_numero IS NULL THEN 1 ELSE 0 END) AS sin_presupuesto
FROM proyectos;

-- =====================================================
-- FIN DE LA MIGRACIÓN
-- =====================================================
