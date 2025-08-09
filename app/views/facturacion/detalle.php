<!-- app/views/facturacion/detalle.php -->
<div class="container">
    <h4>Comprobante #<?= htmlspecialchars($cabecera['id_comprobante']) ?> (<?= $cabecera['tipo'] ?>)</h4>
    <p><strong>Fecha:</strong> <?= $cabecera['fecha'] ?> |
        <strong>PV/Nro:</strong> <?= $cabecera['punto_venta'] ?> / <?= htmlspecialchars($cabecera['numero'] ?? '—') ?> |
        <strong>Cliente:</strong> <?= htmlspecialchars($cabecera['razon_social'] ?? '') ?> (<?= htmlspecialchars($cabecera['cuit'] ?? '') ?>)
    </p>
    <p><strong>Estado:</strong> <?= $cabecera['estado'] ?> |
        <strong>CAE:</strong> <?= htmlspecialchars($cabecera['cae'] ?? '—') ?> (Vto: <?= htmlspecialchars($cabecera['cae_vto'] ?? '—') ?>)
    </p>

    <div class="table-responsive">
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Descripción</th>
                    <th>Cant</th>
                    <th>P.Unit</th>
                    <th>IVA%</th>
                    <th>Neto</th>
                    <th>IVA</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $i => $it): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($it['descripcion']) ?></td>
                        <td><?= number_format($it['cantidad'], 2, ',', '.') ?></td>
                        <td><?= number_format($it['precio_unit'], 2, ',', '.') ?></td>
                        <td><?= number_format($it['alicuota_iva'], 2, ',', '.') ?></td>
                        <td><?= number_format($it['importe_neto'], 2, ',', '.') ?></td>
                        <td><?= number_format($it['importe_iva'], 2, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="text-end">
        <h5>Total: $ <?= number_format((float)$cabecera['importe_total'], 2, ',', '.') ?></h5>
    </div>

    <form method="post" action="/ventas/facturacion/anular" class="mt-3">
        <input type="hidden" name="id" value="<?= $cabecera['id_comprobante'] ?>">
        <div class="input-group">
            <input name="motivo" class="form-control" placeholder="Motivo de la NC (anulación)">
            <button class="btn btn-outline-danger" <?= $cabecera['estado'] === 'ANULADO' ? 'disabled' : '' ?>>Emitir Nota de Crédito</button>
        </div>
    </form>
</div>