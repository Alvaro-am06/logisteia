-- datos_servicios_informatica.sql
-- Servicios y precios enfocados al desarrollo de software
-- Fecha: 13 de enero de 2026

USE `Logisteia`;

-- =====================================================================
-- SERVICIOS DE DESARROLLO WEB
-- =====================================================================
INSERT INTO `servicios_informatica` (`nombre`, `categoria`, `descripcion`, `precio_base`, `unidad`, `tecnologias`) VALUES
('Desarrollo Frontend con React', 'Desarrollo Web', 'Desarrollo de interfaces de usuario interactivas y responsive con React.js, incluyendo componentes reutilizables, gestión de estado y optimización', 45.00, 'hora', '["React", "TypeScript", "TailwindCSS", "Redux"]'),
('Desarrollo Frontend con Angular', 'Desarrollo Web', 'Creación de aplicaciones web SPA con Angular, TypeScript, módulos standalone y servicios', 50.00, 'hora', '["Angular", "TypeScript", "RxJS", "Angular Material"]'),
('Desarrollo Frontend con Vue.js', 'Desarrollo Web', 'Desarrollo de interfaces modernas con Vue 3, Composition API y Pinia para gestión de estado', 40.00, 'hora', '["Vue 3", "TypeScript", "Pinia", "Vite"]'),
('Desarrollo Backend con Node.js', 'Desarrollo Web', 'Creación de APIs RESTful y GraphQL con Node.js, Express, autenticación JWT y middleware personalizado', 50.00, 'hora', '["Node.js", "Express", "TypeScript", "JWT"]'),
('Desarrollo Backend con PHP Laravel', 'Desarrollo Web', 'Desarrollo de aplicaciones web robustas con Laravel, Eloquent ORM, migraciones y seeders', 45.00, 'hora', '["PHP", "Laravel", "MySQL", "Composer"]'),
('Desarrollo Backend con Python Django', 'Desarrollo Web', 'Construcción de aplicaciones web escalables con Django, Django REST Framework y PostgreSQL', 48.00, 'hora', '["Python", "Django", "PostgreSQL", "DRF"]'),
('Integración de APIs Externas', 'Desarrollo Web', 'Integración con APIs de terceros: Stripe, PayPal, Google Maps, redes sociales, etc.', 40.00, 'hora', '["REST", "GraphQL", "OAuth", "Webhooks"]'),
('Desarrollo Full Stack MERN', 'Desarrollo Web', 'Stack completo MongoDB, Express, React, Node.js para aplicaciones web modernas', 2500.00, 'proyecto', '["MongoDB", "Express", "React", "Node.js"]'),
('Desarrollo Full Stack MEAN', 'Desarrollo Web', 'Stack completo MongoDB, Express, Angular, Node.js con TypeScript', 2800.00, 'proyecto', '["MongoDB", "Express", "Angular", "Node.js"]'),
('Landing Page Corporativa', 'Desarrollo Web', 'Diseño y desarrollo de landing page responsive con formularios de contacto y animaciones', 800.00, 'proyecto', '["HTML5", "CSS3", "JavaScript", "TailwindCSS"]');

-- =====================================================================
-- SERVICIOS DE DESARROLLO MÓVIL
-- =====================================================================
INSERT INTO `servicios_informatica` (`nombre`, `categoria`, `descripcion`, `precio_base`, `unidad`, `tecnologias`) VALUES
('App Móvil con React Native', 'Desarrollo Móvil', 'Desarrollo de aplicaciones móviles multiplataforma (iOS y Android) con React Native', 55.00, 'hora', '["React Native", "TypeScript", "Expo", "Redux"]'),
('App Móvil con Flutter', 'Desarrollo Móvil', 'Creación de apps nativas para iOS y Android con Flutter y Dart', 60.00, 'hora', '["Flutter", "Dart", "Firebase", "GetX"]'),
('App iOS Nativa', 'Desarrollo Móvil', 'Desarrollo de aplicaciones nativas para iOS con Swift y SwiftUI', 65.00, 'hora', '["Swift", "SwiftUI", "Xcode", "Core Data"]'),
('App Android Nativa', 'Desarrollo Móvil', 'Desarrollo de aplicaciones nativas para Android con Kotlin y Jetpack Compose', 60.00, 'hora', '["Kotlin", "Jetpack Compose", "Android Studio", "Room"]'),
('Publicación en App Store / Google Play', 'Desarrollo Móvil', 'Proceso completo de publicación de aplicación en las tiendas oficiales', 300.00, 'proyecto', '["App Store", "Google Play", "Certificados", "Screenshots"]');

