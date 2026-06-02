# 📋 GUÍA DE DESPLIEGUE EN ORACLE CLOUD

## 1️⃣ Preparación en Oracle Cloud

### Crear una Instancia Compute

1. **Ir a Compute > Instances**
2. **Create Instance** con estas características:
   - **Nombre**: `logisteia-backend`
   - **Image**: Oracle Linux 8 (Minimal) o Ubuntu 24.04 LTS
   - **Shape**: VM.Standard.E2.1.Micro (Capa Gratuita)
   - **OCID Compartment**: Tu compartimento
   - **Boot volume**: 50 GB (SSD)
   - **Networking**: Virtual Cloud Network (VCN) con acceso público

3. **Security List (Inbound Rules)**:
   - Port 22 (SSH) - Tu IP
   - Port 80 (HTTP) - 0.0.0.0/0
   - Port 443 (HTTPS) - 0.0.0.0/0
   - Port 3306 (MySQL) - Solo si es necesario remoto
   - Port 8080 (Spring Boot) - 0.0.0.0/0 (opcional)

### Obtener la Clave SSH

1. Durante la creación de la instancia, descarga el archivo `.key`
2. Guárdalo en `~/.ssh/logisteia_private_key`
3. Asigna permisos: `chmod 600 ~/.ssh/logisteia_private_key`

---

## 2️⃣ Setup Inicial en el Servidor

### Conectarse a la Instancia

```bash
ssh -i ~/.ssh/logisteia_private_key opc@<IP_PUBLICA>
```

### Instalar Requisitos

```bash
# Actualizar sistema
sudo yum update -y
# O en Ubuntu:
# sudo apt update && sudo apt upgrade -y

# Instalar Java 25 LTS
sudo yum install -y java-25-openjdk-devel
# O en Ubuntu:
# sudo apt install -y openjdk-25-jdk-headless

# Instalar MySQL 8.0
sudo yum install -y mysql-server
# O en Ubuntu:
# sudo apt install -y mysql-server

# Instalar Git
sudo yum install -y git
# O en Ubuntu:
# sudo apt install -y git

# Instalar Maven 3.9.x
curl -O https://archive.apache.org/dist/maven/maven-3/3.9.6/binaries/apache-maven-3.9.6-bin.tar.gz
sudo tar xzf apache-maven-3.9.6-bin.tar.gz -C /opt/
sudo ln -s /opt/apache-maven-3.9.6 /opt/maven

# Agregar Maven a PATH
echo 'export PATH="/opt/maven/bin:$PATH"' >> ~/.bashrc
source ~/.bashrc

# Instalar Docker
sudo yum install -y docker
sudo systemctl start docker
sudo systemctl enable docker
sudo usermod -aG docker opc
```

### Crear Usuario de Despliegue

```bash
sudo useradd -m -s /bin/bash logisteia
sudo su - logisteia
```

---

## 3️⃣ Configurar Repositorio Bare (Opción A: Manual)

### En el Servidor

```bash
# Como usuario logisteia
mkdir -p ~/git/logisteia.git
cd ~/git/logisteia.git
git init --bare

# Crear hook de despliegue automático
mkdir -p ~/deployment
cat > ~/git/logisteia.git/hooks/post-receive << 'EOF'
#!/bin/bash
REPO_DIR="/home/logisteia/git/logisteia.git"
WORK_DIR="/home/logisteia/logisteia"
BACKUP_DIR="/home/logisteia/backups"

# Crear directorio de trabajo si no existe
mkdir -p $WORK_DIR $BACKUP_DIR

# Hacer backup
if [ -d "$WORK_DIR/.git" ]; then
  TIMESTAMP=$(date +%Y%m%d_%H%M%S)
  cp -r $WORK_DIR $BACKUP_DIR/logisteia_backup_$TIMESTAMP
fi

# Desplegar código
cd $WORK_DIR
git --git-dir=$REPO_DIR --work-tree=$WORK_DIR reset --hard

# Ejecutar despliegue
cd $WORK_DIR
./scripts/deploy.sh

echo "✅ Despliegue completado en $(date)"
EOF

chmod +x ~/git/logisteia.git/hooks/post-receive
```

### En tu Máquina Local

```bash
# Agregar remote de Oracle Cloud
git remote add oracle ssh://logisteia@<IP_PUBLICA>/home/logisteia/git/logisteia.git

# Hacer push
git push oracle appmod/security-fix-20260520145300:main
# O la rama que quieras desplegar
```

---

## 4️⃣ Script de Despliegue

### Crear `scripts/deploy.sh`

```bash
#!/bin/bash
set -e

echo "🚀 Iniciando despliegue de Logisteia..."

# Variables
JAVA_HOME=/usr/lib/jvm/java-25-openjdk
APP_PORT=8080
APP_JAR_NAME=logisteia-backend-1.0.0.jar
PID_FILE=/home/logisteia/logisteia.pid

# 1. Compilar y empaquetar
echo "📦 Compilando..."
export JAVA_HOME
cd /home/logisteia/logisteia
mvn clean package -DskipTests -q

# 2. Detener aplicación anterior
if [ -f "$PID_FILE" ]; then
  OLD_PID=$(cat $PID_FILE)
  if kill -0 $OLD_PID 2>/dev/null; then
    echo "⛔ Deteniendo aplicación anterior (PID: $OLD_PID)..."
    kill $OLD_PID
    sleep 3
  fi
fi

# 3. Iniciar nueva aplicación
echo "🟢 Iniciando aplicación..."
nohup java -jar target/$APP_JAR_NAME \
  --server.port=$APP_PORT \
  --spring.datasource.url=jdbc:mysql://localhost:3306/Logisteia \
  --spring.datasource.username=logisteia \
  --spring.datasource.password=$(cat ~/.db_password) \
  --spring.jpa.hibernate.ddl-auto=update \
  > /home/logisteia/logs/app.log 2>&1 &

NEW_PID=$!
echo $NEW_PID > $PID_FILE

echo "✅ Aplicación iniciada (PID: $NEW_PID)"
echo "📊 Logs disponibles en: /home/logisteia/logs/app.log"
```

