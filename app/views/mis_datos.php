<?php
// app/views/mis_datos.php

// "render()" ya extrajo y puso disponible la variable $usuario
// Si prefieres, podrías hacer: $usuario = $_SESSION['usuario'];

// ob_start();  // Inicia el buffer
?>

<h3 class="mb-4">Mis Datos</h3>

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label">CUIT</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($usuario['CUIT']) ?>" readonly>
        </div>
        <div class="mb-3">
            <label class="form-label">Apellidos</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($usuario['Apellidos']) ?>" readonly>
        </div>
        <div class="mb-3">
            <label class="form-label">Nombres</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($usuario['Nombres']) ?>" readonly>
        </div>
        <div class="mb-3">
            <label class="form-label">Razón Social</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($usuario['RazonSocial']) ?>" readonly>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" value="<?= htmlspecialchars($usuario['Email']) ?>" readonly>
        </div>
        <div class="mb-3">
            <label class="form-label">Régimen</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($usuario['Regimen']) ?>" readonly>
        </div>
    </div>
</div>

<?php
// Capturamos todo lo anterior en $contenido para pasarlo al layout
// $contenido = ob_get_clean();
