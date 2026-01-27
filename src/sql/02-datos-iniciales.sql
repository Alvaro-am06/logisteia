-- datos_iniciales.sql: usuarios de ejemplo
INSERT IGNORE INTO usuarios (dni, email, nombre, contrase, rol) VALUES
('11111111A','admin@example.com','Administrador','$2y$10$ZHx9oD8FdCFvQKXcoBjyJe31TsqbE2n3zHTmuZXfT9mdymFCrRhsK','administrador'),
('22222222B','juan@example.com','Juan Pérez','$2y$10$CPIyQ6VJVpu18ZcYSSAvSeYOWup8alw0BHi.yt1RZNVg19qMKx1wu','registrado'),
('33333333C','maria@example.com','María López','$2y$10$ydhyOg2vz6XSByKK9bcSKuX8kEASSmKyc6jmcM.59qySdRA2vkl6W','registrado');

-- Servicios de logística profesional
INSERT IGNORE INTO servicios (nombre, precio_base, descripcion, categoria_nombre, esta_activo) VALUES
('Transporte Terrestre Nacional', 150.00, 'Servicio de transporte por carretera en territorio nacional', 'Transporte', 1),
('Transporte Terrestre Internacional', 450.00, 'Servicio de transporte por carretera a nivel internacional', 'Transporte', 1),
('Transporte Marítimo', 800.00, 'Servicio de transporte marítimo para mercancías', 'Transporte', 1),
('Transporte Aéreo', 1200.00, 'Servicio de transporte aéreo urgente', 'Transporte', 1),
('Almacenamiento Básico', 80.00, 'Almacenaje básico en nave logística (por m²/mes)', 'Almacenaje', 1),
('Almacenamiento Refrigerado', 150.00, 'Almacenaje en cámara frigorífica (por m²/mes)', 'Almacenaje', 1),
('Gestión de Inventario', 200.00, 'Control y gestión completa de inventario', 'Gestión', 1),
('Embalaje Estándar', 25.00, 'Servicio de embalaje estándar para mercancías', 'Manipulación', 1),
('Embalaje Premium', 60.00, 'Embalaje especial para productos delicados o de alto valor', 'Manipulación', 1),
('Carga y Descarga', 100.00, 'Servicio de carga y descarga de mercancías', 'Manipulación', 1),
('Seguro de Transporte Básico', 50.00, 'Seguro básico para mercancías en tránsito', 'Seguros', 1),
('Seguro de Transporte Premium', 120.00, 'Seguro completo con cobertura total', 'Seguros', 1),
('Gestión Aduanera', 180.00, 'Tramitación completa de documentación aduanera', 'Gestión', 1),
('Consultoría Logística', 300.00, 'Asesoramiento profesional en optimización logística', 'Consultoría', 1),
('Tracking GPS en Tiempo Real', 40.00, 'Seguimiento GPS de mercancías en tiempo real', 'Tecnología', 1),
('Sistema de Gestión ERP', 500.00, 'Implementación de sistema ERP para logística', 'Tecnología', 1),
('Distribución Última Milla', 35.00, 'Entrega en destino final al cliente', 'Distribución', 1),
('Logística Inversa', 90.00, 'Gestión de devoluciones y productos retornados', 'Gestión', 1),
('Paletizado', 15.00, 'Organización de mercancías en pallets', 'Manipulación', 1),
('Cross-Docking', 120.00, 'Servicio de transferencia directa sin almacenaje', 'Distribución', 1);