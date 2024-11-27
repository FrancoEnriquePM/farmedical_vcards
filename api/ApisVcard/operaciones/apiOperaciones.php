<?php
// Configuración de cabeceras CORS
header("Access-Control-Allow-Origin: *"); // Permitir solicitudes desde cualquier origen
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); // Métodos soportados
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Headers permitidos

require_once '../../../controllers/VcardControlador/VcardControlador.php';

$controller = new VcardControlador();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        $controller->crearVcard();
        break;
    case 'GET':
        $controller->listarTodasLasVcards();
        break;
    case 'PUT':
        $controller->actualizarVcard();
        break;
    case 'DELETE':
        $controller->eliminarVcard();
        break;
    default:
        echo json_encode(["message" => "Método no soportado."]);
        break;
}
