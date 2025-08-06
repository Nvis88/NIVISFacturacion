<?php
$usuario = $_SESSION['usuario'] ?? null;

ob_start(); // Inicia el buffer
?>

<h3 class="mb-1">
    Bienvenido, <?= htmlspecialchars($usuario['Nombres'] . ' ' . $usuario['Apellidos']) ?>
</h3>
<p class="text-muted mb-4">CUIT: <?= htmlspecialchars($usuario['CUIT']) ?></p>

<div class="row">
    <div class="col-md-4 mb-3">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex flex-column justify-content-between">
                <div>
                    <h5 class="card-title">Consulta</h5>
                    <p class="card-text">Acceder a la búsqueda de contribuyentes.</p>
                </div>
                <a href="#" class="btn btn-outline-primary mt-3">Ir</a>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-3">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex flex-column justify-content-between">
                <div>
                    <h5 class="card-title">Reportes</h5>
                    <p class="card-text">Visualizar reportes disponibles.</p>
                </div>
                <a href="#" class="btn btn-outline-primary mt-3">Ir</a>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-3">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex flex-column justify-content-between">
                <div>
                    <h5 class="card-title">Configuración</h5>
                    <p class="card-text">Ajustes generales del sistema.</p>
                </div>
                <a href="#" class="btn btn-outline-primary mt-3">Ir</a>
            </div>
        </div>
    </div>
</div>

<?php
$contenido = ob_get_clean(); // ⚠️ NO INCLUIR layout_base.php manualmente
