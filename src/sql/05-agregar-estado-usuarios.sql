-- =====================================================================
-- MIGRACIÓN: Agregar campos de estado a usuarios
-- Fecha: 28 de enero de 2026
-- Descripción: Agrega columnas estado, fecha_baneo y motivo_baneo
-- =====================================================================

USE `Logisteia`;

-- Verificar si la columna 'estado' ya existe antes de agregarla
SET @columna_existe = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'Logisteia'
    AND TABLE_NAME = 'usuarios'
    AND COLUMN_NAME = 'estado'
);

-- Agregar columna estado si no existe
SET @sql_estado = IF(
    @columna_existe = 0,
    "ALTER TABLE `usuarios` ADD COLUMN `estado` ENUM('activo', 'baneado', 'eliminado') NOT NULL DEFAULT 'activo' AFTER `telefono`",
    "SELECT 'La columna estado ya existe' AS mensaje"
);

PREPARE stmt FROM @sql_estado;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar si la columna 'fecha_baneo' ya existe
SET @columna_fecha_baneo = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'Logisteia'
    AND TABLE_NAME = 'usuarios'
    AND COLUMN_NAME = 'fecha_baneo'
);

-- Agregar columna fecha_baneo si no existe
SET @sql_fecha_baneo = IF(
    @columna_fecha_baneo = 0,
    "ALTER TABLE `usuarios` ADD COLUMN `fecha_baneo` TIMESTAMP NULL AFTER `estado`",
    "SELECT 'La columna fecha_baneo ya existe' AS mensaje"
);

PREPARE stmt FROM @sql_fecha_baneo;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar si la columna 'motivo_baneo' ya existe
SET @columna_motivo_baneo = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'Logisteia'
    AND TABLE_NAME = 'usuarios'
    AND COLUMN_NAME = 'motivo_baneo'
);

-- Agregar columna motivo_baneo si no existe
SET @sql_motivo_baneo = IF(
    @columna_motivo_baneo = 0,
    "ALTER TABLE `usuarios` ADD COLUMN `motivo_baneo` TEXT NULL AFTER `fecha_baneo`",
    "SELECT 'La columna motivo_baneo ya existe' AS mensaje"
);

PREPARE stmt FROM @sql_motivo_baneo;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Actualizar usuarios existentes para que tengan estado 'activo' si es NULL
UPDATE `usuarios` SET `estado` = 'activo' WHERE `estado` IS NULL OR `estado` = '';

SELECT 'Migración completada exitosamente' AS resultado;
