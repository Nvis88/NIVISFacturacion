<?php

session_start();

// Cargar controlador de login
require_once __DIR__ . '/../app/controllers/LoginController.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$controller = new LoginController();

switch ($uri) {
    case '/':
        $controller->index();
        break;

    case '/login':
        if ($method === 'POST') {
            $controller->auth();
        } else {
            header('Location: /');
        }
        break;

    case '/dashboard':
        if (isset($_SESSION['usuario'])) {
            $controller->dashboard();
        } else {
            header('Location: /');
        }
        break;
    case '/configuracion':
        require_once __DIR__ . '/../app/controllers/ConfiguracionController.php';
        $ctrl = new ConfiguracionController();
        $ctrl->index();
        break;

    case '/logout':
        $controller->logout();
        break;
    case '/mis-datos':
        if (isset($_SESSION['usuario'])) {
            require_once __DIR__ . '/../app/views/mis_datos.php';
        } else {
            header('Location: /');
        }
        break;

    default:
        http_response_code(404);
        echo "Página no encontrada.";
}
