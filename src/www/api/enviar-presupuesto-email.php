<?php
/**
 * API endpoint para enviar un presupuesto por email al usuario/cliente.
 * 
 * Recibe POST con:
 * - numero_presupuesto: Número del presupuesto a enviar
 * - usuario_dni: DNI del usuario propietario del presupuesto
 * 
 * Devuelve JSON con éxito/error.
 */

// Cargar configuración centralizada
require_once __DIR__ . '/../config/config.php';

// Iniciar output buffering para evitar output accidental
ob_start();

// Configurar CORS
setupCors();

// Manejar preflight OPTIONS
if (handlePreflight()) {
    ob_end_clean();
    exit();
}

header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../modelos/ConexionBBDD.php';
    require_once __DIR__ . '/../modelos/Presupuesto.php';
    require_once __DIR__ . '/../modelos/Usuarios.php';
    require_once __DIR__ . '/../config/email.php';
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        ob_end_clean();
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        exit();
    }

    // Obtener datos JSON
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['numero_presupuesto']) || !isset($data['usuario_dni'])) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['error' => 'Número de presupuesto y DNI requeridos']);
        exit();
    }

    $numero = filter_var($data['numero_presupuesto'], FILTER_SANITIZE_SPECIAL_CHARS);
    $dni = filter_var($data['usuario_dni'], FILTER_SANITIZE_SPECIAL_CHARS);

    // Obtener conexión
    $db = ConexionBBDD::obtener();
    
    // Obtener datos del usuario
    $usuariosModel = new Usuarios($db);
    $usuario = $usuariosModel->obtenerPorDNI($dni);
    
    if (!$usuario) {
        ob_end_clean();
        http_response_code(404);
        echo json_encode(['error' => 'Usuario no encontrado']);
        exit();
    }

    // Obtener datos del presupuesto
    $presupuestoModel = new Presupuesto($db);
    $presupuesto = $presupuestoModel->obtenerPorNumero($numero);
    
    if (!$presupuesto) {
        ob_end_clean();
        http_response_code(404);
        echo json_encode(['error' => 'Presupuesto no encontrado']);
        exit();
    }

    // Obtener detalles del presupuesto
    $detalles = $presupuestoModel->obtenerDetalles($numero);

    // Verificar que la función de email esté disponible
    if (!function_exists('enviarEmail')) {
        ob_end_clean();
        http_response_code(500);
        echo json_encode([
            'error' => 'Función enviarEmail no encontrada',
            'message' => 'La función enviarEmail no está definida en config/email.php'
        ]);
        exit();
    }

    // Extraer email del cliente de las notas si existe
    $emailCliente = null;
    $nombreCliente = null;
    if (isset($presupuesto['notas']) && strpos($presupuesto['notas'], 'Email Cliente:') !== false) {
        // Es presupuesto del wizard con email del cliente
        if (preg_match('/Email Cliente:\s*([^\n]+)/', $presupuesto['notas'], $matches)) {
            $emailCliente = trim($matches[1]);
        }
        if (preg_match('/Cliente:\s*([^\n]+)/', $presupuesto['notas'], $matches)) {
            $nombreCliente = trim($matches[1]);
        }
    }

    // Generar HTML del presupuesto
    try {
        $htmlPresupuesto = generarHTMLPresupuestoEmail($presupuesto, $detalles);
    } catch (Exception $htmlError) {
        ob_end_clean();
        error_log("❌ Error al generar HTML: " . $htmlError->getMessage());
        http_response_code(500);
        echo json_encode([
            'error' => 'Error al generar HTML del presupuesto',
            'message' => $htmlError->getMessage()
        ]);
        exit();
    }

    // Preparar email - enviar al cliente si existe, sino al usuario
    $asunto = "Presupuesto #" . $presupuesto['numero_presupuesto'] . " - Logisteia";
    
    if ($emailCliente) {
        // Enviar al cliente
        $destinatario = $emailCliente;
        $nombreDestinatario = $nombreCliente ?: 'Cliente';
    } else {
        // Enviar al usuario (comportamiento antiguo)
        $destinatario = $usuario['email'];
        $nombreDestinatario = $usuario['nombre'];
    }

    // Enviar email
    
    try {
        $resultado = enviarEmail(
            $destinatario,
            $nombreDestinatario,
            $asunto,
            $htmlPresupuesto,
            'logisteiaa@gmail.com',
            'Logisteia'
        );
    } catch (Exception $emailSendError) {
        ob_end_clean();
        error_log("❌ Error al enviar email: " . $emailSendError->getMessage());
        http_response_code(500);
        echo json_encode([
            'error' => 'Error al enviar email',
            'message' => $emailSendError->getMessage(),
            'destinatario' => $destinatario
        ]);
        exit();
    }

    if ($resultado) {
        ob_end_clean();
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Presupuesto enviado por email correctamente a ' . $destinatario
        ]);
    } else {
        ob_end_clean();
        http_response_code(500);
        echo json_encode([
            'error' => 'Error al enviar el email',
            'destinatario' => $destinatario
        ]);
    }
    
} catch (Exception $e) {
    ob_end_clean();
    error_log('❌ Error en enviar-presupuesto-email.php: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'error' => 'Error interno del servidor',
        'message' => $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine()
    ]);
}

