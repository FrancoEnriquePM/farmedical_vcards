<?php
// Configuración de cabeceras CORS
header("Access-Control-Allow-Origin: *"); // Permitir solicitudes desde cualquier origen
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); // Métodos soportados
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Headers permitidos

require_once '../../../controllers/UsuarioControlador/UsuarioControlador.php';

$controller = new UsuarioControlador();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        $controller->crearUsuario();
        break;
    case 'GET':
        $controller->listarTodo();
        break;
    case 'PUT':
        $controller->actualizarUsuario();
        break;
    case 'DELETE':
        $controller->eliminarUsuario();
        break;
    default:
        echo json_encode(["message" => "Método no soportado."]);
        break;
}
