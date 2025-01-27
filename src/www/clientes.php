<?php
require_once '../controladores/ControladorCliente.php';

$controller = new ControladorDeCliente();

$accion = $_GET['accion'] ?? 'listar';

switch ($accion) {
    case 'listar':
        $controller->listar();
        break;
    
    case 'nuevo':
        $controller->mostrarFormulario();
        break;
    
    case 'editar':
        $id = $_GET['id'] ?? null;
        if ($id) {
            $controller->mostrarFormulario($id);
        } else {
            header('Location: clientes.php');
        }
        break;
    
    case 'eliminar':
        $id = $_GET['id'] ?? null;
        if ($id) {
            $controller->eliminar($id);
        } else {
            header('Location: clientes.php');
        }
        break;
    
    case 'buscar':
        $controller->buscar();
        break;
    
    default:
        $controller->listar();
        break;
}
?>
