# ✅ CHECKLIST DE DESPLIEGUE EN ORACLE CLOUD

## 🔍 Pre-Despliegue (Local)

- [ ] **Código actualizado y committeado**
  - [ ] Hacer `git status` para verificar que no hay cambios sin commit
  - [ ] Hacer `git log -1` para ver el último commit

- [ ] **Tests pasando**
  - [ ] `mvn clean test` - Todos los tests deben pasar (64/64)
  - [ ] No hay warnings de compilación

- [ ] **Construcción exitosa**
  - [ ] `mvn clean package -DskipTests` - Build sin errores
  - [ ] JAR generado correctamente en `target/logisteia-backend-1.0.0.jar`

- [ ] **Seguridad verificada**
  - [ ] Sin vulnerabilidades críticas conocidas
  - [ ] Java 25 LTS utilizado
  - [ ] Tomcat 11.0.22+ (sin CVEs)
  - [ ] Dependencias actualizadas

- [ ] **Configuración lista**
  - [ ] Variables de entorno documentadas en `.env.oracle.template`
  - [ ] `application-oracle.yml` configurado correctamente
  - [ ] Script `scripts/deploy.sh` es ejecutable

---

## 🚀 Despliegue (Oracle Cloud)

### Paso 1: Preparar Servidor

- [ ] **Instancia creada**
  - [ ] Instancia Oracle Compute activa
  - [ ] IP pública asignada
  - [ ] Acceso SSH verificado

- [ ] **Dependencias instaladas**
  - [ ] Java 25 LTS instalado
  - [ ] Maven 3.9.x instalado
  - [ ] MySQL 8.0 corriendo
  - [ ] Git instalado
  - [ ] Nginx instalado (opcional pero recomendado)

- [ ] **Usuarios y permisos**
  - [ ] Usuario `logisteia` creado
  - [ ] SSH key agregada a `~/.ssh/authorized_keys`
  - [ ] Directorios necesarios creados:
    - [ ] `~/git/logisteia.git` (repositorio bare)
    - [ ] `~/logisteia` (directorio de trabajo)
    - [ ] `~/logs` (logs de aplicación)
    - [ ] `~/backups` (respaldos)

- [ ] **Base de datos configurada**
  - [ ] Base de datos `Logisteia` creada
  - [ ] Usuario `logisteia` con permisos en BD
  - [ ] Contraseña guardada en `~/.db_password`
  - [ ] MySQL accesible localmente

- [ ] **Firewall y seguridad**
  - [ ] Puerto 22 (SSH) abierto desde tu IP
  - [ ] Puerto 80 (HTTP) abierto
  - [ ] Puerto 443 (HTTPS) abierto (si tienes dominio)
  - [ ] Puerto 8080 abierto (para acceso directo, opcional)

### Paso 2: Configurar Repositorio Bare

- [ ] **Repositorio bare inicializado**
  ```bash
  # En el servidor
  cd ~/git/logisteia.git
  git init --bare
  ```

- [ ] **Hook de despliegue creado**
  - [ ] Archivo `post-receive` existe
  - [ ] Hook es ejecutable (`chmod +x`)
  - [ ] Contiene script de deploy correcto

- [ ] **Remote agregado localmente**
  ```bash
  # En tu máquina
  git remote add oracle ssh://logisteia@<IP_PUBLICA>/home/logisteia/git/logisteia.git
  git remote -v  # Verificar que aparece
  ```

### Paso 3: Primer Despliegue

- [ ] **Push inicial**
  ```bash
  git push oracle main  # O la rama que uses
  ```

- [ ] **Despliegue automático**
  - [ ] Hook se ejecutó automáticamente
  - [ ] Código se descargó en `~/logisteia`
  - [ ] Script `deploy.sh` se ejecutó
  - [ ] Aplicación compilada (`target/JAR` existe)
  - [ ] Aplicación iniciada (PID guardado)

- [ ] **Verificar aplicación**
  - [ ] Proceso Java ejecutándose: `ps aux | grep java`
  - [ ] Puerto 8080 escuchando: `netstat -tlnp | grep 8080`
  - [ ] Health check OK: `curl http://localhost:8080/api/health`

