-- bbdd.sql: tabla usuarios y acciones_administrativas (dni como PK)
CREATE TABLE IF NOT EXISTS usuarios (
  dni VARCHAR(50) NOT NULL PRIMARY KEY,
  email VARCHAR(255) NOT NULL UNIQUE,
  nombre VARCHAR(255) NOT NULL,
  contrase VARCHAR(255) NOT NULL,
  rol ENUM('administrador','registrado') NOT NULL DEFAULT 'registrado',
  estado ENUM('activo','suspendido','eliminado') NOT NULL DEFAULT 'activo',
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS acciones_administrativas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  administrador VARCHAR(255) NOT NULL,
  accion VARCHAR(50) NOT NULL,
  usuario_dni VARCHAR(50),
  motivo TEXT NULL,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_dni) REFERENCES usuarios(dni) ON DELETE SET NULL
);