<?php

if (!function_exists('obtenerIniciales')) {
    function obtenerIniciales(string $nombre, string $apellido): string
    {
        $ini1 = strtoupper(substr(trim($nombre), 0, 1));
        $ini2 = strtoupper(substr(trim($apellido), 0, 1));
        return $ini1 . $ini2;
    }
}
