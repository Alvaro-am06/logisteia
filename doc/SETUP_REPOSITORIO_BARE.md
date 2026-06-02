# 📚 GUÍA: CONFIGURAR REPOSITORIO BARE EN ORACLE CLOUD

## ¿Qué es un Repositorio Bare?

Un **repositorio bare** es un repositorio Git sin directorio de trabajo. Es ideal para usarlo como servidor central donde hacer push de cambios que se despliegan automáticamente.

---

## OPCIÓN 1: Setup Manual del Repositorio Bare

### Paso 1: Conectarse al Servidor Oracle Cloud

```bash
# Desde tu máquina local
ssh -i ~/.ssh/logisteia_private_key opc@<IP_PUBLICA>

# Cambiar a usuario logisteia
sudo su - logisteia
```

### Paso 2: Crear el Repositorio Bare

```bash
# Crear estructura de directorios
mkdir -p ~/git/logisteia.git
cd ~/git/logisteia.git

# Inicializar como repositorio bare
git init --bare

# Configurar permisos
chmod -R 755 ~/git/logisteia.git
```

### Paso 3: Crear el Directorio de Trabajo

```bash
# Este es donde se desplegará el código
mkdir -p ~/logisteia
cd ~/logisteia
git init
```

### Paso 4: Crear Hook de Despliegue Automático

```bash
# Crear archivo de hook
cat > ~/git/logisteia.git/hooks/post-receive << 'EOF'
#!/bin/bash

# Configuración
REPO_DIR="/home/logisteia/git/logisteia.git"
WORK_DIR="/home/logisteia/logisteia"
BACKUP_DIR="/home/logisteia/backups"
LOG_FILE="/home/logisteia/logs/deploy.log"

# Crear directorio de logs si no existe
mkdir -p $(dirname $LOG_FILE)

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" >> $LOG_FILE
echo "Despliegue iniciado: $(date '+%Y-%m-%d %H:%M:%S')" >> $LOG_FILE
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" >> $LOG_FILE

# Crear directorio de trabajo si no existe
mkdir -p $WORK_DIR $BACKUP_DIR

# Hacer backup del código anterior
if [ -d "$WORK_DIR/.git" ]; then
  TIMESTAMP=$(date +%Y%m%d_%H%M%S)
  echo "📦 Creando backup..." >> $LOG_FILE
  cp -r $WORK_DIR $BACKUP_DIR/logisteia_backup_$TIMESTAMP
  echo "✅ Backup creado: logisteia_backup_$TIMESTAMP" >> $LOG_FILE
fi

# Desplegar código nuevo
echo "📥 Descargando código nuevo..." >> $LOG_FILE
cd $WORK_DIR
git --git-dir=$REPO_DIR --work-tree=$WORK_DIR reset --hard

# Ejecutar script de despliegue si existe
if [ -f "$WORK_DIR/scripts/deploy.sh" ]; then
  echo "🚀 Ejecutando script de despliegue..." >> $LOG_FILE
  chmod +x $WORK_DIR/scripts/deploy.sh
  cd $WORK_DIR
  ./scripts/deploy.sh >> $LOG_FILE 2>&1
  DEPLOY_STATUS=$?
  
  if [ $DEPLOY_STATUS -eq 0 ]; then
    echo "✅ Despliegue completado exitosamente" >> $LOG_FILE
  else
    echo "❌ Error en despliegue (Código: $DEPLOY_STATUS)" >> $LOG_FILE
  fi
else
  echo "⚠️  Script deploy.sh no encontrado" >> $LOG_FILE
fi

echo "Despliegue finalizado: $(date '+%Y-%m-%d %H:%M:%S')" >> $LOG_FILE
echo "" >> $LOG_FILE

# Enviar notificación (opcional)
# curl -X POST -H 'Content-type: application/json' \
#   --data '{"text":"✅ Despliegue de Logisteia completado"}' \
#   YOUR_WEBHOOK_URL

EOF

# Hacer ejecutable
chmod +x ~/git/logisteia.git/hooks/post-receive

# Verificar
echo "✅ Hook de despliegue configurado"
```

---

## OPCIÓN 2: Automatizar Setup con Script

Si prefieres automatizar todo, ejecuta este script en el servidor:

```bash
#!/bin/bash
# setup-bare-repo.sh

LOGISTEIA_USER="logisteia"
GIT_REPO_PATH="/home/$LOGISTEIA_USER/git/logisteia.git"
WORK_DIR="/home/$LOGISTEIA_USER/logisteia"
BACKUP_DIR="/home/$LOGISTEIA_USER/backups"

# Crear directorios
sudo su - $LOGISTEIA_USER << 'EOFSCRIPT'
mkdir -p $GIT_REPO_PATH $WORK_DIR $BACKUP_DIR

# Inicializar repo bare
cd $GIT_REPO_PATH
git init --bare

# Crear hook
cat > hooks/post-receive << 'EOFHOOK'
#!/bin/bash
REPO_DIR="$GIT_REPO_PATH"
WORK_DIR="$WORK_DIR"
BACKUP_DIR="$BACKUP_DIR"
LOG_FILE="$BACKUP_DIR/deploy.log"

mkdir -p $(dirname $LOG_FILE)
echo "[$(date)] Despliegue iniciado" >> $LOG_FILE

mkdir -p $WORK_DIR $BACKUP_DIR

if [ -d "$WORK_DIR/.git" ]; then
  TIMESTAMP=$(date +%Y%m%d_%H%M%S)
  cp -r $WORK_DIR $BACKUP_DIR/logisteia_backup_$TIMESTAMP
fi

cd $WORK_DIR
git --git-dir=$REPO_DIR --work-tree=$WORK_DIR reset --hard

if [ -f "$WORK_DIR/scripts/deploy.sh" ]; then
  chmod +x $WORK_DIR/scripts/deploy.sh
  cd $WORK_DIR
  ./scripts/deploy.sh >> $LOG_FILE 2>&1
fi

echo "[$(date)] Despliegue completado" >> $LOG_FILE
EOFHOOK

chmod +x hooks/post-receive

echo "✅ Repositorio bare configurado en $GIT_REPO_PATH"
EOFSCRIPT
```

