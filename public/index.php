<?php

declare(strict_types=1);

// 1) Autoload de Composer y dotenv
require __DIR__ . '/../vendor/autoload.php';

// public/index.php (o donde hagas el bootstrap)
require_once __DIR__ . '/../app/models/Database.php';
require_once __DIR__ . '/../app/core/BaseController.php';
require_once __DIR__ . '/../app/models/ComprobanteModel.php';


$rootDir = realpath(__DIR__ . '/../');
if ($rootDir === false) {
    die('Error al resolver la ruta raíz del proyecto');
}

Dotenv\Dotenv::createImmutable($rootDir)->load();

// 2) Arrancar sesión
session_start();

// 3) Importar controladores (PSR-4)
use App\Controllers\LoginController;
use App\Controllers\ConfiguracionController;

// 4) Instanciar controlador de login
$loginController = new LoginController();

// 5) Despachar rutas
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

switch ($uri) {
    case '/':
        if (isset($_SESSION['usuario'])) {
            header('Location: /dashboard');
            exit;
        }
        $loginController->index();
        break;

    case '/login':
        if ($method === 'POST') {
            $loginController->auth();
        } else {
            header('Location: /');
        }
        break;

    case '/dashboard':
        if (isset($_SESSION['usuario'])) {
            $loginController->dashboard();
        } else {
            header('Location: /');
        }
        break;

    case '/configuracion':
        $configController = new ConfiguracionController();
        if ($method === 'POST') {
            $configController->updateClaveFiscal();
        } else {
            $configController->index();
        }
        break;

    case '/configuracion/certificado':
        $configController = new ConfiguracionController();
        if ($method === 'POST') {
            $configController->updateCertificado();
        } else {
            header('Location: /configuracion');
        }
        break;

    case '/configuracion/crear-db':
        $ctrl = new ConfiguracionController();
        $ctrl->crearBaseDatos();
        break;

    case '/logout':
        $loginController->logout();
        break;

    case '/mis-datos':
        $loginController->misDatos();
        break;

    case '/ventas/facturacion':
        $ctrl = new ConfiguracionController(); // si tenés otro base, usalo
        require_once __DIR__ . '/../app/controllers/FacturacionController.php';
        $c = new FacturacionController();
        $c->index(); // redirige a listado
        break;

    case '/ventas/facturacion/listado':
        require_once __DIR__ . '/../app/controllers/FacturacionController.php';
        (new FacturacionController())->listado();
        break;

    case '/ventas/facturacion/nueva':
        require_once __DIR__ . '/../app/controllers/FacturacionController.php';
        $c = new FacturacionController();
        ($_SERVER['REQUEST_METHOD'] === 'POST') ? $c->crear() : $c->formNueva();
        break;

    case '/ventas/facturacion/detalle':
        require_once __DIR__ . '/../app/controllers/FacturacionController.php';
        (new FacturacionController())->detalle(); // ?id=...
        break;

    case '/ventas/facturacion/anular':
        require_once __DIR__ . '/../app/controllers/FacturacionController.php';
        (new FacturacionController())->anular(); // POST: id y motivo -> emite NC
        break;

    default:
        http_response_code(404);
        echo 'Página no encontrada.';
        break;
}
