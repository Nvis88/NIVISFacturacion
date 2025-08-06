<?php

require_once __DIR__ . '/../helpers/view.php';

class ConfiguracionController
{
    public function index()
    {
        if (!isset($_SESSION['usuario'])) {
            header('Location: /');
            exit;
        }

        $usuario = $_SESSION['usuario'];
        render('configuracion/index', ['usuario' => $usuario]);
    }
}
