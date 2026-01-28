<?php
/**
 * API endpoint para exportar un presupuesto a PDF.
 * 
 * Recibe GET con parámetro numero (numero_presupuesto)
 * Genera un PDF y lo devuelve para descarga.
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../modelos/ConexionBBDD.php';
require_once __DIR__ . '/../modelos/Presupuesto.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        exit();
    }

    if (!isset($_GET['numero']) || empty($_GET['numero'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Número de presupuesto requerido']);
        exit();
    }

    $numero = filter_var($_GET['numero'], FILTER_SANITIZE_SPECIAL_CHARS);
    $db = ConexionBBDD::obtener();
    
    // Verificar si es presupuesto wizard o clásico
    $esWizard = false;
    try {
        $queryCheck = "SELECT * FROM presupuestos_wizard WHERE numero_presupuesto = :numero";
        $stmtCheck = $db->prepare($queryCheck);
        $stmtCheck->execute([':numero' => $numero]);
        $datosWizard = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        $esWizard = $datosWizard !== false;
    } catch (Exception $e) {
        $esWizard = false;
    }
    
    if ($esWizard) {
        // Presupuesto wizard
        $datos = $datosWizard;
        $detalles = [];
    } else {
        // Presupuesto clásico
        $presupuesto = new Presupuesto($db);
        $datos = $presupuesto->obtenerPorNumero($numero);
        
        if (!$datos) {
            http_response_code(404);
            echo json_encode(['error' => 'Presupuesto no encontrado']);
            exit();
        }
        
        $detalles = $presupuesto->obtenerDetalles($numero);
    }

    // Generar HTML para imprimir/guardar como PDF desde el navegador
    header('Content-Type: text/html; charset=UTF-8');
    $html = generarHTMLPresupuesto($datos, $detalles, $esWizard);
    echo $html;
    
} catch (Exception $e) {
    http_response_code(500);
    error_log('Error en exportar-presupuesto-pdf.php: ' . $e->getMessage());
    echo json_encode(['error' => 'Error interno: ' . $e->getMessage()]);
}

function generarHTMLPresupuesto($datos, $detalles, $esWizard = false) {
    $datosCalculados = generarDatosPresupuesto($datos);
    // Usar el parámetro $esWizard si está disponible, sino detectarlo desde datos
    if (!$esWizard && isset($datosCalculados['esWizard'])) {
        $esWizard = $datosCalculados['esWizard'];
    }
    extract($datosCalculados);
    
    $html = "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Presupuesto {$datos['numero_presupuesto']}</title>
    <style>
        @media print {
            body { margin: 0; padding: 20mm; }
            .no-print { display: none; }
            @page { size: A4; margin: 0; }
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            padding: 40px;
            color: #333;
            background: white;
        }
        
        .container { max-width: 800px; margin: 0 auto; }
        
        /* Header */
        .header {
            background: linear-gradient(135deg, #102a41 0%, #1a3f5e 100%);
            color: white;
            padding: 30px;
            border-radius: 8px 8px 0 0;
            margin-bottom: 0;
        }
        
        .header h1 {
            font-size: 32px;
            margin-bottom: 5px;
            letter-spacing: 2px;
        }
        
        .header .tagline {
            font-size: 14px;
            opacity: 0.9;
            font-style: italic;
        }
        
        /* Info bar */
        .info-bar {
            background: #f8f9fa;
            border-left: 4px solid #102a41;
            padding: 20px;
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
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        
        .info-value {
            font-size: 16px;
            font-weight: 600;
            color: #102a41;
        }
        
        .estado {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }
        
        .estado.borrador { background: #e9ecef; color: #495057; }
        .estado.enviado { background: #cfe2ff; color: #084298; }
        .estado.aprobado { background: #d1e7dd; color: #0f5132; }
        .estado.rechazado { background: #f8d7da; color: #842029; }
        
        /* Section title */
        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #102a41;
            margin: 30px 0 15px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid #102a41;
        }
        
        /* Información del wizard */
        .wizard-info {
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
        
        .wizard-full {
            grid-column: span 2;
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
        
        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        thead {
            background: #102a41;
            color: white;
        }
        
        th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        th:last-child, td:last-child { text-align: right; }
        
        tbody tr {
            border-bottom: 1px solid #e9ecef;
        }
        
        tbody tr:hover {
            background: #f8f9fa;
        }
        
        td {
            padding: 12px;
            font-size: 14px;
        }
        
        /* Totals */
        .totals-section {
            margin-top: 30px;
            display: flex;
            justify-content: flex-end;
        }
        
        .totals-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            min-width: 300px;
            border: 2px solid #dee2e6;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 15px;
        }
        
        .total-row.subtotal {
            color: #666;
        }
        
        .total-row.iva {
            color: #666;
            font-size: 14px;
        }
        
        .total-row.final {
            border-top: 2px solid #102a41;
            margin-top: 8px;
            padding-top: 12px;
            font-size: 20px;
            font-weight: 700;
            color: #102a41;
        }
        
        /* Notes */
        .notes-box {
            background: #e7f3ff;
            border-left: 4px solid #0066cc;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        
        .notes-title {
            font-weight: 600;
            color: #0066cc;
            margin-bottom: 8px;
        }
        
        .notes-content {
            color: #333;
            line-height: 1.6;
        }
        
        /* Footer */
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 2px solid #dee2e6;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        
        .footer-info {
            margin-bottom: 10px;
        }
        
        /* Print button */
        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #102a41;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(16, 42, 65, 0.3);
            z-index: 1000;
        }
        
        .print-btn:hover {
            background: #1a3f5e;
        }
    </style>
</head>
<body>
    <button class='print-btn no-print' onclick='window.print()'>🖨️ Imprimir / Guardar PDF</button>
    
    <div class='container'>
        <!-- Header -->
        <div class='header'>
            <h1>LOGISTEIA</h1>
            <div class='tagline'>Planifica con precisión. Ejecuta con control.</div>
        </div>
        
        <!-- Info bar -->
        <div class='info-bar'>
            <div class='info-item'>
                <span class='info-label'>Número de Presupuesto</span>
                <span class='info-value'>{$datos['numero_presupuesto']}</span>
            </div>
            <div class='info-item'>
                <span class='info-label'>Fecha de Emisión</span>
                <span class='info-value'>{$fecha}</span>
            </div>
            <div class='info-item'>
                <span class='info-label'>Estado</span>
                <span class='estado " . strtolower($datos['estado']) . "'>" . ucfirst($datos['estado']) . "</span>
            </div>
            <div class='info-item'>
                <span class='info-label'>Válido hasta</span>
                <span class='info-value'>{$fechaValidez}</span>
            </div>
        </div>";
    
    // Si es presupuesto del wizard, mostrar información parseada
    if ($esWizard) {
        $html .= "<div class='section-title'>Información del Proyecto</div>";
        $html .= "<div class='wizard-info'>";
        
        // Parsear datos del wizard
        $notas = $datos['notas'];
        
        if (preg_match('/Proyecto:\s*(.+?)$/m', $notas, $matches) || preg_match('/Presupuesto automático para proyecto:\s*(.+?)$/mi', $notas, $matches)) {
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
        
        if (preg_match('/Tiempo estimado:\s*(.+?)$/m', $notas, $matches)) {
            $html .= "<div class='wizard-item'>
                <div class='wizard-item-label'>Tiempo Estimado</div>
                <div class='wizard-item-value'>" . htmlspecialchars(trim($matches[1])) . "</div>
            </div>";
        }
        
        if (preg_match('/Presupuesto:\s*(.+?)$/m', $notas, $matches) || preg_match('/Presupuesto aproximado:\s*(.+?)$/mi', $notas, $matches)) {
            $html .= "<div class='wizard-item'>
                <div class='wizard-item-label'>Presupuesto Aproximado</div>
                <div class='wizard-item-value'>" . htmlspecialchars(trim($matches[1])) . "</div>
            </div>";
        }
        
        if (preg_match('/Prioridad:\s*(.+?)$/m', $notas, $matches)) {
            $html .= "<div class='wizard-item'>
                <div class='wizard-item-label'>Prioridad</div>
                <div class='wizard-item-value'>" . ucfirst(htmlspecialchars(trim($matches[1]))) . "</div>
            </div>";
        }
        
        if (preg_match('/Fecha inicio:\s*(.+?)$/m', $notas, $matches)) {
            $html .= "<div class='wizard-item'>
                <div class='wizard-item-label'>Fecha de Inicio</div>
                <div class='wizard-item-value'>" . htmlspecialchars(trim($matches[1])) . "</div>
            </div>";
        }
        
        if (preg_match('/Plazo de entrega:\s*(.+?)$/m', $notas, $matches) || preg_match('/Plazo entrega:\s*(.+?)$/m', $notas, $matches)) {
            $html .= "<div class='wizard-item'>
                <div class='wizard-item-label'>Plazo de Entrega</div>
                <div class='wizard-item-value'>" . ucfirst(htmlspecialchars(trim($matches[1]))) . "</div>
            </div>";
        }

        if (preg_match('/Metodología:\s*(.+?)$/m', $notas, $matches)) {
            $html .= "<div class='wizard-item'>
                <div class='wizard-item-label'>Metodología</div>
                <div class='wizard-item-value'>" . htmlspecialchars(trim($matches[1])) . "</div>
            </div>";
        }
        
        if (preg_match('/Descripción:\s*(.+?)$/m', $notas, $matches)) {
            $html .= "<div class='wizard-item wizard-full'>
                <div class='wizard-item-label'>Descripción</div>
                <div class='wizard-item-value'>" . htmlspecialchars(trim($matches[1])) . "</div>
            </div>";
        }
        
        if (preg_match('/Tecnologías:\s*(\[.+?\])/s', $notas, $matches)) {
            $tecnologias = json_decode($matches[1], true);
            if ($tecnologias) {
                $html .= "<div class='wizard-item wizard-full'>
                    <div class='wizard-item-label'>Tecnologías Seleccionadas</div>
                    <div class='tech-tags'>";
                foreach ($tecnologias as $tech) {
                    $html .= "<span class='tech-tag'>" . htmlspecialchars($tech) . "</span>";
                }
                $html .= "</div></div>";
            }
        }
        
        if (preg_match('/Notas adicionales:\s*(.+)/s', $notas, $matches)) {
            $notasAdicionales = trim($matches[1]);
            if ($notasAdicionales) {
                $html .= "<div class='wizard-item wizard-full'>
                    <div class='wizard-item-label'>Notas Adicionales</div>
                    <div class='wizard-item-value'>" . nl2br(htmlspecialchars($notasAdicionales)) . "</div>
                </div>";
            }
        }
        
        $html .= "</div>";
    } else {
        // Presupuesto clásico - mostrar tabla de servicios
        $html .= "
        <div class='section-title'>Servicios Contratados</div>
        
        <table>
            <thead>
                <tr>
                    <th>Servicio</th>
                    <th style='text-align: center;'>Cantidad</th>
                    <th style='text-align: right;'>Precio Unitario</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>";
        
        foreach ($detalles as $detalle) {
            $subtotal = $detalle['cantidad'] * $detalle['preci'];
            $html .= "<tr>
                <td><strong>" . htmlspecialchars($detalle['servicio_nombre']) . "</strong>";
            
            if (!empty($detalle['comentario'])) {
                $html .= "<br><small style='color: #666;'>" . htmlspecialchars($detalle['comentario']) . "</small>";
            }
            
            $html .= "</td>
                <td style='text-align: center;'>{$detalle['cantidad']}</td>
                <td style='text-align: right;'>" . number_format($detalle['preci'], 2, ',', '.') . " €</td>
                <td style='text-align: right;'><strong>" . number_format($subtotal, 2, ',', '.') . " €</strong></td>
            </tr>";
        }
        
        $html .= "</tbody>
        </table>";
        
        // Mostrar notas si existen y no es del wizard
        if (!empty($datos['notas'])) {
            $html .= "<div class='notes-box'>
                <div class='notes-title'>Notas</div>
                <div class='notes-content'>" . nl2br(htmlspecialchars($datos['notas'])) . "</div>
            </div>";
        }
    }
    
    // Totales
    $html .= "
        <div class='totals-section'>
            <div class='totals-box'>
                <div class='total-row subtotal'>
                    <span>Subtotal (sin IVA)</span>
                    <span>{$total} €</span>
                </div>
                <div class='total-row iva'>
                    <span>IVA (21%)</span>
                    <span>{$iva} €</span>
                </div>
                <div class='total-row final'>
                    <span>TOTAL</span>
                    <span>{$totalIVA} €</span>
                </div>
            </div>
        </div>
        
        <div class='footer'>
            <div class='footer-info'>
                <strong>Logisteia</strong> - Gestión de Proyectos y Presupuestos
            </div>
            <div>
                Documento generado el " . date('d/m/Y') . " a las " . date('H:i') . "
            </div>
            <div style='margin-top: 10px; font-size: 10px;'>
                Este presupuesto tiene una validez de {$datos['validez_dias']} días desde su fecha de emisión.
            </div>
        </div>
    </div>
</body>
</html>";
    
    return $html;
}
