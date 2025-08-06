<?php
ob_start();
?>

<h3 class="mb-4">Configuración</h3>

<p>Este es el panel de configuración. Acá podrás agregar parámetros del sistema, editar información institucional, etc.</p>

<?php
$contenido = ob_get_clean();
