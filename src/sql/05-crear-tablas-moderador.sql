-- =============================================
-- Script: Crear tablas para funcionalidad de moderador
-- Descripción: Crea las tablas necesarias para que el
--              rol de moderador funcione correctamente
-- Fecha: 2026-02-03
-- =============================================

USE logisteia;

-- Tabla: historial_baneos
-- Almacena el historial de baneos realizados por jefes de equipo
CREATE TABLE IF NOT EXISTS `historial_baneos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `usuario_dni` VARCHAR(15) NOT NULL,
  `jefe_dni` VARCHAR(15) NOT NULL,
  `motivo` TEXT,
  `fecha_baneo` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `fecha_desbaneo` TIMESTAMP NULL DEFAULT NULL,
  `activo` BOOLEAN DEFAULT 1,
  INDEX `historial_baneos_usuario_dni_index`(`usuario_dni`),
  INDEX `historial_baneos_jefe_dni_index`(`jefe_dni`),
  CONSTRAINT `historial_baneos_usuario_dni_foreign` 
    FOREIGN KEY(`usuario_dni`) REFERENCES `usuarios`(`dni`) ON DELETE CASCADE,
  CONSTRAINT `historial_baneos_jefe_dni_foreign` 
    FOREIGN KEY(`jefe_dni`) REFERENCES `usuarios`(`dni`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: acciones_administrativas
-- Registra todas las acciones realizadas por administradores y moderadores
CREATE TABLE IF NOT EXISTS `acciones_administrativas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `administrador_dni` VARCHAR(15) NOT NULL,
  `accion` VARCHAR(50) NOT NULL COMMENT 'Tipo de acción: activar, suspender, eliminar, desbanear, etc.',
  `usuario_dni` VARCHAR(15) NULL COMMENT 'Usuario afectado por la acción',
  `motivo` TEXT NULL,
  `ip_origen` VARCHAR(45) NULL COMMENT 'IP desde donde se realizó la acción',
  `creado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `acciones_administrativas_administrador_dni_index`(`administrador_dni`),
  INDEX `acciones_administrativas_usuario_dni_index`(`usuario_dni`),
  CONSTRAINT `acciones_administrativas_administrador_dni_foreign` 
    FOREIGN KEY(`administrador_dni`) REFERENCES `usuarios`(`dni`) ON DELETE CASCADE,
  CONSTRAINT `acciones_administrativas_usuario_dni_foreign` 
    FOREIGN KEY(`usuario_dni`) REFERENCES `usuarios`(`dni`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Verificar que existan usuarios con rol moderador
-- Si no existen, este script debería ejecutarse después de crear moderadores manualmente

SELECT 'Tablas de moderador creadas exitosamente' AS mensaje;
