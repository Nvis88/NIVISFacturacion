<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Database;
use DateTime;
use PDO;

class ComprobanteModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function buscar(array $filtros, int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT c.*, cl.razon_social
                  FROM comprobante c
             LEFT JOIN clientes cl ON cl.id_cliente = c.cliente_id
                 WHERE 1=1";
        $params = [];
        if (!empty($filtros['fecha_desde'])) {
            $sql .= " AND c.fecha >= :fd";
            $params[':fd'] = $filtros['fecha_desde'];
        }
        if (!empty($filtros['fecha_hasta'])) {
            $sql .= " AND c.fecha <= :fh";
            $params[':fh'] = $filtros['fecha_hasta'];
        }
        if (!empty($filtros['cliente'])) {
            $sql .= " AND cl.razon_social LIKE :cl";
            $params[':cl'] = '%' . $filtros['cliente'] . '%';
        }
        if (!empty($filtros['estado'])) {
            $sql .= " AND c.estado = :st";
            $params[':st'] = $filtros['estado'];
        }
        if (!empty($filtros['tipo'])) {
            $sql .= " AND c.tipo = :tp";
            $params[':tp'] = $filtros['tipo'];
        }

        $sql .= " ORDER BY c.fecha DESC, c.punto_venta, c.numero DESC LIMIT :lim OFFSET :off";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crearCabecera(array $data): int
    {
        $sql = "INSERT INTO comprobante
                (tipo,punto_venta,numero,fecha,cliente_id,moneda,cotizacion,
                 importe_neto,importe_iva,importe_trib,importe_total,estado,created_at)
                VALUES (:tipo,:pv,:nro,:fecha,:cli,:mon,:cot,:neto,:iva,:trib,:tot,'PENDIENTE',NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':tipo' => $data['tipo'],
            ':pv' => $data['punto_venta'],
            ':nro' => null,
            ':fecha' => $data['fecha'],
            ':cli' => $data['cliente_id'],
            ':mon' => $data['moneda'] ?? 'ARS',
            ':cot' => $data['cotizacion'] ?? 1.0,
            ':neto' => $data['importe_neto'],
            ':iva' => $data['importe_iva'],
            ':trib' => $data['importe_trib'] ?? 0,
            ':tot' => $data['importe_total'],
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function agregarItem(int $cbteId, array $it): void
    {
        $sql = "INSERT INTO comprobante_item
                (comprobante_id,descripcion,cantidad,precio_unit,alicuota_iva,importe_neto,importe_iva)
                VALUES (:id,:desc,:cant,:pu,:al,:neto,:iva)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id' => $cbteId,
            ':desc' => $it['descripcion'],
            ':cant' => $it['cantidad'],
            ':pu' => $it['precio_unit'],
            ':al' => $it['alicuota_iva'],
            ':neto' => $it['importe_neto'],
            ':iva' => $it['importe_iva']
        ]);
    }

    public function setEmitido(int $cbteId, array $afip): void
    {
        $sql = "UPDATE comprobante SET
                    numero=:nro, estado='EMITIDO', cae=:cae, cae_vto=:vto,
                    afip_result=:res, afip_obs=:obs, updated_at=NOW()
                WHERE id_comprobante=:id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nro' => $afip['numero'],
            ':cae' => $afip['cae'],
            ':vto' => $afip['cae_vto'],
            ':res' => $afip['resultado'],
            ':obs' => $afip['obs'] ?? null,
            ':id' => $cbteId
        ]);
    }

    public function detalle(int $id): array
    {
        $cab = $this->db->prepare("SELECT c.*, cl.razon_social, cl.cuit FROM comprobante c
                                   LEFT JOIN clientes cl ON cl.id_cliente=c.cliente_id
                                   WHERE c.id_comprobante=:id");
        $cab->execute([':id' => $id]);
        $cabecera = $cab->fetch(PDO::FETCH_ASSOC);

        $it = $this->db->prepare("SELECT * FROM comprobante_item WHERE comprobante_id=:id ORDER BY id_item");
        $it->execute([':id' => $id]);
        $items = $it->fetchAll(PDO::FETCH_ASSOC);

        return ['cabecera' => $cabecera, 'items' => $items];
    }

    public function crearNotaCredito(int $idOriginal, string $motivo): int
    {
        // Carga original
        $orig = $this->detalle($idOriginal);
        // Crea cabecera NC con importes en negativo
        $data = $orig['cabecera'];
        $nuevoId = $this->crearCabecera([
            'tipo' => 'NC',
            'punto_venta' => $data['punto_venta'],
            'fecha' => (new DateTime())->format('Y-m-d'),
            'cliente_id' => $data['cliente_id'],
            'moneda' => $data['moneda'],
            'cotizacion' => $data['cotizacion'],
            'importe_neto' => -1 * (float)$data['importe_neto'],
            'importe_iva'  => -1 * (float)$data['importe_iva'],
            'importe_trib' => -1 * (float)$data['importe_trib'],
            'importe_total' => -1 * (float)$data['importe_total'],
        ]);
        // Items espejo negativos
        foreach ($orig['items'] as $it) {
            $this->agregarItem($nuevoId, [
                'descripcion' => '[NC] ' . $it['descripcion'] . ' (' . $motivo . ')',
                'cantidad'    => -1 * (float)$it['cantidad'],
                'precio_unit' => (float)$it['precio_unit'],
                'alicuota_iva' => (float)$it['alicuota_iva'],
                'importe_neto' => -1 * (float)$it['importe_neto'],
                'importe_iva' => -1 * (float)$it['importe_iva'],
            ]);
        }
        // vincular original
        $this->db->prepare("UPDATE comprobante SET id_original=:orig WHERE id_comprobante=:id")
            ->execute([':orig' => $idOriginal, ':id' => $nuevoId]);

        return $nuevoId;
    }
}
