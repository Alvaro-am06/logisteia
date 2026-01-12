<?php
/**
 * API endpoint para exportar un presupuesto a PDF.
 * 
 * Recibe GET con parámetro numero (numero_presupuesto)
 * Genera un PDF y lo devuelve para descarga.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

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

    $database = new Conexion();
    $db = $database->obtener();
    $presupuesto = new Presupuesto($db);

    // Obtener datos del presupuesto
    $datos = $presupuesto->obtenerPorNumero($numero);
    
    if (!$datos) {
        http_response_code(404);
        echo json_encode(['error' => 'Presupuesto no encontrado']);
        exit();
    }

    // Obtener detalles
    $detalles = $presupuesto->obtenerDetalles($numero);

    // Generar PDF básico con HTML
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="presupuesto-' . $numero . '.pdf"');

    // Usar TCPDF o similar si está disponible, sino HTML simple
    // Por ahora, generamos un HTML que se puede convertir a PDF
    $html = generarHTMLPresupuesto($datos, $detalles);
    
    // Si no hay librería PDF, devolver HTML para que el navegador lo imprima
    header('Content-Type: text/html');
    echo $html;
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error interno: ' . $e->getMessage()]);
}

function generarHTMLPresupuesto($datos, $detalles) {
    $total = number_format($datos['total'], 2, ',', '.');
    $totalIVA = number_format($datos['total'] * 1.21, 2, ',', '.');
    $fecha = date('d/m/Y', strtotime($datos['fecha_creacion']));
    
    $html = "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Presupuesto {$datos['numero_presupuesto']}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #102a41; }
        .info { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #102a41; color: white; }
        .total { text-align: right; font-weight: bold; margin-top: 20px; }
        .footer { margin-top: 40px; text-align: center; color: #666; }
    </style>
</head>
<body>
    <div class='header'>
        <h1>LOGISTEIA</h1>
        <p>Planifica con precisión. Ejecuta con control.</p>
    </div>
    
    <div class='info'>
        <p><strong>Presupuesto N°:</strong> {$datos['numero_presupuesto']}</p>
        <p><strong>Fecha:</strong> {$fecha}</p>
        <p><strong>Estado:</strong> " . ucfirst($datos['estado']) . "</p>
        <p><strong>Validez:</strong> {$datos['validez_dias']} días</p>
    </div>
    
    <table>
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
            <td>{$detalle['servicio_nombre']}</td>
            <td>{$detalle['cantidad']}</td>
            <td>" . number_format($detalle['preci'], 2, ',', '.') . " €</td>
            <td>" . number_format($subtotal, 2, ',', '.') . " €</td>
        </tr>";
    }
    
    $html .= "</tbody>
    </table>
    
    <div class='total'>
        <p>Subtotal: {$total} €</p>
        <p>IVA (21%): " . number_format($datos['total'] * 0.21, 2, ',', '.') . " €</p>
        <p style='font-size: 18px;'>TOTAL: {$totalIVA} €</p>
    </div>";
    
    if (!empty($datos['notas'])) {
        $html .= "<div style='margin-top: 30px;'>
            <p><strong>Notas:</strong></p>
            <p>{$datos['notas']}</p>
        </div>";
    }
    
    $html .= "
    <div class='footer'>
        <p>Documento generado el " . date('d/m/Y H:i') . "</p>
    </div>
    
    <script>
        window.onload = function() { window.print(); }
    </script>
</body>
</html>";
    
    return $html;
}
