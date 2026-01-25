-- bbdd_nueva.sql: Nueva estructura para LOGISTEIA - Gestión de Proyectos en Equipo
-- Fecha: 13 de enero de 2026

USE `Logisteia`;

-- =====================================================================
-- 1. MODIFICAR TABLA USUARIOS
-- =====================================================================
-- Agregar nuevos campos y modificar roles
ALTER TABLE `usuarios` 
  MODIFY `rol` ENUM('jefe_equipo', 'trabajador') NOT NULL DEFAULT 'trabajador',
  MODIFY `estado` ENUM('activo', 'baneado', 'eliminado') NOT NULL DEFAULT 'activo' AFTER `rol`,
  ADD `fecha_baneo` TIMESTAMP NULL AFTER `fecha_registro`,
  ADD `motivo_baneo` TEXT NULL AFTER `fecha_baneo`,
  ADD `avatar` VARCHAR(255) NULL AFTER `telefono`,
  ADD `bio` TEXT NULL AFTER `avatar`;

-- =====================================================================
-- 2. TABLA EQUIPOS - Cada jefe tiene su equipo
-- =====================================================================
CREATE TABLE IF NOT EXISTS `equipos` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(255) NOT NULL,
  `descripcion` TEXT NULL,
  `jefe_dni` VARCHAR(255) NOT NULL,
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  FOREIGN KEY (`jefe_dni`) REFERENCES `usuarios`(`dni`) ON DELETE CASCADE,
  INDEX `equipos_jefe_dni_index`(`jefe_dni`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 3. TABLA MIEMBROS_EQUIPO - Relación trabajadores-equipos
-- =====================================================================
CREATE TABLE IF NOT EXISTS `miembros_equipo` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `equipo_id` INT NOT NULL,
  `trabajador_dni` VARCHAR(255) NOT NULL,
  `rol_proyecto` VARCHAR(255) NOT NULL,
  `fecha_ingreso` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `estado_invitacion` ENUM('pendiente', 'aceptada', 'rechazada') NOT NULL DEFAULT 'pendiente',
  `token_invitacion` VARCHAR(255) NULL,
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  FOREIGN KEY (`equipo_id`) REFERENCES `equipos`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`trabajador_dni`) REFERENCES `usuarios`(`dni`) ON DELETE CASCADE,
  UNIQUE KEY `unico_trabajador_equipo` (`equipo_id`, `trabajador_dni`),
  INDEX `miembros_equipo_trabajador_dni_index`(`trabajador_dni`),
  INDEX `miembros_equipo_equipo_id_index`(`equipo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 4. TABLA CLIENTES - Gestionados por cada jefe
-- =====================================================================
CREATE TABLE IF NOT EXISTS `clientes` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `jefe_dni` VARCHAR(255) NOT NULL,
  `nombre` VARCHAR(255) NOT NULL,
  `empresa` VARCHAR(255) NULL,
  `email` VARCHAR(255) NOT NULL,
  `telefono` VARCHAR(20) NULL,
  `direccion` TEXT NULL,
  `cif_nif` VARCHAR(20) NULL,
  `notas` TEXT NULL,
  `fecha_registro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  FOREIGN KEY (`jefe_dni`) REFERENCES `usuarios`(`dni`) ON DELETE CASCADE,
  INDEX `clientes_jefe_dni_index`(`jefe_dni`),
  INDEX `clientes_email_index`(`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 5. TABLA PROYECTOS - Creados por jefes de equipo
-- =====================================================================
CREATE TABLE IF NOT EXISTS `proyectos` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `codigo` VARCHAR(50) NOT NULL UNIQUE,
  `nombre` VARCHAR(255) NOT NULL,
  `descripcion` TEXT NULL,
  `jefe_dni` VARCHAR(255) NOT NULL,
  `cliente_id` INT NULL,
  `equipo_id` INT NULL,
  `estado` ENUM('creado', 'en_proceso', 'finalizado', 'pausado', 'cancelado') NOT NULL DEFAULT 'creado',
  `fecha_inicio` DATE NULL,
  `fecha_fin_estimada` DATE NULL,
  `fecha_fin_real` DATE NULL,
  `horas_estimadas` DECIMAL(10, 2) NULL DEFAULT 0,
  `horas_trabajadas` DECIMAL(10, 2) NULL DEFAULT 0,
  `precio_hora` DECIMAL(10, 2) NULL DEFAULT 0,
  `precio_total` DECIMAL(10, 2) NULL DEFAULT 0,
  `tecnologias` TEXT NULL COMMENT 'JSON con tecnologías: React, Laravel, MySQL, etc.',
  `repositorio_github` VARCHAR(255) NULL,
  `notas` TEXT NULL,
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `fecha_actualizacion` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`jefe_dni`) REFERENCES `usuarios`(`dni`) ON DELETE CASCADE,
  FOREIGN KEY (`cliente_id`) REFERENCES `clientes`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`equipo_id`) REFERENCES `equipos`(`id`) ON DELETE SET NULL,
  INDEX `proyectos_jefe_dni_index`(`jefe_dni`),
  INDEX `proyectos_cliente_id_index`(`cliente_id`),
  INDEX `proyectos_estado_index`(`estado`),
  INDEX `proyectos_codigo_index`(`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 6. TABLA TAREAS - Dentro de cada proyecto
-- =====================================================================
CREATE TABLE IF NOT EXISTS `tareas` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `proyecto_id` INT NOT NULL,
  `nombre` VARCHAR(255) NOT NULL,
  `descripcion` TEXT NULL,
  `trabajador_dni` VARCHAR(255) NULL,
  `rol_requerido` ENUM(
    'Frontend Developer',
    'Backend Developer',
    'Database Administrator',
    'UI/UX Designer',
    'QA Tester',
    'DevOps Engineer'
  ) NULL,
  `estado` ENUM('pendiente', 'en_progreso', 'completada', 'bloqueada') NOT NULL DEFAULT 'pendiente',
  `prioridad` ENUM('baja', 'media', 'alta', 'critica') NOT NULL DEFAULT 'media',
  `horas_estimadas` DECIMAL(10, 2) NULL DEFAULT 0,
  `horas_trabajadas` DECIMAL(10, 2) NULL DEFAULT 0,
  `fecha_inicio` TIMESTAMP NULL,
  `fecha_fin` TIMESTAMP NULL,
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`trabajador_dni`) REFERENCES `usuarios`(`dni`) ON DELETE SET NULL,
  INDEX `tareas_proyecto_id_index`(`proyecto_id`),
  INDEX `tareas_trabajador_dni_index`(`trabajador_dni`),
  INDEX `tareas_estado_index`(`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 7. TABLA REGISTRO_HORAS - Cronómetro para cada tarea (HU futura)
-- =====================================================================
CREATE TABLE IF NOT EXISTS `registro_horas` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `tarea_id` INT NOT NULL,
  `trabajador_dni` VARCHAR(255) NOT NULL,
  `proyecto_id` INT NOT NULL,
  `hora_inicio` TIMESTAMP NOT NULL,
  `hora_fin` TIMESTAMP NULL,
  `duracion_minutos` INT NULL COMMENT 'Calculado automáticamente',
  `descripcion` TEXT NULL,
  `fecha_registro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  FOREIGN KEY (`tarea_id`) REFERENCES `tareas`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`trabajador_dni`) REFERENCES `usuarios`(`dni`) ON DELETE CASCADE,
  FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos`(`id`) ON DELETE CASCADE,
  INDEX `registro_horas_tarea_id_index`(`tarea_id`),
  INDEX `registro_horas_trabajador_dni_index`(`trabajador_dni`),
  INDEX `registro_horas_proyecto_id_index`(`proyecto_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 8. MODIFICAR TABLA PRESUPUESTOS - Asociados a proyectos finalizados
-- =====================================================================
ALTER TABLE `presupuestos`
  ADD `proyecto_id` INT NULL AFTER `id_presupuesto`,
  ADD `cliente_id` INT NULL AFTER `proyecto_id`,
  ADD `horas_totales` DECIMAL(10, 2) NULL DEFAULT 0 AFTER `validez_dias`,
  ADD `precio_por_hora` DECIMAL(10, 2) NULL DEFAULT 0 AFTER `horas_totales`,
  ADD FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos`(`id`) ON DELETE SET NULL,
  ADD FOREIGN KEY (`cliente_id`) REFERENCES `clientes`(`id`) ON DELETE SET NULL;

-- =====================================================================
-- 9. TABLA SERVICIOS_INFORMATICA - Nuevos servicios enfocados a IT
-- =====================================================================
CREATE TABLE IF NOT EXISTS `servicios_informatica` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(255) NOT NULL,
  `categoria` ENUM(
    'Desarrollo Web',
    'Desarrollo Móvil',
    'Base de Datos',
    'UI/UX Design',
    'Testing',
    'DevOps',
    'Infraestructura',
    'Consultoría',
    'Mantenimiento',
    'Otros'
  ) NOT NULL,
  `descripcion` TEXT NULL,
  `precio_base` DECIMAL(10, 2) NULL DEFAULT 0,
  `unidad` ENUM('hora', 'proyecto', 'mes', 'otro') NOT NULL DEFAULT 'hora',
  `tecnologias` TEXT NULL COMMENT 'JSON: React, Laravel, etc.',
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  INDEX `servicios_informatica_categoria_index`(`categoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 10. TABLA BANEOS - Historial de baneos temporales
-- =====================================================================
CREATE TABLE IF NOT EXISTS `historial_baneos` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `usuario_dni` VARCHAR(255) NOT NULL,
  `jefe_dni` VARCHAR(255) NOT NULL,
  `motivo` TEXT NOT NULL,
  `fecha_baneo` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `fecha_desbaneo` TIMESTAMP NULL,
  `activo` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=baneado, 0=desbaneado',
  FOREIGN KEY (`usuario_dni`) REFERENCES `usuarios`(`dni`) ON DELETE CASCADE,
  FOREIGN KEY (`jefe_dni`) REFERENCES `usuarios`(`dni`) ON DELETE CASCADE,
  INDEX `historial_baneos_usuario_dni_index`(`usuario_dni`),
  INDEX `historial_baneos_jefe_dni_index`(`jefe_dni`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 11. TABLA INVITACIONES - Sistema de invitaciones al equipo
-- =====================================================================
CREATE TABLE IF NOT EXISTS `invitaciones` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `jefe_dni` VARCHAR(255) NOT NULL,
  `trabajador_email` VARCHAR(255) NOT NULL,
  `equipo_id` INT NOT NULL,
  `rol_proyecto` ENUM(
    'Frontend Developer',
    'Backend Developer',
    'Database Administrator',
    'UI/UX Designer',
    'QA Tester',
    'DevOps Engineer'
  ) NOT NULL,
  `token` VARCHAR(255) NOT NULL UNIQUE,
  `estado` ENUM('pendiente', 'aceptada', 'rechazada', 'expirada') NOT NULL DEFAULT 'pendiente',
  `mensaje` TEXT NULL,
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `fecha_expiracion` TIMESTAMP NULL DEFAULT NULL,
  `fecha_respuesta` TIMESTAMP NULL,
  FOREIGN KEY (`jefe_dni`) REFERENCES `usuarios`(`dni`) ON DELETE CASCADE,
  FOREIGN KEY (`equipo_id`) REFERENCES `equipos`(`id`) ON DELETE CASCADE,
  INDEX `invitaciones_trabajador_email_index`(`trabajador_email`),
  INDEX `invitaciones_token_index`(`token`),
  INDEX `invitaciones_estado_index`(`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 12. ACTUALIZAR TABLA ACCIONES_ADMINISTRATIVAS
-- =====================================================================
ALTER TABLE `acciones_administrativas`
  MODIFY `accion` VARCHAR(100) NOT NULL,
  ADD `proyecto_id` INT NULL AFTER `usuario_dni`,
  ADD `equipo_id` INT NULL AFTER `proyecto_id`,
  ADD FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos`(`id`) ON DELETE SET NULL,
  ADD FOREIGN KEY (`equipo_id`) REFERENCES `equipos`(`id`) ON DELETE SET NULL;

-- =====================================================================
-- ACTUALIZACIONES POST-MIGRACIÓN
-- =====================================================================

-- Agregar columna token_invitacion si no existe (para compatibilidad)
ALTER TABLE miembros_equipo ADD COLUMN IF NOT EXISTS token_invitacion VARCHAR(255) NULL AFTER estado_invitacion;

-- Crear índice para token_invitacion si no existe
CREATE INDEX IF NOT EXISTS idx_miembros_equipo_token ON miembros_equipo(token_invitacion);

-- =====================================================================
-- FIN DEL SCRIPT
-- =====================================================================
