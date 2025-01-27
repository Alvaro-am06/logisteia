-- Eliminar columnas innecesarias de la tabla usuarios
ALTER TABLE usuarios 
DROP COLUMN IF EXISTS avatar,
DROP COLUMN IF EXISTS bio;
