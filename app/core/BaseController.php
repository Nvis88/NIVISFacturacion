<?php

declare(strict_types=1);

class BaseController
{
    /**
     * Carga una vista de app/views/$view.php
     * y expone $data como variables locales.
     */
    protected function view(string $view, array $data = []): void
    {
        $file = __DIR__ . '/../views/' . $view . '.php';
        if (!is_file($file)) {
            http_response_code(500);
            echo "Vista no encontrada: {$view}";
            return;
        }
        extract($data, EXTR_SKIP);
        require $file;
    }

    /**
     * Carga e instancia un modelo de app/models/$name.php.
     * Si el modelo tiene namespace App\Models, lo respeta.
     */
    protected function model(string $name)
    {
        $file = __DIR__ . '/../models/' . $name . '.php';
        if (!is_file($file)) {
            throw new RuntimeException("Modelo no encontrado: {$name}");
        }
        require_once $file;

        $fqcn = '\\App\\Models\\' . $name;   // con namespace
        if (class_exists($fqcn)) {
            return new $fqcn();
        }
        if (class_exists($name)) {           // sin namespace
            return new $name();
        }
        throw new RuntimeException("Clase de modelo inválida: {$name}");
    }
}
