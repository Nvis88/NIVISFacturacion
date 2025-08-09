<?php
$usuario = $_SESSION['usuario'] ?? null;
$iniciales = $usuario ? obtenerIniciales($usuario['Nombres'], $usuario['Apellidos']) : '??';
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="/dashboard">NVFacturando</a>

        <div class="d-flex align-items-center ms-auto">
            <?php if ($usuario): ?>
                <div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="rounded-circle bg-primary text-white d-flex justify-content-center align-items-center me-2" style="width: 36px; height: 36px;">
                            <?= $iniciales ?>
                        </div>
                        <?= htmlspecialchars($usuario['Nombres'] . ' ' . $usuario['Apellidos']) ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="/mis-datos">Mis Datos</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item text-danger" href="/logout">Cerrar sesión</a></li>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>