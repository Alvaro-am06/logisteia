-- Agregar columna DNI a la tabla clientes si no existe
ALTER TABLE clientes 
ADD COLUMN dni VARCHAR(20) UNIQUE AFTER id;

-- Crear índice para búsquedas rápidas por DNI
CREATE INDEX idx_cliente_dni ON clientes(dni);
