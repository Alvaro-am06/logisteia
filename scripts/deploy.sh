#!/bin/bash
#######################################
# Script de Despliegue Automatizado
# Logisteia Backend en Oracle Cloud
#######################################

set -e

# Colores para output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Configuración
JAVA_HOME=${JAVA_HOME:-/usr/lib/jvm/java-25-openjdk}
APP_PORT=8080
APP_JAR_NAME="logisteia-backend-1.0.0.jar"
APP_DIR="/home/logisteia/logisteia"
LOG_DIR="/home/logisteia/logs"
PID_FILE="/home/logisteia/logisteia.pid"
BACKUP_DIR="/home/logisteia/backups"

# Crear directorios si no existen
mkdir -p "$LOG_DIR" "$BACKUP_DIR"

echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}🚀 Iniciando despliegue de Logisteia${NC}"
echo -e "${YELLOW}========================================${NC}"

# 1. Validar Java
echo -e "\n${YELLOW}1️⃣  Verificando Java...${NC}"
if ! command -v $JAVA_HOME/bin/java &> /dev/null; then
    echo -e "${RED}❌ Java no encontrado en $JAVA_HOME${NC}"
    exit 1
fi
JAVA_VERSION=$($JAVA_HOME/bin/java -version 2>&1 | grep "java version" | awk '{print $3}' | tr -d '"')
echo -e "${GREEN}✅ Java $JAVA_VERSION encontrado${NC}"

# 2. Cambiar a directorio de aplicación
echo -e "\n${YELLOW}2️⃣  Cambiando a directorio: $APP_DIR${NC}"
cd "$APP_DIR"
if [ ! -f "pom.xml" ]; then
    echo -e "${RED}❌ pom.xml no encontrado en $APP_DIR${NC}"
    exit 1
fi
echo -e "${GREEN}✅ Directorio válido${NC}"

# 3. Hacer backup si existe una versión anterior
echo -e "\n${YELLOW}3️⃣  Creando backup...${NC}"
if [ -d ".git" ]; then
    TIMESTAMP=$(date +%Y%m%d_%H%M%S)
    BACKUP_PATH="$BACKUP_DIR/logisteia_backup_$TIMESTAMP"
    echo "   📦 Respaldando código actual en: $BACKUP_PATH"
    cp -r . "$BACKUP_PATH" 2>/dev/null || true
    echo -e "${GREEN}✅ Backup creado${NC}"
else
    echo -e "${YELLOW}⚠️  No hay código anterior para respaldar${NC}"
fi

# 4. Compilar proyecto
echo -e "\n${YELLOW}4️⃣  Compilando proyecto con Maven...${NC}"
export JAVA_HOME
if mvn clean package -DskipTests -q; then
    echo -e "${GREEN}✅ Compilación exitosa${NC}"
else
    echo -e "${RED}❌ Error en compilación${NC}"
    exit 1
fi

# 5. Verificar JAR generado
echo -e "\n${YELLOW}5️⃣  Verificando JAR...${NC}"
if [ ! -f "target/$APP_JAR_NAME" ]; then
    echo -e "${RED}❌ JAR no encontrado: target/$APP_JAR_NAME${NC}"
    exit 1
fi
JAR_SIZE=$(du -h "target/$APP_JAR_NAME" | cut -f1)
echo -e "${GREEN}✅ JAR verificado (${JAR_SIZE})${NC}"

# 6. Detener aplicación anterior
echo -e "\n${YELLOW}6️⃣  Deteniendo aplicación anterior...${NC}"
if [ -f "$PID_FILE" ]; then
    OLD_PID=$(cat "$PID_FILE")
    if kill -0 "$OLD_PID" 2>/dev/null; then
        echo "   ⛔ Deteniendo proceso (PID: $OLD_PID)..."
        kill "$OLD_PID"
        sleep 3
        # Enviar SIGKILL si aún está ejecutándose
        if kill -0 "$OLD_PID" 2>/dev/null; then
            echo "   ⚠️  Enviando SIGKILL..."
            kill -9 "$OLD_PID"
        fi
        echo -e "${GREEN}✅ Proceso anterior detenido${NC}"
    else
        echo -e "${YELLOW}⚠️  Proceso anterior no está ejecutándose${NC}"
    fi
    rm -f "$PID_FILE"