---

## PASO 3: Configurar Remote en tu Máquina Local

### Agregar el remoto de Oracle Cloud

```bash
# En tu máquina local, dentro del repositorio logisteia
cd ~/Documents/Repositorios/logisteia

# Agregar remoto (reemplazar <IP_PUBLICA> con la IP de Oracle Cloud)
git remote add oracle ssh://logisteia@<IP_PUBLICA>/home/logisteia/git/logisteia.git

# Verificar que se agregó
git remote -v
# Deberías ver:
# oracle  ssh://logisteia@<IP_PUBLICA>/home/logisteia/git/logisteia.git (fetch)
# oracle  ssh://logisteia@<IP_PUBLICA>/home/logisteia/git/logisteia.git (push)
```

---

## PASO 4: Hacer Push para Desplegar

### Primera vez: Pushear rama actual

```bash
# Ver rama actual
git branch -a

# Hacer push (por ejemplo, de security-fix-20260520145300)
git push oracle appmod/security-fix-20260520145300:main

# O si quieres usar otra rama
git push oracle tu-rama:main
```

### Subsecuentes: Simplemente hacer push

```bash
# Una vez configurado, solo necesitas:
git push oracle main

# Esto descargará el código y ejecutará deploy.sh automáticamente
```

---

## PASO 5: Monitorear Despliegue

### En tiempo real

```bash
# Conectarse al servidor
ssh -i ~/.ssh/logisteia_private_key logisteia@<IP_PUBLICA>

# Ver logs de despliegue
tail -f ~/backups/deploy.log

# Ver logs de aplicación
tail -f ~/logs/app.log

# Ver si está ejecutándose
ps aux | grep java
```

### Ver estado del push

```bash
# En tu máquina local, cuando hagas git push
git push oracle main
# Verás el output de deploy.sh en tiempo real
```

---

## TROUBLESHOOTING

### Problema: "Permission denied (publickey)"

**Solución:**
```bash
# Asegurar que la clave SSH está agregada
ssh-add ~/.ssh/logisteia_private_key

# O usar la clave explícitamente
GIT_SSH_COMMAND="ssh -i ~/.ssh/logisteia_private_key" git push oracle main
```

### Problema: Hook no se ejecuta

**Verificar:**
```bash
# En el servidor, verificar permisos del hook
ls -la ~/git/logisteia.git/hooks/post-receive

# Debe tener permisos 755
chmod +x ~/git/logisteia.git/hooks/post-receive
```

### Problema: Despliegue falla silenciosamente

**Revisar logs:**
```bash
# En el servidor
tail -f ~/logs/deploy.log
tail -f ~/logs/app.log

# Verificar si hay errores de compilación
cd ~/logisteia
mvn clean package -DskipTests
```

### Problema: Puerto 8080 ya en uso

```bash
# En el servidor, buscar procesos en ese puerto
lsof -i :8080

# O cambiar puerto en deploy.sh
# Editar APP_PORT=8080 a otro puerto disponible
nano scripts/deploy.sh
```

---

## ALIAS ÚTILES

Para facilitar, agrega estos alias a tu `~/.bashrc`:

```bash
# En tu máquina local
alias push-oracle='git push oracle main'
alias logs-oracle='ssh -i ~/.ssh/logisteia_private_key logisteia@<IP_PUBLICA> "tail -f ~/logs/app.log"'
alias deploy-oracle='ssh -i ~/.ssh/logisteia_private_key logisteia@<IP_PUBLICA> "~/logisteia/scripts/deploy.sh"'
alias status-oracle='ssh -i ~/.ssh/logisteia_private_key logisteia@<IP_PUBLICA> "ps aux | grep java"'
```

Luego puedes usar:
```bash
push-oracle
logs-oracle
deploy-oracle
status-oracle
```

---

## RESUMEN DE COMANDO RÁPIDO

Una sola línea para automatizar todo:

```bash
# Ejecutar en el servidor Oracle Cloud (como usuario logisteia)
mkdir -p ~/git/logisteia.git ~/logisteia ~/backups && \
cd ~/git/logisteia.git && \
git init --bare && \
cat > hooks/post-receive << 'EOF'
#!/bin/bash
REPO_DIR="/home/logisteia/git/logisteia.git"
WORK_DIR="/home/logisteia/logisteia"
mkdir -p $WORK_DIR && \
cd $WORK_DIR && \
git --git-dir=$REPO_DIR --work-tree=$WORK_DIR reset --hard && \
[ -f scripts/deploy.sh ] && chmod +x scripts/deploy.sh && ./scripts/deploy.sh
EOF
chmod +x hooks/post-receive && echo "✅ Repositorio bare listo"
```

---

## PRÓXIMOS PASOS

1. ✅ Crear repositorio bare en Oracle Cloud
2. ✅ Configurar hook de despliegue automático
3. ✅ Agregar remote en tu máquina local
4. 🔄 Hacer `git push oracle main` para desplegar
5. 📊 Monitorear logs en tiempo real
6. 🔄 Actualizar código y hacer push nuevamente