### Hacer ejecutable

```bash
chmod +x scripts/deploy.sh
```

---

## 5️⃣ Configuración de Base de Datos

### Crear Usuario MySQL

```bash
sudo mysql -u root -p << EOF
CREATE DATABASE Logisteia CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'logisteia'@'localhost' IDENTIFIED BY 'TuContraseñaSegura123';
GRANT ALL PRIVILEGES ON Logisteia.* TO 'logisteia'@'localhost';
FLUSH PRIVILEGES;
EXIT;
EOF

# Guardar contraseña en archivo seguro
echo "TuContraseñaSegura123" > ~/.db_password
chmod 600 ~/.db_password
```

### Importar esquema inicial (Opcional)

```bash
mysql -u logisteia -p Logisteia < src/sql/bbdd.sql
```

---

## 6️⃣ Configuración de Application Properties

### `src/main/resources/application.yml` (Oracle Cloud)

```yaml
spring:
  application:
    name: logisteia-backend
  datasource:
    url: jdbc:mysql://localhost:3306/Logisteia?useSSL=false&serverTimezone=UTC
    username: logisteia
    password: ${DB_PASSWORD:}
    driver-class-name: com.mysql.cj.jdbc.Driver
    hikari:
      maximum-pool-size: 10
      minimum-idle: 2
  jpa:
    hibernate:
      ddl-auto: update
    show-sql: false
    properties:
      hibernate:
        dialect: org.hibernate.dialect.MySQL8Dialect
        format_sql: true

server:
  port: 8080
  servlet:
    context-path: /api

logging:
  level:
    root: WARN
    com.logisteia.backend: INFO
  file:
    name: /home/logisteia/logs/app.log
```

---

## 7️⃣ Firewall y Nginx (Proxy Reverso - Opcional)

### Instalar Nginx

```bash
# Oracle Linux
sudo yum install -y nginx

# Ubuntu
# sudo apt install -y nginx

# Iniciar servicio
sudo systemctl start nginx
sudo systemctl enable nginx
```

### Configurar Nginx

```bash
sudo tee /etc/nginx/conf.d/logisteia.conf > /dev/null << 'EOF'
upstream logisteia_backend {
    server localhost:8080;
}

server {
    listen 80;
    server_name _;
    client_max_body_size 50M;

    location / {
        proxy_pass http://logisteia_backend;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_redirect off;
    }

    # Health check
    location /health {
        access_log off;
        return 200 "OK\n";
        add_header Content-Type text/plain;
    }
}
EOF

sudo nginx -t
sudo systemctl reload nginx
```

---

## 8️⃣ Certificado SSL (Let's Encrypt)

```bash
# Instalar Certbot
sudo yum install -y certbot python3-certbot-nginx
# O en Ubuntu:
# sudo apt install -y certbot python3-certbot-nginx

# Solicitar certificado
sudo certbot --nginx -d tu-dominio.com

# Auto-renovación
sudo systemctl enable certbot.timer
```

---

## 9️⃣ Monitoreo y Logs

### Ver logs en tiempo real

```bash
tail -f /home/logisteia/logs/app.log
```

### Monitorear procesos

```bash
# Ver si está ejecutándose
ps aux | grep java

# Ver puertos en uso
netstat -tlnp | grep java

# Ver uso de recursos
top -p $(cat /home/logisteia/logisteia.pid)
```

### Crear servicio systemd (Opcional pero recomendado)

```bash
sudo tee /etc/systemd/system/logisteia.service > /dev/null << 'EOF'
[Unit]
Description=Logisteia Backend
After=network.target

[Service]
Type=simple
User=logisteia
WorkingDirectory=/home/logisteia/logisteia
Environment="JAVA_HOME=/usr/lib/jvm/java-25-openjdk"
ExecStart=/usr/lib/jvm/java-25-openjdk/bin/java -jar target/logisteia-backend-1.0.0.jar
Restart=on-failure
RestartSec=10

[Install]
WantedBy=multi-user.target
EOF

sudo systemctl daemon-reload
sudo systemctl enable logisteia
sudo systemctl start logisteia
```

---

## 🔟 Paso a Paso Final

### Resumen de despliegue

1. ✅ **Java 25 instalado**
2. ✅ **MySQL 8.0 configurado**
3. ✅ **Repositorio bare preparado**
4. ✅ **Script deploy.sh listo**
5. ✅ **Nginx actuando como proxy**
6. ✅ **SSL configurado**
7. ✅ **Systemd para auto-inicio**

### Despliegue inicial

```bash
# Desde tu máquina local
cd logisteia
git push oracle appmod/security-fix-20260520145300:main

# Desde el servidor (si es necesario)
cd /home/logisteia/logisteia
./scripts/deploy.sh
```

### Verificar despliegue

```bash
curl http://localhost:8080/api/health
# O desde tu máquina:
curl http://<IP_PUBLICA>/api/health
```

---

## ⚠️ Notas Importantes

- **Backup**: Los datos de MySQL están en `/var/lib/mysql` (considerar backup automático)
- **Logs**: Revisar `/home/logisteia/logs/app.log` en caso de problemas
- **Seguridad**: Cambiar contraseñas por defecto y configurar firewall adecuadamente
- **Escalabilidad**: Si necesitas más recursos, cambiar el Shape de la instancia

**¿Necesitas ayuda con algo específico?**
