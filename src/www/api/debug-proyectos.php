<?php
/**
 * Debug endpoint para diagnosticar error 500 en proyectos.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== DEBUG PROYECTOS ===\n\n";

// 1. Test config.php
echo "1. Cargando config.php...\n";
try {
    require_once __DIR__ . '/../config/config.php';
    echo "   ✓ config.php cargado\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

// 2. Test conexión BD
echo "\n2. Probando conexión BD...\n";
try {
    require_once __DIR__ . '/../modelos/ConexionBBDD.php';
    $conn = ConexionBBDD::obtener();
    echo "   ✓ Conexión BD exitosa\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

// 3. Test modelo Proyecto
echo "\n3. Cargando modelo Proyecto...\n";
try {
    require_once __DIR__ . '/../modelos/Proyecto.php';
    $proyecto = new Proyecto($conn);
    echo "   ✓ Modelo Proyecto cargado\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

// 4. Test obtener proyectos
echo "\n4. Obteniendo proyectos de prueba...\n";
try {
    $proyectos = $proyecto->obtenerProyectosPorJefe('JEFE001');
    echo "   ✓ Query exitoso. Proyectos encontrados: " . count($proyectos) . "\n";
    if (count($proyectos) > 0) {
        echo "   Primer proyecto: " . json_encode($proyectos[0]) . "\n";
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
    echo "   Stack trace: " . $e->getTraceAsString() . "\n";
}

// 5. Test verificarAutenticacion
echo "\n5. Probando verificarAutenticacion()...\n";
$_SERVER['HTTP_X_USER_DNI'] = '12345678A';
$usuario = verificarAutenticacion();
if ($usuario) {
    echo "   ✓ Usuario autenticado: " . json_encode($usuario) . "\n";
} else {
    echo "   ✗ Autenticación falló\n";
}

echo "\n=== FIN DEBUG ===\n";
