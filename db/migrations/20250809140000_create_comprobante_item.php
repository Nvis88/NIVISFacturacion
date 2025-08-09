<?php
// db/migrations/20250809_create_comprobante_item.php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateComprobanteItem extends AbstractMigration {
    public function change(): void {
        $this->table('comprobante_item', ['id' => 'id_item'])
            ->addColumn('comprobante_id', 'integer')
            ->addColumn('descripcion', 'string', ['limit'=>255])
            ->addColumn('cantidad', 'decimal', ['precision'=>12,'scale'=>2])
            ->addColumn('precio_unit', 'decimal', ['precision'=>14,'scale'=>2])
            ->addColumn('alicuota_iva', 'decimal', ['precision'=>5,'scale'=>2, 'default'=>21.00]) // 0,10.5,21,27...
            ->addColumn('importe_neto', 'decimal', ['precision'=>14,'scale'=>2])
            ->addColumn('importe_iva', 'decimal', ['precision'=>14,'scale'=>2])
            ->addColumn('created_at', 'datetime', ['default'=>'CURRENT_TIMESTAMP'])
            ->addIndex(['comprobante_id'])
            ->create();
    }
}