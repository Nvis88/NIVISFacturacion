<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\UsuarioModel;

final class LoginController
{
    public function index(): void
    {
        // Vista simple de login (ajustá la ruta si tu archivo está en otra carpeta)
        require __DIR__ . '/../views/login.php';
    }

    public function auth(): void
    {
        $cuit  = trim($_POST['cuit']  ?? '');
        $clave = trim($_POST['clave'] ?? '');

        $modelo  = new UsuarioModel(); // PSR-4: App\Models\UsuarioModel
        $usuario = $modelo->verificarCredenciales($cuit, $clave);

        if ($usuario) {
            $_SESSION['usuario'] = $usuario;
            header('Location: /dashboard');
            exit;
        }

        $_SESSION['error'] = 'CUIT o clave incorrectos';
        header('Location: /');
        exit;
    }

    public function dashboard(): void
    {
        $this->verificarSesion();
        // Vista simple de dashboard
        require __DIR__ . '/../views/dashboard.php';
    }

    public function misDatos(): void
    {
        $this->verificarSesion();
        require __DIR__ . '/../views/mis_datos.php';
    }

    public function logout(): void
    {
        session_destroy();
        header('Location: /');
        exit;
    }

    private function verificarSesion(): void
    {
        if (!isset($_SESSION['usuario'])) {
            header('Location: /');
            exit;
        }
    }
}
