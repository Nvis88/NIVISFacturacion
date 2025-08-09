<!-- app/views/facturacion/form_nueva.php -->
<div class="container">
    <h4>Nueva Factura</h4>
    <form method="post" action="/ventas/facturacion/nueva" id="frmFactura">
        <div class="row mb-2">
            <div class="col-md-3">
                <label class="form-label">Fecha</label>
                <input type="date" name="fecha" class="form-control" value="<?= $hoy ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Tipo</label>
                <select name="tipo" class="form-select">
                    <option value="FA">Factura A/B/C (ajusta en backend)</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Punto de Venta</label>
                <input type="number" name="punto_venta" class="form-control" value="1">
            </div>
            <div class="col-md-3">
                <label class="form-label">Cliente (ID)</label>
                <input type="number" name="cliente_id" class="form-control">
            </div>
        </div>

        <hr>
        <h6>Ítems</h6>
        <table class="table table-sm" id="tblItems">
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th>Cant</th>
                    <th>P.Unit</th>
                    <th>IVA%</th>
                    <th>Neto</th>
                    <th>IVA</th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
        <button type="button" class="btn btn-outline-secondary" id="btnAdd">Agregar ítem</button>

        <div class="text-end mt-3">
            <div><strong>Neto:</strong> <span id="vNeto">0.00</span></div>
            <div><strong>IVA:</strong> <span id="vIva">0.00</span></div>
            <div><strong>Total:</strong> <span id="vTotal">0.00</span></div>
        </div>

        <input type="hidden" name="items" id="itemsJson">
        <button class="btn btn-primary mt-3">Guardar / Emitir</button>
    </form>
</div>

<script>
    const tbody = document.querySelector('#tblItems tbody');
    document.getElementById('btnAdd').addEventListener('click', () => addRow());

    function addRow() {
        const tr = document.createElement('tr');
        tr.innerHTML = `
    <td><input class="form-control form-control-sm desc"></td>
    <td><input type="number" step="0.01" class="form-control form-control-sm cant" value="1"></td>
    <td><input type="number" step="0.01" class="form-control form-control-sm pu" value="0"></td>
    <td><input type="number" step="0.01" class="form-control form-control-sm iva" value="21"></td>
    <td class="neto">0.00</td>
    <td class="ivai">0.00</td>
    <td><button type="button" class="btn btn-sm btn-link" onclick="this.closest('tr').remove(); recalc();">Quitar</button></td>`;
        tbody.appendChild(tr);
    }

    function recalc() {
        let neto = 0,
            iva = 0;
        tbody.querySelectorAll('tr').forEach(tr => {
            const cant = parseFloat(tr.querySelector('.cant').value || 0);
            const pu = parseFloat(tr.querySelector('.pu').value || 0);
            const aiva = parseFloat(tr.querySelector('.iva').value || 0);
            const n = +(cant * pu).toFixed(2);
            const i = +(n * (aiva / 100)).toFixed(2);
            tr.querySelector('.neto').textContent = n.toFixed(2);
            tr.querySelector('.ivai').textContent = i.toFixed(2);
            neto += n;
            iva += i;
        });
        document.getElementById('vNeto').textContent = neto.toFixed(2);
        document.getElementById('vIva').textContent = iva.toFixed(2);
        document.getElementById('vTotal').textContent = (neto + iva).toFixed(2);
    }
    tbody.addEventListener('input', recalc);

    document.getElementById('frmFactura').addEventListener('submit', (e) => {
        const items = [];
        tbody.querySelectorAll('tr').forEach(tr => {
            items.push({
                descripcion: tr.querySelector('.desc').value.trim(),
                cantidad: parseFloat(tr.querySelector('.cant').value || 0),
                precio_unit: parseFloat(tr.querySelector('.pu').value || 0),
                alicuota_iva: parseFloat(tr.querySelector('.iva').value || 0)
            });
        });
        document.getElementById('itemsJson').value = JSON.stringify(items);
    });
</script>