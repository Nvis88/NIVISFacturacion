<?php

function render(string $vista, array $variables = [])
{
    $rutaVista = __DIR__ . '/../views/' . $vista . '.php';

    if (!file_exists($rutaVista)) {
        die("La vista '$vista' no existe.");
    }

    // Hacer disponibles las variables dentro de la vista
    extract($variables);

    // Capturar el contenido de la vista
    ob_start();
    require $rutaVista;
    $contenido = ob_get_clean();

    // Incluir el layout principal con el contenido
    require __DIR__ . '/../views/layout/layout_base.php';
}
