<?php
// app/controllers/FacturacionController.php
declare(strict_types=1);

class FacturacionController extends BaseController
{
    public function index(): void
    {
        header('Location: /ventas/facturacion/listado');
        exit;
    }

    public function listado(): void
    {
        $Comprobante = $this->model('ComprobanteModel');
        $filtros = [
            'fecha_desde' => $_GET['fd'] ?? null,
            'fecha_hasta' => $_GET['fh'] ?? null,
            'cliente'     => $_GET['cl'] ?? null,
            'estado'      => $_GET['st'] ?? null,
            'tipo'        => $_GET['tp'] ?? null,
        ];
        $data = [
            'registros' => $Comprobante->buscar($filtros, 50, 0),
            'filtros'   => $filtros,
        ];
        $this->view('facturacion/listado', $data);
    }

    public function formNueva(): void
    {
        $this->view('facturacion/form_nueva', ['hoy' => date('Y-m-d')]);
    }

    public function crear(): void
    {
        $Comprobante = $this->model('ComprobanteModel');

        // 1) Validar POST (mínimo)
        $items = json_decode($_POST['items'] ?? '[]', true);
        if (!$items) {
            http_response_code(422);
            echo "Items requeridos";
            return;
        }

        // 2) Calcular totales
        $neto = 0;
        $iva = 0;
        foreach ($items as &$it) {
            $it['importe_neto'] = round($it['cantidad'] * $it['precio_unit'], 2);
            $it['importe_iva']  = round($it['importe_neto'] * ($it['alicuota_iva'] / 100), 2);
            $neto += $it['importe_neto'];
            $iva += $it['importe_iva'];
        }
        $total = $neto + $iva;

        // 3) Crear cabecera + items en BD (estado PENDIENTE)
        $id = $Comprobante->crearCabecera([
            'tipo' => $_POST['tipo'],
            'punto_venta' => (int)$_POST['punto_venta'],
            'fecha' => $_POST['fecha'],
            'cliente_id' => (int)$_POST['cliente_id'],
            'moneda' => $_POST['moneda'] ?? 'ARS',
            'cotizacion' => (float)($_POST['cotizacion'] ?? 1),
            'importe_neto' => $neto,
            'importe_iva' => $iva,
            'importe_trib' => 0,
            'importe_total' => $total,
        ]);
        foreach ($items as $it) {
            $Comprobante->agregarItem($id, $it);
        }

        // 4) Emitir en ARCA (si ya lo tenés) o dejar en PENDIENTE.
        //    Aquí sólo dejo la llamada conceptual:
        /*
        $resp = $this->service('ArcaService')->emitirFactura($id); // devuelve nro, cae, cae_vto, resultado, obs
        if ($resp['resultado']==='A') {
            $Comprobante->setEmitido($id, $resp);
        } else { // RECHAZADO -> guardar estado y motivo
        }
        */

        header('Location: /ventas/facturacion/detalle?id=' . $id);
    }

    public function detalle(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(404);
            echo "No encontrado";
            return;
        }
        $Comprobante = $this->model('ComprobanteModel');
        $this->view('facturacion/detalle', $Comprobante->detalle($id));
    }

    public function anular(): void
    {
        // Anular = emitir Nota de Crédito
        $id = (int)($_POST['id'] ?? 0);
        $motivo = trim($_POST['motivo'] ?? 'Anulación');
        if ($id <= 0) {
            http_response_code(422);
            echo "ID inválido";
            return;
        }

        $Comprobante = $this->model('ComprobanteModel');
        $ncId = $Comprobante->crearNotaCredito($id, $motivo);

        // (Opcional) Enviar NC a ARCA
        // $resp = $this->service('ArcaService')->emitirNotaCredito($ncId);
        // if ($resp['resultado']==='A') { $Comprobante->setEmitido($ncId, $resp); }

        header('Location: /ventas/facturacion/detalle?id=' . $ncId);
    }
}
