<!-- app/views/facturacion/listado.php -->
<div class="container-fluid">
    <h4>Facturas Emitidas</h4>
    <form class="row g-2 mb-3">
        <div class="col-auto"><input type="date" name="fd" value="<?= htmlspecialchars($filtros['fecha_desde'] ?? '') ?>" class="form-control" placeholder="Desde"></div>
        <div class="col-auto"><input type="date" name="fh" value="<?= htmlspecialchars($filtros['fecha_hasta'] ?? '') ?>" class="form-control" placeholder="Hasta"></div>
        <div class="col-auto"><input type="text" name="cl" value="<?= htmlspecialchars($filtros['cliente'] ?? '') ?>" class="form-control" placeholder="Cliente"></div>
        <div class="col-auto">
            <select name="st" class="form-select">
                <option value="">Estado</option>
                <?php foreach (['PENDIENTE', 'EMITIDO', 'RECHAZADO', 'ANULADO'] as $st): ?>
                    <option <?= $filtros['estado'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-auto"><button class="btn btn-primary">Filtrar</button></div>
        <div class="col-auto"><a href="/ventas/facturacion/nueva" class="btn btn-success">Nueva Factura</a></div>
    </form>

    <div class="table-responsive">
        <table class="table table-sm table-striped">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>PV</th>
                    <th>Nro</th>
                    <th>Cliente</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($registros as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['fecha']) ?></td>
                        <td><?= htmlspecialchars($r['tipo']) ?></td>
                        <td><?= htmlspecialchars($r['punto_venta']) ?></td>
                        <td><?= htmlspecialchars($r['numero']) ?></td>
                        <td><?= htmlspecialchars($r['razon_social'] ?? '') ?></td>
                        <td>$ <?= number_format((float)$r['importe_total'], 2, ',', '.') ?></td>
                        <td><?= htmlspecialchars($r['estado']) ?></td>
                        <td><a href="/ventas/facturacion/detalle?id=<?= $r['id_comprobante'] ?>" class="btn btn-link btn-sm">Ver</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>