/**
 * Generar HTML del presupuesto para email
 */
function generarHTMLPresupuestoEmail($presupuesto, $detalles) {
    $datosCalculados = generarDatosPresupuesto($presupuesto);
    extract($datosCalculados);

    $html = "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            color: #333;
            background: #f5f5f5;
        }
        .container { 
            max-width: 800px; 
            margin: 0 auto;
            background: white;
        }
        .header {
            background: linear-gradient(135deg, #102a41 0%, #1a3f5e 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 32px;
            margin: 0 0 5px 0;
            color: white;
        }
        .header p {
            margin: 0;
            opacity: 0.9;
            font-style: italic;
        }
        .content { padding: 30px; }
        .info-bar {
            background: #f8f9fa;
            border-left: 4px solid #102a41;
            padding: 15px;
            margin-bottom: 30px;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        .info-item {
            display: flex;
            flex-direction: column;
        }
        .info-label {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .info-value {
            font-size: 16px;
            font-weight: 600;
            color: #102a41;
        }
        h2 {
            color: #102a41;
            font-size: 18px;
            margin: 30px 0 15px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid #102a41;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th {
            background: #102a41;
            color: white;
            padding: 12px;
            text-align: left;
            font-size: 13px;
            text-transform: uppercase;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #e9ecef;
        }
        .totals {
            text-align: right;
            margin-top: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 6px;
        }
        .total-row {
            display: flex;
            justify-content: flex-end;
            gap: 20px;
            padding: 8px 0;
            font-size: 15px;
        }
        .total-row.final {
            border-top: 2px solid #102a41;
            margin-top: 8px;
            padding-top: 12px;
            font-size: 20px;
            font-weight: 700;
            color: #102a41;
        }
        .wizard-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        .wizard-item {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 6px;
            border-left: 3px solid #102a41;
        }
        .wizard-item-label {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .wizard-item-value {
            font-size: 14px;
            font-weight: 500;
            color: #333;
        }
        .tech-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 8px;
        }
        .tech-tag {
            background: #102a41;
            color: white;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 12px;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #666;
            font-size: 12px;
            border-top: 1px solid #dee2e6;
        }
        .cta-button {
            background: #102a41;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            display: inline-block;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class='container'>
        <!-- Header -->
        <div class='header'>
            <h1>LOGISTEIA</h1>
            <p>Planifica con precisión. Ejecuta con control.</p>
        </div>

        <!-- Content -->
        <div class='content'>
            <p>Hola <strong>" . (isset($presupuesto['usuario_nombre']) ? $presupuesto['usuario_nombre'] : 'Cliente') . "</strong>,</p>
            <p>Te adjuntamos el presupuesto solicitado:</p>

            <!-- Info bar -->
            <div class='info-bar'>
                <div class='info-item'>
                    <span class='info-label'>Número</span>
                    <span class='info-value'>{$presupuesto['numero_presupuesto']}</span>
                </div>
                <div class='info-item'>
                    <span class='info-label'>Fecha</span>
                    <span class='info-value'>{$fecha}</span>
                </div>
                <div class='info-item'>
                    <span class='info-label'>Válido hasta</span>
                    <span class='info-value'>{$fechaValidez}</span>
                </div>
                <div class='info-item'>
                    <span class='info-label'>Validez</span>
                    <span class='info-value'>{$presupuesto['validez_dias']} días</span>
                </div>
            </div>";

            // Si es presupuesto del wizard
            if ($esWizard) {
                $html .= "<h2>Información del Proyecto</h2>";
                $html .= "<div class='wizard-grid'>";
                
                $notas = $presupuesto['notas'];
                
                if (preg_match('/Proyecto:\s*(.+?)$/m', $notas, $matches)) {
                    $html .= "<div class='wizard-item'>
                        <div class='wizard-item-label'>Proyecto</div>
                        <div class='wizard-item-value'>" . htmlspecialchars(trim($matches[1])) . "</div>
                    </div>";
                }
                
                if (preg_match('/Cliente:\s*(.+?)$/m', $notas, $matches)) {
                    $html .= "<div class='wizard-item'>
                        <div class='wizard-item-label'>Cliente</div>
                        <div class='wizard-item-value'>" . htmlspecialchars(trim($matches[1])) . "</div>
                    </div>";
                }
                
                if (preg_match('/Categoría:\s*(.+?)$/m', $notas, $matches)) {
                    $html .= "<div class='wizard-item'>
                        <div class='wizard-item-label'>Categoría</div>
                        <div class='wizard-item-value'>" . htmlspecialchars(trim($matches[1])) . "</div>
                    </div>";
                }
                
                if (preg_match('/Presupuesto:\s*(.+?)$/m', $notas, $matches)) {
                    $html .= "<div class='wizard-item'>
                        <div class='wizard-item-label'>Presupuesto</div>
                        <div class='wizard-item-value'>" . htmlspecialchars(trim($matches[1])) . "</div>
                    </div>";
                }
                
                if (preg_match('/Descripción:\s*(.+?)$/m', $notas, $matches)) {
                    $html .= "<div class='wizard-item' style='grid-column: span 2;'>
                        <div class='wizard-item-label'>Descripción</div>
                        <div class='wizard-item-value'>" . htmlspecialchars(trim($matches[1])) . "</div>
                    </div>";
                }
                
                if (preg_match('/Tecnologías:\s*(\[.+?\])/s', $notas, $matches)) {
                    $techs = json_decode($matches[1], true);
                    if ($techs) {
                        $html .= "<div class='wizard-item' style='grid-column: span 2;'>
                            <div class='wizard-item-label'>Tecnologías</div>
                            <div class='tech-tags'>";
                        foreach ($techs as $tech) {
                            $html .= "<span class='tech-tag'>" . htmlspecialchars($tech) . "</span>";
                        }
                        $html .= "</div></div>";
                    }
                }
                
                $html .= "</div>";
            } else {
                // Presupuesto clásico
                $html .= "<h2>Servicios Contratados</h2>";
                $html .= "<table>
                    <thead>
                        <tr>
                            <th>Servicio</th>
                            <th>Cantidad</th>
                            <th>Precio Unitario</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>";
                
                foreach ($detalles as $detalle) {
                    $subtotal = $detalle['cantidad'] * $detalle['preci'];
                    $html .= "<tr>
                        <td>" . htmlspecialchars($detalle['servicio_nombre']) . "</td>
                        <td>{$detalle['cantidad']}</td>
                        <td>" . number_format($detalle['preci'], 2, ',', '.') . " €</td>
                        <td>" . number_format($subtotal, 2, ',', '.') . " €</td>
                    </tr>";
                }
                
                $html .= "</tbody></table>";
            }

            // Totales
            $html .= "<div class='totals'>
                <div class='total-row'>
                    <span>Subtotal (sin IVA):</span>
                    <span><strong>{$total} €</strong></span>
                </div>
                <div class='total-row'>
                    <span>IVA (21%):</span>
                    <span><strong>{$iva} €</strong></span>
                </div>
                <div class='total-row final'>
                    <span>TOTAL:</span>
                    <span>{$totalIVA} €</span>
                </div>
            </div>";

            $html .= "
            <p style='margin-top: 30px; color: #666;'>
                Si tienes alguna pregunta o necesitas aclaraciones sobre este presupuesto, 
                no dudes en ponerte en contacto con nosotros.
            </p>
        </div>

        <!-- Footer -->
        <div class='footer'>
            <p><strong>Logisteia</strong> - Gestión de Proyectos y Presupuestos</p>
            <p>Documento generado automáticamente. Por favor, no responda a este email.</p>
            <p style='margin-top: 10px;'>© 2026 Logisteia. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>";

    return $html;
}