-- =====================================================================
-- SERVICIOS DE BASE DE DATOS
-- =====================================================================
INSERT INTO `servicios_informatica` (`nombre`, `categoria`, `descripcion`, `precio_base`, `unidad`, `tecnologias`) VALUES
('Diseño de Base de Datos Relacional', 'Base de Datos', 'Diseño de esquema de BD, normalización, relaciones, índices y optimización de consultas', 35.00, 'hora', '["MySQL", "PostgreSQL", "MariaDB", "SQL"]'),
('Diseño de Base de Datos NoSQL', 'Base de Datos', 'Modelado de datos para MongoDB, Firestore, Redis o DynamoDB', 40.00, 'hora', '["MongoDB", "Firestore", "Redis", "DynamoDB"]'),
('Migración de Base de Datos', 'Base de Datos', 'Migración de datos entre diferentes motores de BD con scripts y validación', 45.00, 'hora', '["MySQL", "PostgreSQL", "MongoDB", "Scripts"]'),
('Optimización de Consultas SQL', 'Base de Datos', 'Análisis y optimización de consultas lentas, creación de índices, refactorización', 40.00, 'hora', '["SQL", "Índices", "Query Optimization", "EXPLAIN"]'),
('Configuración de Backups Automáticos', 'Base de Datos', 'Implementación de sistema de backups automáticos con retención y recuperación', 250.00, 'proyecto', '["MySQL", "PostgreSQL", "Cron", "AWS S3"]');

-- =====================================================================
-- SERVICIOS DE UI/UX DESIGN
-- =====================================================================
INSERT INTO `servicios_informatica` (`nombre`, `categoria`, `descripcion`, `precio_base`, `unidad`, `tecnologias`) VALUES
('Diseño UI/UX con Figma', 'UI/UX Design', 'Diseño de interfaces de usuario, prototipos interactivos y sistema de diseño', 40.00, 'hora', '["Figma", "Adobe XD", "Sketch", "Design System"]'),
('Wireframes y Mockups', 'UI/UX Design', 'Creación de wireframes de baja/alta fidelidad y mockups para validación', 30.00, 'hora', '["Figma", "Balsamiq", "Miro", "Wireframes"]'),
('Prototipo Interactivo', 'UI/UX Design', 'Prototipo clickable con animaciones y flujos de navegación completos', 35.00, 'hora', '["Figma", "Protopie", "InVision", "Interactions"]'),
('Sistema de Diseño Completo', 'UI/UX Design', 'Creación de Design System con componentes, tipografías, colores y guías de uso', 1200.00, 'proyecto', '["Figma", "Storybook", "Design Tokens", "Guidelines"]'),
('Rediseño UI Completo', 'UI/UX Design', 'Rediseño completo de interfaz existente con mejoras de usabilidad y accesibilidad', 1500.00, 'proyecto', '["Figma", "User Research", "A/B Testing", "Accessibility"]');

