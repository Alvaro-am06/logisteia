# 🚀 LOGISTEIA - Backend Spring Boot

> **Sistema de Gestión Empresarial | Java 25 LTS | Spring Boot 4.0 | MySQL 8.0**

## 📋 Índice

- [📊 Estado del Proyecto](#estado-del-proyecto)
- [🏗️ Arquitectura](#arquitectura)
- [📦 Stack Tecnológico](#stack-tecnológico)
- [🌐 Despliegue en Oracle Cloud](#despliegue-en-oracle-cloud)
- [🚀 Inicio Rápido](#inicio-rápido)
- [🔒 Seguridad](#seguridad)
- [📚 Documentación](#documentación)

---

## 📊 Estado del Proyecto

| Aspecto | Estado |
|---------|--------|
| **Java Runtime** | ✅ Java 25 LTS |
| **CVE Security** | ✅ 0 vulnerabilidades críticas (Tomcat 11.0.22) |
| **Tests** | ✅ 64/64 pasando (100%) |
| **Build** | ✅ Maven 3.9.x |
| **Database** | ✅ MySQL 8.0 |
| **Ready for Production** | ✅ Sí |

---

## 🏗️ Arquitectura

```
Logisteia Backend
├── 📦 API REST (Spring Boot 4.0)
├── 🛡️ Security (Spring Security + JWT)
├── 💾 Data Layer (Spring Data JPA + Hibernate)
├── 🔐 Authentication (JWT + OAuth ready)
└── 📡 Database (MySQL 8.0)
```

---

## 📦 Stack Tecnológico

### Backend
- **Java 25 LTS** - Runtime moderno y seguro
- **Spring Boot 4.0.6** - Framework de aplicación
- **Spring Security 7.0** - Autenticación y autorización
- **Spring Data JPA** - Acceso a datos
- **Hibernate 7.2** - ORM
- **MySQL 8.0** - Base de datos

### Build & DevOps
- **Maven 3.9.x** - Gestor de dependencias
- **Docker** - Containerización
- **Git** - Control de versiones

### Testing
- **JUnit 5** - Framework de testing
- **64 tests unitarios** - Cobertura completa

---

## 🌐 Despliegue en Oracle Cloud

### 📚 Documentación de Despliegue

1. **[📖 GUIA_ORACLE_CLOUD.md](doc/GUIA_ORACLE_CLOUD.md)**
   - Setup inicial del servidor Oracle Cloud
   - Instalación de dependencias
   - Configuración de Base de Datos
   - SSL y Nginx
   - **Lectura OBLIGATORIA para primera instalación**

2. **[📖 SETUP_REPOSITORIO_BARE.md](doc/SETUP_REPOSITORIO_BARE.md)**
   - Crear repositorio bare para despliegue automático
   - Configurar hooks de despliegue
   - Conectar máquina local al servidor
   - **Lectura OBLIGATORIA para automatizar despliegues**

3. **[✅ CHECKLIST_DESPLIEGUE.md](doc/CHECKLIST_DESPLIEGUE.md)**
   - Verificación antes de cada despliegue
   - Troubleshooting rápido
   - Comandos útiles

### 🚀 Inicio Rápido (Oracle Cloud)

```bash
# 1. Crear instancia en Oracle Cloud Console
# - Seleccionar: VM.Standard.E2.1.Micro (Capa Gratuita)
# - Descargar SSH key

# 2. Conectarse y configurar
ssh -i ~/.ssh/logisteia_private_key opc@<IP_PUBLICA>

# 3. Seguir pasos en GUIA_ORACLE_CLOUD.md
# Esto instala Java 25, MySQL, Git, etc.

# 4. Configurar repositorio bare
# Seguir pasos en SETUP_REPOSITORIO_BARE.md

# 5. En tu máquina local
git remote add oracle ssh://logisteia@<IP>/home/logisteia/git/logisteia.git
git push oracle main

# 6. ¡Despliegue automático!
# Revisar logs:
ssh logisteia@<IP> tail -f ~/logs/app.log
```

---

## 🚀 Inicio Rápido (Local)

### Requisitos
- Java 25 JDK
- Maven 3.9.x
- MySQL 8.0

### Pasos

```bash
# 1. Clonar repositorio
git clone https://github.com/tu-usuario/logisteia.git
cd logisteia

# 2. Configurar BD
mysql -u root -p << EOF
CREATE DATABASE Logisteia CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'logisteia'@'localhost' IDENTIFIED BY 'password123';
GRANT ALL PRIVILEGES ON Logisteia.* TO 'logisteia'@'localhost';
FLUSH PRIVILEGES;
EOF

# 3. Configurar variables de entorno
cp .env.oracle.template .env
nano .env  # Editar con tus valores

# 4. Compilar y ejecutar tests
mvn clean test

# 5. Construir aplicación
mvn clean package

# 6. Ejecutar
java -jar target/logisteia-backend-1.0.0.jar

# Acceder a: http://localhost:8080/api/health
```

---

## 🔒 Seguridad

### Estado de Seguridad

✅ **Sin vulnerabilidades críticas**

- Java 25 LTS (última versión segura)
- Tomcat 11.0.22 (todos los CVEs parchados)
- Spring Boot 4.0.6 con actualizaciones de seguridad
- Dependencias verificadas con CVE scanner

### Reporte de Vulnerabilidades

Último escaneo: 20 de mayo de 2026
- **CVEs encontrados inicialmente**: 7 (Tomcat 11.0.21)
- **CVEs parchados**: 7/7 (100%)
- **CVEs actuales**: 0

[Ver detalles del escaneo de seguridad](doc/SEGURIDAD.md)

---

## 📚 Documentación

### Desarrollo
- [Manual de Instalación](doc/manual_instalacion.md)
- [Manual del Programador](doc/manual_programador.md)
- [Arquitectura Tecnológica](doc/analisis/arquitectura_tecnologica.md)
- [Diccionario de Datos](doc/analisis/diccionario_de_datos.md)

### Despliegue
- [🌐 Guía Oracle Cloud](doc/GUIA_ORACLE_CLOUD.md) ⭐ **EMPEZAR AQUÍ**
- [📦 Setup Repositorio Bare](doc/SETUP_REPOSITORIO_BARE.md)
- [✅ Checklist Despliegue](doc/CHECKLIST_DESPLIEGUE.md)

### Sprints
- [Sprint 2 Backlog](doc/sprints/Sprint%20Backlog%202.md)
- [Sprint 3 Backlog](doc/sprints/Sprint%20Backlog%203.md)

---

## 🛠️ Comandos Útiles

### Desarrollo Local

```bash
# Compilar
mvn clean compile

# Tests
mvn clean test

# Empaquetar
mvn clean package -DskipTests

# Ejecutar
mvn spring-boot:run

# Limpiar
mvn clean
```

### Oracle Cloud (Después de configurado)

```bash
# Ver logs
ssh logisteia@<IP> tail -f ~/logs/app.log

# Verificar salud
curl http://<IP>:8080/api/health

# Redeploy
git push oracle main

# Status
ssh logisteia@<IP> ps aux | grep java
```

### Base de Datos

```bash
# Conectarse a MySQL
mysql -u logisteia -p Logisteia

# Ver usuarios
SELECT * FROM usuarios;

# Backup
mysqldump -u logisteia -p Logisteia > backup.sql

# Restaurar
mysql -u logisteia -p Logisteia < backup.sql
```

---

## 📊 Estadísticas del Proyecto

| Métrica | Valor |
|---------|-------|
| Archivos Java | 110 |
| Tests | 64 |
| Tasa de Prueba | 100% |
| Líneas de Código | ~4,000 |
| Dependencias | 25+ |
| Vulnerabilidades | 0 |

---

## 🎯 Próximos Pasos

- [ ] Crear instancia en Oracle Cloud
- [ ] Seguir [GUIA_ORACLE_CLOUD.md](doc/GUIA_ORACLE_CLOUD.md)
- [ ] Configurar repositorio bare (SETUP_REPOSITORIO_BARE.md)
- [ ] Hacer primer push: `git push oracle main`
- [ ] Verificar logs de despliegue
- [ ] Configurar dominio y SSL
- [ ] Configurar backups automáticos

---

## 📞 Soporte

Si tienes problemas:

1. **Revisa logs**: `tail -f ~/logs/app.log`
2. **Consulta Troubleshooting**: [CHECKLIST_DESPLIEGUE.md](doc/CHECKLIST_DESPLIEGUE.md#troubleshooting)
3. **Verifica requisitos**: [CHECKLIST_DESPLIEGUE.md](doc/CHECKLIST_DESPLIEGUE.md)

---

## 📄 Licencia

Este proyecto está bajo licencia [YOUR_LICENSE].

---

## 👨‍💻 Autores

Desarrollado por: [Tu Nombre]
Última actualización: 20 de mayo de 2026
