-- Agregar campo estado a la tabla usuarios para eliminación lógica
USE `Logisteia`;

-- Agregar columna estado si no existe
ALTER TABLE `usuarios` 
ADD COLUMN `estado` ENUM('activo', 'suspendido', 'eliminado') NOT NULL DEFAULT 'activo' 
AFTER `rol`;

-- Actualizar usuarios existentes según su rol
UPDATE `usuarios` 
SET `estado` = CASE 
    WHEN `rol` = 'administrador' THEN 'activo'
    ELSE 'suspendido'
END
WHERE `estado` = 'activo';