-- =====================================================================
-- SERVICIOS DE TESTING
-- =====================================================================
INSERT INTO `servicios_informatica` (`nombre`, `categoria`, `descripcion`, `precio_base`, `unidad`, `tecnologias`) VALUES
('Tests Unitarios (Frontend)', 'Testing', 'Implementación de tests unitarios con Jest, Vitest o Jasmine para componentes', 35.00, 'hora', '["Jest", "Vitest", "Testing Library", "Jasmine"]'),
('Tests Unitarios (Backend)', 'Testing', 'Desarrollo de tests unitarios para APIs y lógica de negocio con PHPUnit, Pytest o Mocha', 35.00, 'hora', '["PHPUnit", "Pytest", "Mocha", "Chai"]'),
('Tests de Integración', 'Testing', 'Tests de integración entre módulos y servicios externos', 40.00, 'hora', '["Supertest", "Postman", "Newman", "Integration Tests"]'),
('Tests E2E con Cypress', 'Testing', 'Tests End-to-End automatizados con Cypress para flujos completos de usuario', 45.00, 'hora', '["Cypress", "E2E", "Automation", "CI/CD"]'),
('Tests E2E con Playwright', 'Testing', 'Automatización de tests E2E multiplataforma con Playwright', 45.00, 'hora', '["Playwright", "E2E", "Cross-browser", "Screenshots"]'),
('Suite Completa de Testing', 'Testing', 'Implementación de estrategia de testing completa: unitarios, integración y E2E', 2000.00, 'proyecto', '["Jest", "Cypress", "CI/CD", "Coverage"]');

-- =====================================================================
-- SERVICIOS DE DEVOPS
-- =====================================================================
INSERT INTO `servicios_informatica` (`nombre`, `categoria`, `descripcion`, `precio_base`, `unidad`, `tecnologias`) VALUES
('Configuración CI/CD con GitHub Actions', 'DevOps', 'Pipeline de integración y despliegue continuo con GitHub Actions', 500.00, 'proyecto', '["GitHub Actions", "Docker", "AWS", "Testing"]'),
('Configuración CI/CD con GitLab CI', 'DevOps', 'Pipeline automatizado con GitLab CI/CD, builds, tests y deploys', 550.00, 'proyecto', '["GitLab CI", "Docker", "Kubernetes", "Testing"]'),
('Dockerización de Aplicación', 'DevOps', 'Creación de Dockerfile, Docker Compose y optimización de imágenes', 400.00, 'proyecto', '["Docker", "Docker Compose", "Multi-stage", "Alpine"]'),
('Deploy en AWS (EC2, S3, RDS)', 'DevOps', 'Configuración de infraestructura en AWS con EC2, S3, RDS y CloudFront', 600.00, 'proyecto', '["AWS", "EC2", "S3", "RDS", "CloudFront"]'),
('Deploy en Vercel/Netlify', 'DevOps', 'Despliegue de aplicación frontend en Vercel o Netlify con dominio personalizado', 150.00, 'proyecto', '["Vercel", "Netlify", "DNS", "SSL"]'),
('Configuración de Servidor Linux', 'DevOps', 'Instalación y configuración de servidor LAMP/LEMP con seguridad y firewall', 400.00, 'proyecto', '["Linux", "Nginx", "Apache", "SSL", "Firewall"]'),
('Monitoreo y Logging', 'DevOps', 'Implementación de sistema de monitoreo con logs centralizados', 500.00, 'proyecto', '["Prometheus", "Grafana", "ELK Stack", "CloudWatch"]');

-- =====================================================================
-- SERVICIOS DE INFRAESTRUCTURA
-- =====================================================================
INSERT INTO `servicios_informatica` (`nombre`, `categoria`, `descripcion`, `precio_base`, `unidad`, `tecnologias`) VALUES
('Configuración de CDN', 'Infraestructura', 'Integración de CDN para optimización de assets (CloudFlare, AWS CloudFront)', 300.00, 'proyecto', '["CloudFlare", "CloudFront", "CDN", "Caching"]'),
('Optimización de Performance', 'Infraestructura', 'Análisis y optimización de rendimiento web: lazy loading, code splitting, caching', 40.00, 'hora', '["Lighthouse", "WebPageTest", "Bundle Analyzer", "Caching"]'),
('Implementación de SSL/TLS', 'Infraestructura', 'Instalación y configuración de certificados SSL con Let\'s Encrypt o comercial', 150.00, 'proyecto', '["SSL", "Let\'s Encrypt", "Certbot", "HTTPS"]'),
('Migración a Cloud', 'Infraestructura', 'Migración de aplicación on-premise a cloud (AWS, Azure, GCP)', 1500.00, 'proyecto', '["AWS", "Azure", "GCP", "Migration", "Cloud"]');

