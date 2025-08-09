<?php
// app/views/configuracion/index.php
// Variables recibidas: $flash, $claveFiscalSet, $fechaCreacion, $fechaUpdate,
// $tieneCert, $cert, $sysName, $sysExists, $sysStats, $tenName, $tenExists, $tenStats

ob_start();
?>

<h1 class="mb-4">Configuración</h1>

<?php if (!empty($flash)): ?>
    <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>" role="alert">
        <?= htmlspecialchars($flash['msg']) ?>
    </div>
<?php endif; ?>

<!-- Fila: Clave Fiscal -->
<div class="row justify-content-center mb-4">
    <div class="col-12 col-md-10 col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Clave Fiscal</h5>

                <?php if (!empty($claveFiscalSet)): ?>
                    <p class="text-success mb-1">✓ Clave Fiscal configurada</p>
                    <p class="small text-muted mb-3">
                        Cargada: <?= htmlspecialchars($fechaCreacion ?? '') ?><br>
                        <?php if (!empty($fechaUpdate)): ?>
                            Actualizada: <?= htmlspecialchars($fechaUpdate) ?>
                        <?php endif; ?>
                    </p>
                <?php else: ?>
                    <p class="text-danger mb-3">✗ Clave Fiscal no configurada</p>
                <?php endif; ?>

                <!-- Ajustá la action si tu ruta es /configuracion/clave-fiscal -->
                <form method="post" action="/configuracion">
                    <div class="form-group">
                        <label for="claveFiscal">Nueva Clave Fiscal</label>
                        <input
                            type="password"
                            id="claveFiscal"
                            name="clave_fiscal"
                            class="form-control"
                            placeholder="Ingresa tu Clave Fiscal"
                            required>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">Actualizar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Fila: Certificado Digital -->
<div class="row justify-content-center mb-4">
    <div class="col-12 col-md-10 col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Certificado Digital</h5>

                <?php if (!empty($tieneCert)): ?>
                    <p class="text-success mb-1">✓ Certificado activo</p>
                    <p class="small text-muted mb-3">
                        CN: <?= htmlspecialchars($cert['subject_cn'] ?? '') ?><br>
                        Emisor: <?= htmlspecialchars($cert['issuer_cn'] ?? '') ?><br>
                        Huella (SHA-256): <?= htmlspecialchars($cert['fingerprint_sha256'] ?? '') ?><br>
                        Válido: <?= htmlspecialchars($cert['not_before'] ?? '') ?> → <?= htmlspecialchars($cert['not_after'] ?? '') ?><br>
                        Ámbito: <?= htmlspecialchars($cert['ambito'] ?? 'prod') ?><br>
                        Última actualización: <?= htmlspecialchars($cert['updated_at'] ?? $cert['created_at'] ?? '') ?>
                    </p>
                <?php else: ?>
                    <p class="text-danger mb-3">✗ No hay certificado activo</p>
                <?php endif; ?>

                <form method="post" action="/configuracion/certificado" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="certificado">Certificado (.cer / .crt / .pem)</label>
                            <input class="form-control" type="file" id="certificado" name="certificado" accept=".cer,.crt,.pem" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="privada">Clave privada (.key / .pem)</label>
                            <input class="form-control" type="file" id="privada" name="privada" accept=".key,.pem" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="passphrase">Passphrase (si corresponde)</label>
                            <input class="form-control" type="password" id="passphrase" name="passphrase" placeholder="Opcional">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="ambito">Ámbito</label>
                            <select class="form-select" id="ambito" name="ambito">
                                <option value="prod">Producción</option>
                                <option value="homo">Homologación</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">Subir / Actualizar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Fila: Bases de Datos -->
<div class="row">
    <!-- Col: BD del Sistema -->
    <div class="col-md-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Base de Datos del Sistema</h6>
            </div>
            <div class="card-body">
                <p><strong>Nombre:</strong> <?= htmlspecialchars($sysName ?? '') ?></p>

                <?php if (!empty($sysExists)): ?>
                    <span class="badge badge-success mb-3">Existe</span>
                    <ul class="list-unstyled">
                        <li>
                            <strong>Tamaño:</strong>
                            <?= htmlspecialchars($sysStats['human_size'] ?? '-') ?>
                            (<?= number_format((int)($sysStats['bytes'] ?? 0), 0, ',', '.') ?> bytes)
                        </li>
                        <li><strong>Fecha de creación (aprox.):</strong>
                            <?php if (!empty($sysStats['create_time'])): ?>
                                <?= date('Y-m-d H:i:s', strtotime($sysStats['create_time'])) ?>
                            <?php else: ?>
                                <em>No disponible (sin tablas o no reportado)</em>
                            <?php endif; ?>
                        </li>
                        <li><strong>Tablas:</strong> <?= (int)($sysStats['tables'] ?? 0) ?></li>
                    </ul>
                <?php else: ?>
                    <span class="badge badge-danger mb-3">No existe</span>
                    <form action="/configuracion/crear-db" method="post"
                        onsubmit="return confirm('¿Crear base de datos <?= htmlspecialchars($sysName ?? '') ?>?');">
                        <input type="hidden" name="tipo" value="sistema">
                        <button class="btn btn-primary">Crear base de datos</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Col: BD del Usuario -->
    <div class="col-md-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Base de Datos del Usuario</h6>
            </div>
            <div class="card-body">
                <?php if (empty($tenName)): ?>
                    <p><em>No se puede determinar el nombre de la base de datos del usuario.
                            Cargá un CUIT en tu perfil o ajustá tenantDbName().</em></p>

                <?php elseif (!empty($tenExists)): ?>
                    <p><strong>Nombre:</strong> <?= htmlspecialchars($tenName) ?></p>
                    <span class="badge badge-success mb-3">Existe</span>
                    <ul class="list-unstyled">
                        <li>
                            <strong>Tamaño:</strong>
                            <?= htmlspecialchars($tenStats['human_size'] ?? '-') ?>
                            (<?= number_format((int)($tenStats['bytes'] ?? 0), 0, ',', '.') ?> bytes)
                        </li>
                        <li><strong>Fecha de creación (aprox.):</strong>
                            <?php if (!empty($tenStats['create_time'])): ?>
                                <?= date('Y-m-d H:i:s', strtotime($tenStats['create_time'])) ?>
                            <?php else: ?>
                                <em>No disponible (sin tablas o no reportado)</em>
                            <?php endif; ?>
                        </li>
                        <li><strong>Tablas:</strong> <?= (int)($tenStats['tables'] ?? 0) ?></li>
                    </ul>

                <?php else: ?>
                    <p><strong>Nombre:</strong> <?= htmlspecialchars($tenName) ?></p>
                    <span class="badge badge-danger mb-3">No existe</span>
                    <form action="/configuracion/crear-db" method="post"
                        onsubmit="return confirm('¿Crear base de datos <?= htmlspecialchars($tenName) ?>?');">
                        <input type="hidden" name="tipo" value="usuario">
                        <button class="btn btn-primary">Crear base de datos</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$contenido = ob_get_clean();
require __DIR__ . '/../layout/layout_base.php';