else
    echo -e "${YELLOW}⚠️  Ningún PID anterior encontrado${NC}"
fi

# 7. Esperar puertos libres
echo -e "\n${YELLOW}7️⃣  Esperando a que puertos se liberen...${NC}"
sleep 2

# 8. Iniciar nueva aplicación
echo -e "\n${YELLOW}8️⃣  Iniciando nueva aplicación...${NC}"
echo "   📌 Puerto: $APP_PORT"
echo "   📁 JAR: target/$APP_JAR_NAME"
echo "   📊 Logs: $LOG_DIR/app.log"

# Leer contraseña de BD si existe
DB_PASSWORD=""
if [ -f ~/.db_password ]; then
    DB_PASSWORD=$(cat ~/.db_password)
fi

# Iniciar aplicación
nohup "$JAVA_HOME/bin/java" \
    -Xmx512m \
    -Xms256m \
    -Dfile.encoding=UTF-8 \
    -jar "target/$APP_JAR_NAME" \
    --server.port="$APP_PORT" \
    --spring.datasource.url="jdbc:mysql://localhost:3306/Logisteia?useSSL=false&serverTimezone=UTC" \
    --spring.datasource.username="logisteia" \
    --spring.datasource.password="$DB_PASSWORD" \
    --spring.jpa.hibernate.ddl-auto="update" \
    > "$LOG_DIR/app.log" 2>&1 &

NEW_PID=$!
echo $NEW_PID > "$PID_FILE"
echo -e "${GREEN}✅ Aplicación iniciada (PID: $NEW_PID)${NC}"

# 9. Esperar a que la aplicación esté lista
echo -e "\n${YELLOW}9️⃣  Esperando a que la aplicación esté lista...${NC}"
RETRY_COUNT=0
MAX_RETRIES=30
while [ $RETRY_COUNT -lt $MAX_RETRIES ]; do
    if curl -s http://localhost:$APP_PORT/api/health > /dev/null 2>&1; then
        echo -e "${GREEN}✅ Aplicación está lista${NC}"
        break
    fi
    RETRY_COUNT=$((RETRY_COUNT + 1))
    echo "   ⏳ Intento $RETRY_COUNT/$MAX_RETRIES..."
    sleep 2
done

if [ $RETRY_COUNT -eq $MAX_RETRIES ]; then
    echo -e "${YELLOW}⚠️  Timeout esperando aplicación, pero podría estar iniciando...${NC}"
    echo -e "${YELLOW}   Revisa los logs: tail -f $LOG_DIR/app.log${NC}"
fi

# 10. Resumen final
echo -e "\n${YELLOW}========================================${NC}"
echo -e "${GREEN}✅ DESPLIEGUE COMPLETADO EXITOSAMENTE${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""
echo -e "${GREEN}📊 Estado de la Aplicación:${NC}"
echo "   🔸 Aplicación: http://localhost:$APP_PORT"
echo "   🔸 API Health: http://localhost:$APP_PORT/api/health"
echo "   🔸 Logs: $LOG_DIR/app.log"
echo "   🔸 PID: $NEW_PID"
echo ""
echo -e "${GREEN}🔧 Comandos Útiles:${NC}"
echo "   Ver logs:    tail -f $LOG_DIR/app.log"
echo "   Ver estado:  ps aux | grep java"
echo "   Detener:     kill $NEW_PID"
echo "   Reiniciar:   ./scripts/deploy.sh"
echo ""
echo -e "${YELLOW}Despliegue realizado en $(date '+%Y-%m-%d %H:%M:%S')${NC}"