- [ ] **Logs revisados**
  - [ ] `~/logs/app.log` muestra startup exitoso
  - [ ] Sin errores críticos en logs
  - [ ] Conexión a BD establecida

---

## 🌐 Post-Despliegue (Opcional)

- [ ] **Nginx configurado** (si lo usas como proxy)
  - [ ] `/etc/nginx/conf.d/logisteia.conf` creado
  - [ ] Nginx reiniciado: `sudo systemctl reload nginx`
  - [ ] Acceso a `http://<IP>` funciona

- [ ] **SSL configurado** (si tienes dominio)
  - [ ] Certbot instalado
  - [ ] Certificado generado
  - [ ] Renovación automática habilitada

- [ ] **Monitoreo configurado**
  - [ ] Logs con rotación automática
  - [ ] Alertas configuradas (opcional)
  - [ ] Backup automático (opcional)

- [ ] **Documentación actualizada**
  - [ ] README tiene instrucciones de despliegue
  - [ ] Credenciales guardadas de forma segura
  - [ ] IP y puertos documentados

---

## 🔄 Despliegues Posteriores

Para cada actualización subsecuente:

- [ ] **En tu máquina local**
  ```bash
  git add .
  git commit -m "Descripción de cambios"
  git push oracle main
  ```

- [ ] **En el servidor** (automático pero verifica)
  ```bash
  ssh logisteia@<IP> tail -f ~/logs/app.log
  # Esperar a ver "BUILD SUCCESS" y aplicación iniciada
  curl http://localhost:8080/api/health
  ```

---

## 🛠️ Troubleshooting

### Si algo falla:

1. **Revisar logs de despliegue**
   ```bash
   ssh logisteia@<IP>
   tail -f ~/logs/deploy.log  # Log del hook
   tail -f ~/logs/app.log     # Log de la aplicación
   ```

2. **Verificar estado de la aplicación**
   ```bash
   ps aux | grep java
   curl http://localhost:8080/api/health
   ```

3. **Verificar BD**
   ```bash
   mysql -u logisteia -p -e "SELECT 1 FROM Logisteia.usuarios LIMIT 1;"
   ```

4. **Rollback si es necesario**
   ```bash
   cd ~/logisteia
   git reset --hard HEAD~1  # Volver al commit anterior
   ./scripts/deploy.sh       # Redeploy
   ```

5. **Limpiar y redeployar**
   ```bash
   # Si el proceso quedó stuck
   pkill -f "java.*logisteia"
   rm ~/logisteia.pid
   cd ~/logisteia
   ./scripts/deploy.sh
   ```

---

## 📋 Comandos Rápidos

```bash
# Conectarse al servidor
ssh -i ~/.ssh/logisteia_private_key logisteia@<IP_PUBLICA>

# Ver logs en tiempo real
tail -f ~/logs/app.log

# Ver si está ejecutándose
ps aux | grep java

# Ver estado de salud
curl http://localhost:8080/api/health

# Detener aplicación
kill $(cat ~/logisteia.pid)

# Redeployar manualmente
./logisteia/scripts/deploy.sh

# Ver backups disponibles
ls -la ~/backups/

# Restaurar un backup
cp -r ~/backups/logisteia_backup_TIMESTAMP ~/logisteia
```

---

## ✨ CHECKLIST FINAL

Antes de considerar el despliegue completo:

- [ ] Aplicación accesible en `http://<IP>/api/health`
- [ ] Base de datos funcionando
- [ ] Logs sin errores críticos
- [ ] Tests unitarios pasando
- [ ] Sin vulnerabilidades críticas (CVEs)
- [ ] Backups funcionando
- [ ] Documentación actualizada
- [ ] Equipo notificado del despliegue
- [ ] Plan de rollback documentado

---

**¿Necesitas ayuda con algún paso? Revisa:**
- 📚 [GUIA_ORACLE_CLOUD.md](GUIA_ORACLE_CLOUD.md) - Guía completa de configuración
- 📚 [SETUP_REPOSITORIO_BARE.md](SETUP_REPOSITORIO_BARE.md) - Configuración del repositorio bare
