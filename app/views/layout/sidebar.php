<?php
$current = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$isVentas      = str_starts_with($current, '/ventas');
$isFacturacion = str_starts_with($current, '/ventas/facturacion');
$active = fn(string $path) => ($current === $path) ? 'active' : '';
?>

<div class="d-flex flex-column flex-shrink-0 p-3 bg-light" style="width: 240px; min-height: 100vh;">
    <a href="/dashboard" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-dark text-decoration-none">
        <span class="fs-5 fw-bold">Menú</span>
    </a>
    <hr>

    <ul class="nav nav-pills flex-column mb-auto">

        <!-- Configuración -->
        <li class="nav-item">
            <a href="/configuracion" class="nav-link text-dark <?= $active('/configuracion') ?>">⚙️ Configuración</a>
        </li>

        <!-- Ventas -->
        <li class="nav-item">
            <a class="nav-link d-flex justify-content-between align-items-center text-dark <?= $isVentas ? 'active' : '' ?>"
                data-bs-toggle="collapse" href="#menuVentas" role="button" aria-expanded="<?= $isVentas ? 'true' : 'false' ?>"
                aria-controls="menuVentas">
                <span>💰 Ventas</span>
                <span class="ms-2 small"><?= $isVentas ? '▾' : '▸' ?></span>
            </a>

            <div class="collapse <?= $isVentas ? 'show' : '' ?>" id="menuVentas">
                <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small ps-3">
                    <!-- Facturación -->
                    <li class="mt-2 text-muted small">Facturación</li>
                    <li><a href="/ventas/facturacion/listado" class="nav-link text-dark ps-3 <?= $active('/ventas/facturacion/listado') ?>">• Facturas Emitidas</a></li>
                    <li><a href="/ventas/facturacion/nueva" class="nav-link text-dark ps-3 <?= $active('/ventas/facturacion/nueva') ?>">• Nueva Factura</a></li>
                    <!-- “Anular” va a POST, dejo link a una vista intermedia opcional -->
                    <li><a href="/ventas/facturacion/detalle" class="nav-link text-dark ps-3 <?= $active('/ventas/facturacion/detalle') ?>">• Consultar / Detalle</a></li>
                    <li><a href="/ventas/facturacion/anular" class="nav-link text-dark ps-3 <?= $active('/ventas/facturacion/anular') ?>">• Anular (NC)</a></li>

                    <!-- Futuro (deshabilitado visualmente) -->
                    <li class="mt-3 text-muted small">Próximamente</li>
                    <li><span class="nav-link text-secondary ps-3 disabled">• Ventas por Facturar</span></li>
                    <li><span class="nav-link text-secondary ps-3 disabled">• Libro IVA Ventas</span></li>
                </ul>
            </div>
        </li>

        <!-- Compras -->
        <li>
            <a href="#" class="nav-link text-dark">🧾 Compras</a>
        </li>

        <!-- Cuentas Corrientes -->
        <li>
            <a href="#" class="nav-link text-dark">💼 Cuentas Corrientes</a>
        </li>

        <!-- Contactos -->
        <li>
            <a href="#" class="nav-link text-dark">📇 Contactos</a>
        </li>
    </ul>
</div>