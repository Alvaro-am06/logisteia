#!/usr/bin/env php
<?php
/**
 * Script para enviar emails de bienvenida a trabajadores ya registrados
 * Ejecutar: php enviar_emails_trabajadores.php
 */

// Cargar configuración desde el directorio correcto
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/email.php';
require_once __DIR__ . '/../modelos/ConexionBBDD.php';

echo "========================================\n";
echo "ENVÍO DE EMAILS A TRABAJADORES\n";
echo "========================================\n\n";

try {
    $conn = ConexionBBDD::obtener();
    
    // Obtener todos los trabajadores activos que no han recibido email
    $sql = "SELECT dni, nombre, email, rol, fecha_registro
            FROM usuarios
            WHERE estado = 'activo'
            AND (rol = 'trabajador' OR rol = 'jefe_equipo')
            ORDER BY fecha_registro DESC";
    
    $stmt = $conn->query($sql);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($usuarios)) {
        echo "❌ No se encontraron usuarios para enviar emails.\n";
        exit(1);
    }
    
    echo "📊 Total de usuarios encontrados: " . count($usuarios) . "\n\n";
    
    $exitosos = 0;
    $fallidos = 0;
    
    foreach ($usuarios as $usuario) {
        echo "Procesando: {$usuario['nombre']} ({$usuario['email']})... ";
        
        $asunto = "Bienvenido a Logisteia";
        
        $rolTexto = match($usuario['rol']) {
            'jefe_equipo' => 'Jefe de Equipo',
            'trabajador' => 'Trabajador',
            'moderador' => 'Moderador',
            default => 'Usuario'
        };
        
        $mensajeHTML = "<html><body>
            <h2>¡Bienvenido a Logisteia, {$usuario['nombre']}!</h2>
            <p>Tu cuenta ha sido creada exitosamente en nuestra plataforma de gestión de proyectos.</p>
            <p><strong>Datos de tu cuenta:</strong></p>
            <ul>
                <li><strong>Email:</strong> {$usuario['email']}</li>
                <li><strong>DNI:</strong> {$usuario['dni']}</li>
                <li><strong>Rol:</strong> $rolTexto</li>
                <li><strong>Fecha de registro:</strong> " . date('d/m/Y', strtotime($usuario['fecha_registro'])) . "</li>
            </ul>
            <p>Ya puedes iniciar sesión en la plataforma con tus credenciales.</p>
            <br>
            <p><strong>Accede a la plataforma:</strong></p>
            <p><a href='https://logisteia.com' style='background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Iniciar Sesión</a></p>
            <br>
            <p>Si tienes alguna pregunta o problema, no dudes en contactarnos.</p>
            <br>
            <p>Saludos,<br>Equipo Logisteia</p>
        </body></html>";
        
        try {
            $resultado = enviarEmail(
                $usuario['email'],
                $usuario['nombre'],
                $asunto,
                $mensajeHTML,
                'logisteiaa@gmail.com',
                'Equipo Logisteia'
            );
            
            if ($resultado) {
                echo "✅ Email enviado\n";
                $exitosos++;
            } else {
                echo "❌ Error al enviar\n";
                $fallidos++;
            }
            
            // Esperar 2 segundos entre emails para no saturar el servidor SMTP
            sleep(2);
            
        } catch (Exception $e) {
            echo "❌ Excepción: " . $e->getMessage() . "\n";
            $fallidos++;
        }
    }
    
    echo "\n========================================\n";
    echo "RESUMEN\n";
    echo "========================================\n";
    echo "✅ Exitosos: $exitosos\n";
    echo "❌ Fallidos: $fallidos\n";
    echo "📊 Total: " . count($usuarios) . "\n";
    echo "========================================\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR CRÍTICO: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n✅ Script completado.\n";
?>
