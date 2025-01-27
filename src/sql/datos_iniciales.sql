-- datos_iniciales.sql: usuarios de ejemplo
INSERT INTO usuarios (dni, email, nombre, contrase, rol) VALUES
('11111111A','admin@example.com','Administrador','$2y$10$ZHx9oD8FdCFvQKXcoBjyJe31TsqbE2n3zHTmuZXfT9mdymFCrRhsK','administrador'),
('22222222B','juan@example.com','Juan Pérez','$2y$10$CPIyQ6VJVpu18ZcYSSAvSeYOWup8alw0BHi.yt1RZNVg19qMKx1wu','registrado'),
('33333333C','maria@example.com','María López','$2y$10$ydhyOg2vz6XSByKK9bcSKuX8kEASSmKyc6jmcM.59qySdRA2vkl6W','registrado');