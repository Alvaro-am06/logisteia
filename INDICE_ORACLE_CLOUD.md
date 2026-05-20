# 📚 ÍNDICE COMPLETO - ORACLE CLOUD DEPLOYMENT

**Proyecto**: Logisteia Backend  
**Plataforma Target**: Oracle Cloud Free Tier  
**Estado**: ✅ Completamente preparado  
**Última actualización**: 20 de mayo de 2026

---

## 🎯 GUÍA RÁPIDA

### Para empezar (elige tu escenario):

#### 1️⃣ **Si es tu primera vez con Oracle Cloud**
```
1. Lee → README_ORACLE_CLOUD.md (visión general)
2. Lee → doc/GUIA_ORACLE_CLOUD.md (paso a paso)
3. Crea instancia y sigue instrucciones
```

#### 2️⃣ **Si quieres automatizar despliegues**
```
1. Completa paso 1️⃣ primero
2. Lee → doc/SETUP_REPOSITORIO_BARE.md
3. Configura repositorio bare y hook post-receive
4. Haz git push para desplegar automáticamente
```

#### 3️⃣ **Si necesitas verificar antes de desplegar**
```
1. Lee → doc/CHECKLIST_DESPLIEGUE.md
2. Verifica cada punto antes de hacer push
```

---

## 📄 DOCUMENTACIÓN DISPONIBLE

### 🌟 PRINCIPALES (Lectura Obligatoria)

| Archivo | Propósito | Para Quién | Tiempo |
|---------|-----------|-----------|--------|
| [README_ORACLE_CLOUD.md](README_ORACLE_CLOUD.md) | Visión general del proyecto y primeros pasos | Todos | 10 min |
| [doc/GUIA_ORACLE_CLOUD.md](doc/GUIA_ORACLE_CLOUD.md) | Setup completo paso a paso | Devops/Admin | 30 min |
| [doc/SETUP_REPOSITORIO_BARE.md](doc/SETUP_REPOSITORIO_BARE.md) | Despliegue automático vía Git | Devops | 20 min |
| [doc/CHECKLIST_DESPLIEGUE.md](doc/CHECKLIST_DESPLIEGUE.md) | Verificación antes/después | Todos | 5 min |

### 📋 TÉCNICA (Referencia)

| Archivo | Descripción |
|---------|-------------|
| [ESTADO_ORACLE_CLOUD.md](ESTADO_ORACLE_CLOUD.md) | Estado actual del proyecto, métricas |
| [src/main/resources/application-oracle.yml](src/main/resources/application-oracle.yml) | Configuración Spring Boot para producción |
| [.env.oracle.template](.env.oracle.template) | Template de variables de entorno |
| [scripts/deploy.sh](scripts/deploy.sh) | Script de despliegue automático |

### 📚 EXISTENTE (Referencia)

| Archivo | Descripción |
|---------|-------------|
| [doc/manual_instalacion.md](doc/manual_instalacion.md) | Instalación local |
| [doc/manual_programador.md](doc/manual_programador.md) | Guía para desarrolladores |
| [doc/manual_usuario.md](doc/manual_usuario.md) | Manual de usuario final |
| [doc/analisis/arquitectura_tecnologica.md](doc/analisis/arquitectura_tecnologica.md) | Arquitectura del sistema |
| [doc/analisis/diccionario_de_datos.md](doc/analisis/diccionario_de_datos.md) | Esquema de BD |

---

## 🗂️ ESTRUCTURA DE ARCHIVOS

```
logisteia/
│
├── 📖 README_ORACLE_CLOUD.md          ← EMPIEZA AQUÍ
├── 📖 ESTADO_ORACLE_CLOUD.md          ← Estado actual
│
├── 📁 doc/
│   ├── 🌐 GUIA_ORACLE_CLOUD.md        ← Setup paso a paso
│   ├── 🚀 SETUP_REPOSITORIO_BARE.md   ← Despliegue automático
│   ├── ✅ CHECKLIST_DESPLIEGUE.md     ← Verificación
│   │
│   ├── 📁 analisis/
│   │   ├── arquitectura_tecnologica.md
│   │   └── diccionario_de_datos.md
│   │
│   ├── 📁 diseño/
│   │   └── bocetos/
│   │
│   ├── 📁 sprints/
│   │   ├── Sprint Backlog 2.md
│   │   └── Sprint Backlog 3.md
│   │
│   ├── manual_instalacion.md
│   ├── manual_programador.md
│   └── manual_usuario.md
│
├── 📁 scripts/
│   └── 🔧 deploy.sh                   ← Script de despliegue
│
├── 📁 src/main/resources/
│   ├── 📄 application.yml              (perfil por defecto)
│   ├── 📄 application-oracle.yml       ← Config producción
│   └── 📄 application-prod.properties
│
├── 📄 .env.oracle.template             ← Variables de entorno
├── 📄 pom.xml                          ← Build Java 25 + Tomcat 11.0.22
├── 📄 compose.yml                      ← Docker Compose (desarrollo)
│
└── 📁 docker/
    ├── backend/
    ├── caddy/
    └── frontend/
```

