<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\UsuarioModel;

class LoginController
{
    public function index()
    {
        // Vista sin layout (formulario login)
        require_once __DIR__ . '/../views/login.php';
    }

    public function auth()
    {
        $cuit  = $_POST['cuit'] ?? '';
        $clave = $_POST['clave'] ?? '';

        $modelo  = new UsuarioModel();
        $usuario = $modelo->verificarCredenciales($cuit, $clave);

        if ($usuario) {
            $_SESSION['usuario'] = $usuario;
            header('Location: /dashboard');
        } else {
            $_SESSION['error'] = 'CUIT o clave incorrectos';
            header('Location: /');
        }

        exit;
    }

    public function dashboard()
    {
        if (!isset($_SESSION['usuario'])) {
            header('Location: /');
            exit;
        }

        render('dashboard', ['usuario' => $_SESSION['usuario']]);
    }


    public function misDatos()
    {
        $this->verificarSesion();
        render('mis_datos', ['usuario' => $_SESSION['usuario']]);
    }


    public function logout()
    {
        session_destroy();
        header('Location: /');
        exit;
    }

    private function verificarSesion()
    {
        if (!isset($_SESSION['usuario'])) {
            header('Location: /');
            exit;
        }
    }
}
