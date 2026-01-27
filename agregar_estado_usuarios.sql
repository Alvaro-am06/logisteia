-- =====================================================================
-- MIGRACIÓN: Agregar campos de estado a usuarios (VERSIÓN SIMPLIFICADA)
-- Fecha: 28 de enero de 2026
-- =====================================================================

USE `Logisteia`;

-- Agregar columna estado (ignorar error si ya existe)
ALTER TABLE `usuarios` ADD COLUMN `estado` ENUM('activo', 'baneado', 'eliminado') NOT NULL DEFAULT 'activo' AFTER `telefono`;

-- Agregar columna fecha_baneo (ignorar error si ya existe)
ALTER TABLE `usuarios` ADD COLUMN `fecha_baneo` TIMESTAMP NULL AFTER `estado`;

-- Agregar columna motivo_baneo (ignorar error si ya existe)
ALTER TABLE `usuarios` ADD COLUMN `motivo_baneo` TEXT NULL AFTER `fecha_baneo`;

-- Actualizar usuarios existentes para que tengan estado 'activo'
UPDATE `usuarios` SET `estado` = 'activo' WHERE `estado` IS NULL OR `estado` = '';

SELECT 'Migración completada' AS resultado;