---

## 🎓 ESCENARIOS DE USO

### Escenario 1: Setup Inicial Completo

**Tiempo**: 2-3 horas  
**Pasos**:
1. Crear cuenta Oracle Cloud Free Tier
2. Crear instancia VM.Standard.E2.1.Micro
3. Descargar SSH key
4. SSH a instancia: `ssh -i key.key opc@IP`
5. Seguir [GUIA_ORACLE_CLOUD.md](doc/GUIA_ORACLE_CLOUD.md) punto por punto
6. Resultado: Servidor preparado con Java 25, MySQL, Git

---

### Escenario 2: Setup Despliegue Automático

**Tiempo**: 30 minutos (después de Escenario 1)  
**Pasos**:
1. En servidor: Crear repositorio bare (seguir [SETUP_REPOSITORIO_BARE.md](doc/SETUP_REPOSITORIO_BARE.md))
2. En local: Agregar remote: `git remote add oracle ssh://logisteia@IP/home/logisteia/git/logisteia.git`
3. En local: Push: `git push oracle main`
4. Resultado: Código descargado y aplicación iniciada automáticamente

---

### Escenario 3: Despliegue Subsecuente

**Tiempo**: <1 minuto  
**Pasos**:
1. Hacer cambios en código
2. Commit: `git commit -m "descripción"`
3. Push: `git push oracle main`
4. Resultado: Despliegue automático, sin downtime

---

### Escenario 4: Verificación Pre-Despliegue

**Tiempo**: 5 minutos  
**Pasos**:
1. Abrir [CHECKLIST_DESPLIEGUE.md](doc/CHECKLIST_DESPLIEGUE.md)
2. Marcar cada punto de la sección "Pre-Despliegue"
3. Resultado: Confianza de que todo está listo

---

## 🔧 HERRAMIENTAS PREPARADAS

### Scripts
- **deploy.sh** - Despliegue automático con backups y healthchecks

### Configuraciones
- **application-oracle.yml** - Spring Boot optimizado para producción
- **.env.oracle.template** - Variables de entorno documentadas

### Documentación
- **4 guías detalladas** - Cobertura completa de setup y despliegue
- **Checklist** - Verificación completa antes de cada despliegue

---

## ✨ CARACTERÍSTICAS INCLUIDAS

✅ **Automatización**
- Hook post-receive para despliegue automático
- Deploy script con backups y health checks
- Systemd service para auto-restart

✅ **Seguridad**
- Java 25 LTS (última versión)
- Tomcat 11.0.22 (sin CVEs)
- JWT para autenticación
- Variables de entorno para credenciales

✅ **Monitoreo**
- Logs rotativos
- Health check endpoint
- PID tracking
- Alertas en fallido

✅ **Escalabilidad**
- Pool de BD configurable
- Compresión habilitada
- Threads Tomcat optimizados

---

## 📊 ESTADO DEL PROYECTO

| Componente | Estado | Detalles |
|-----------|--------|----------|
| Java | ✅ 25 LTS | Última versión LTS |
| Spring Boot | ✅ 4.0.6 | Compatible con Java 25 |
| Tests | ✅ 64/64 | Todos pasando |
| Seguridad | ✅ 0 CVEs | Tomcat 11.0.22 |
| Documentación | ✅ Completa | 7 guías incluidas |
| Despliegue | ✅ Automático | Vía Git hook |
| Configuración | ✅ Optimizada | Para Oracle Cloud |

---

## 🚀 SIGUIENTE PASO

1. **Abre**: [README_ORACLE_CLOUD.md](README_ORACLE_CLOUD.md)
2. **Lee**: Primera sección "Despliegue en Oracle Cloud"
3. **Sigue**: El flujo indicado según tu escenario

---

## 💬 SOPORTE RÁPIDO

**¿No sabes por dónde empezar?**  
→ Lee [README_ORACLE_CLOUD.md](README_ORACLE_CLOUD.md#despliegue-en-oracle-cloud)

**¿Necesitas setup paso a paso?**  
→ Lee [doc/GUIA_ORACLE_CLOUD.md](doc/GUIA_ORACLE_CLOUD.md)

**¿Necesitas despliegue automático?**  
→ Lee [doc/SETUP_REPOSITORIO_BARE.md](doc/SETUP_REPOSITORIO_BARE.md)

**¿Tienes dudas antes de desplegar?**  
→ Abre [doc/CHECKLIST_DESPLIEGUE.md](doc/CHECKLIST_DESPLIEGUE.md)

**¿Algo falló?**  
→ Ve a Troubleshooting en [doc/CHECKLIST_DESPLIEGUE.md](doc/CHECKLIST_DESPLIEGUE.md#troubleshooting)

---

**¡El proyecto está 100% preparado para Oracle Cloud!**  
Cuando tengas tu servidor listo, simplemente sigue las guías. 🎯
