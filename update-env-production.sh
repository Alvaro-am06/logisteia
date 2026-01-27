#!/bin/bash
# Script para actualizar JWT_SECRET en producción
# Ejecutar en el servidor: bash update-env-production.sh

ENV_FILE="/home/ubuntu/logisteia/.env"

# Verificar si el archivo existe
if [ ! -f "$ENV_FILE" ]; then
    echo "Error: El archivo $ENV_FILE no existe"
    exit 1
fi

# Nueva clave JWT segura (64 caracteres hexadecimales)
NEW_JWT_SECRET="10ab8d6f51e4276cafc7a1fb44eddf1efb07f21679ad1155a5afe1a22088d4eb"

# Actualizar JWT_SECRET en el archivo .env
if grep -q "^JWT_SECRET=" "$ENV_FILE"; then
    # Si existe, reemplazarla
    sed -i "s|^JWT_SECRET=.*|JWT_SECRET=$NEW_JWT_SECRET|" "$ENV_FILE"
    echo "✅ JWT_SECRET actualizada en $ENV_FILE"
else
    # Si no existe, agregarla
    echo "JWT_SECRET=$NEW_JWT_SECRET" >> "$ENV_FILE"
    echo "✅ JWT_SECRET agregada a $ENV_FILE"
fi

# Reiniciar el contenedor backend para que tome los cambios
echo "🔄 Reiniciando contenedor backend..."
cd /home/ubuntu/logisteia
docker compose restart backend

echo "✅ Actualización completada"
echo ""
echo "Contenido del .env (JWT_SECRET):"
grep "^JWT_SECRET=" "$ENV_FILE"
