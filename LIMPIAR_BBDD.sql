-- =====================================================================
-- LIMPIEZA COMPLETA DE BASE DE DATOS
-- =====================================================================
-- 1. Eliminar tablas innecesarias
-- 2. Eliminar columna estado_invitacion
-- 3. Corregir FK de asignaciones_proyecto
-- =====================================================================

-- 1. ELIMINAR TABLAS INNECESARIAS
DROP TABLE IF EXISTS `pagos`;
DROP TABLE IF EXISTS `facturas`;
DROP TABLE IF EXISTS `tareas`;
DROP TABLE IF EXISTS `asignaciones_proyecto`;

-- 2. RECREAR ASIGNACIONES_PROYECTO CON FK CORRECTA
CREATE TABLE `asignaciones_proyecto` (
  `id` int NOT NULL AUTO_INCREMENT,
  `proyecto_id` int NOT NULL,
  `trabajador_dni` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rol_asignado` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'trabajador',
  `fecha_asignacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_proyecto_trabajador` (`proyecto_id`,`trabajador_dni`),
  KEY `asignaciones_proyecto_proyecto_id_index` (`proyecto_id`),
  KEY `asignaciones_proyecto_trabajador_dni_index` (`trabajador_dni`),
  CONSTRAINT `asignaciones_proyecto_ibfk_1` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `asignaciones_proyecto_ibfk_2` FOREIGN KEY (`trabajador_dni`) REFERENCES `usuarios` (`dni`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. ELIMINAR COLUMNA estado_invitacion DE miembros_equipo
ALTER TABLE `miembros_equipo` DROP COLUMN `estado_invitacion`;
ALTER TABLE `miembros_equipo` DROP COLUMN `token_invitacion`;

-- 4. VERIFICAR RESULTADO
SHOW TABLES;
DESCRIBE miembros_equipo;
DESCRIBE asignaciones_proyecto;

-- =====================================================================
-- EJECUTA ESTO EN phpMyAdmin:
-- 1. Selecciona la base de datos "Logisteia"
-- 2. Copia y pega este script completo
-- 3. Click en "Continuar"
-- =====================================================================
