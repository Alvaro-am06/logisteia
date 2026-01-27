-- =====================================================================
-- FIX CRÍTICO: Corregir Foreign Key de asignaciones_proyecto
-- =====================================================================
-- Problema: asignaciones_proyecto.proyecto_id apunta a presupuestos.id_presupuesto
-- Debe apuntar a: proyectos.id
-- =====================================================================

-- 1. Eliminar FK incorrecta
ALTER TABLE `asignaciones_proyecto` 
  DROP FOREIGN KEY `asignaciones_proyecto_ibfk_1`;

-- 2. Crear FK correcta
ALTER TABLE `asignaciones_proyecto`
  ADD CONSTRAINT `asignaciones_proyecto_ibfk_1` 
  FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`) ON DELETE CASCADE;

-- 3. Verificar que quedó bien
SHOW CREATE TABLE asignaciones_proyecto;

-- =====================================================================
-- EJECUTA ESTO EN phpMyAdmin O DESDE SSH:
-- mysql -u root -p Logisteia < FIX_FK_ASIGNACIONES.sql
-- =====================================================================