-- =====================================================================
-- SERVICIOS DE CONSULTORÍA
-- =====================================================================
INSERT INTO `servicios_informatica` (`nombre`, `categoria`, `descripcion`, `precio_base`, `unidad`, `tecnologias`) VALUES
('Auditoría de Código', 'Consultoría', 'Revisión de código fuente, identificación de bugs, mejoras y buenas prácticas', 50.00, 'hora', '["Code Review", "SonarQube", "ESLint", "Best Practices"]'),
('Auditoría de Seguridad', 'Consultoría', 'Análisis de vulnerabilidades, pentesting básico y recomendaciones de seguridad', 60.00, 'hora', '["OWASP", "Security", "Pentesting", "Vulnerabilities"]'),
('Arquitectura de Software', 'Consultoría', 'Diseño de arquitectura escalable, patrones de diseño y diagramas técnicos', 55.00, 'hora', '["Architecture", "Design Patterns", "Microservices", "UML"]'),
('Consultoría Tecnológica', 'Consultoría', 'Asesoramiento en elección de tecnologías, stack técnico y roadmap de desarrollo', 50.00, 'hora', '["Tech Stack", "Planning", "Best Practices", "Roadmap"]');

-- =====================================================================
-- SERVICIOS DE MANTENIMIENTO
-- =====================================================================
INSERT INTO `servicios_informatica` (`nombre`, `categoria`, `descripcion`, `precio_base`, `unidad`, `tecnologias`) VALUES
('Mantenimiento Mensual Básico', 'Mantenimiento', 'Actualizaciones, backups, monitoreo y soporte básico (hasta 8h/mes)', 400.00, 'mes', '["Updates", "Backups", "Monitoring", "Support"]'),
('Mantenimiento Mensual Premium', 'Mantenimiento', 'Soporte prioritario, actualizaciones, nuevas features y consultoría (hasta 20h/mes)', 900.00, 'mes', '["Priority Support", "Updates", "Features", "Consulting"]'),
('Corrección de Bugs', 'Mantenimiento', 'Identificación y corrección de errores en código existente', 40.00, 'hora', '["Debugging", "Bug Fixing", "Testing", "Git"]'),
('Actualización de Dependencias', 'Mantenimiento', 'Actualización de librerías, frameworks y resolución de incompatibilidades', 35.00, 'hora', '["npm", "Composer", "pip", "Dependencies"]');

-- =====================================================================
-- SERVICIOS VARIOS
-- =====================================================================
INSERT INTO `servicios_informatica` (`nombre`, `categoria`, `descripcion`, `precio_base`, `unidad`, `tecnologias`) VALUES
('Integración con Pasarela de Pago', 'Otros', 'Implementación de Stripe, PayPal, Redsys o similar con webhook y validación', 600.00, 'proyecto', '["Stripe", "PayPal", "Redsys", "Webhooks"]'),
('Sistema de Autenticación', 'Otros', 'Implementación de login, registro, recuperación de contraseña con JWT o sesiones', 400.00, 'proyecto', '["JWT", "OAuth", "Sessions", "Bcrypt"]'),
('Panel de Administración', 'Otros', 'Desarrollo de dashboard administrativo con CRUD, gráficos y gestión de usuarios', 1200.00, 'proyecto', '["React", "Angular", "Charts", "CRUD"]'),
('PWA (Progressive Web App)', 'Otros', 'Conversión de web app a PWA con service workers, offline y notificaciones push', 800.00, 'proyecto', '["Service Workers", "Manifest", "Push Notifications", "Cache"]'),
('SEO Técnico', 'Otros', 'Optimización SEO on-page, meta tags, sitemap, robots.txt y structured data', 350.00, 'proyecto', '["SEO", "Meta Tags", "Schema.org", "Sitemap"]'),
('Documentación Técnica', 'Otros', 'Creación de documentación completa: README, API docs, guías de uso', 30.00, 'hora', '["Markdown", "Swagger", "JSDoc", "Documentation"]');

-- =====================================================================
-- FIN DEL SCRIPT
-- =====================================================================
