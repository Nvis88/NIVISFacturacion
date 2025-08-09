<?php
// db/migrations/20250809_create_comprobante.php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateComprobante extends AbstractMigration {
public function change(): void {
$this->table('comprobante', ['id' => 'id_comprobante'])
->addColumn('tipo', 'string', ['limit' => 3]) // FA,A,B,C,NC,ND
->addColumn('punto_venta', 'integer')
->addColumn('numero', 'integer', ['null' => true]) // puede ser null hasta emisión
->addColumn('fecha', 'date')
->addColumn('cliente_id', 'integer') // FK a clientes unificados
->addColumn('moneda', 'string', ['limit' => 3, 'default' => 'ARS'])
->addColumn('cotizacion', 'decimal', ['precision'=>12,'scale'=>4,'default'=>1])
->addColumn('importe_neto', 'decimal', ['precision'=>14,'scale'=>2,'default'=>0])
->addColumn('importe_iva', 'decimal', ['precision'=>14,'scale'=>2,'default'=>0])
->addColumn('importe_trib', 'decimal', ['precision'=>14,'scale'=>2,'default'=>0])
->addColumn('importe_total', 'decimal', ['precision'=>14,'scale'=>2,'default'=>0])
->addColumn('estado', 'string', ['limit'=>20, 'default'=>'PENDIENTE']) // PENDIENTE, EMITIDO, RECHAZADO, ANULADO
->addColumn('cae', 'string', ['limit'=>14, 'null'=>true])
->addColumn('cae_vto', 'date', ['null'=>true])
->addColumn('afip_result', 'string', ['limit'=>20, 'null'=>true]) // A, R, ...
->addColumn('afip_obs', 'text', ['null'=>true])
->addColumn('id_original', 'integer', ['null'=>true]) // para NC/ND, referencia al comprobante original
->addColumn('created_at', 'datetime', ['default'=>'CURRENT_TIMESTAMP'])
->addColumn('updated_at', 'datetime', ['null'=>true])
->addIndex(['tipo','punto_venta','numero'], ['unique'=>true, 'name'=>'idx_cbte_pvta_nro'])
->create();
}
}