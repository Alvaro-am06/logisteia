#!/bin/bash
# Script para verificar las credenciales de la base de datos y el error en proyectos

echo "================================================="
echo "DIAGNÓSTICO DE LOGISTEIA"
echo "================================================="
echo ""

# 1. Verificar variables de entorno
echo "1. Variables de entorno de la base de datos:"
docker compose exec backend printenv | grep -E "DB_|MYSQL_"
echo ""

# 2. Intentar conectar a la base de datos con diferentes credenciales
echo "2. Probando conexión a MySQL:"
echo "   a) Con usuario root:"
docker compose exec db mysql -uroot -proot_password -e "SHOW DATABASES;" 2>&1 | head -5
echo ""
echo "   b) Con usuario logisteia:"
docker compose exec db mysql -ulogisteia -plogisteia_password -e "SHOW DATABASES;" 2>&1 | head -5
echo ""

# 3. Ver estructura de la tabla proyectos
echo "3. Estructura de la tabla proyectos:"
docker compose exec db mysql -uroot -proot_password Logisteia -e "DESCRIBE proyectos;" 2>&1
echo ""

# 4. Ver logs de PHP con errores
echo "4. Últimos errores de PHP:"
docker compose logs backend --tail=200 | grep -i "error\|fatal\|exception" | tail -20
echo ""

# 5. Verificar que el archivo proyectos.php existe
echo "5. Verificando archivos:"
docker compose exec backend ls -la /var/www/html/api/ | grep proyectos
echo ""

# 6. Probar endpoint de proyectos manualmente
echo "6. Probando endpoint de proyectos:"
docker compose exec backend php -r "
require_once '/var/www/html/config/config.php';
require_once '/var/www/html/modelos/ConexionBBDD.php';
try {
    \$conn = ConexionBBDD::obtener();
    echo '✅ Conexión a BD exitosa\n';
    \$stmt = \$conn->query('SELECT COUNT(*) as total FROM proyectos');
    \$result = \$stmt->fetch(PDO::FETCH_ASSOC);
    echo '✅ Tabla proyectos existe. Total registros: ' . \$result['total'] . '\n';
} catch (Exception \$e) {
    echo '❌ Error: ' . \$e->getMessage() . '\n';
}
"
echo ""

echo "================================================="
echo "FIN DEL DIAGNÓSTICO"
echo "================================================="
