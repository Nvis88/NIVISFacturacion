<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateUsuarioClaveFiscalTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('usuario_clavefiscal', [
            'id'           => false,
            'primary_key'  => ['id'],
            'engine'       => 'InnoDB',
            'encoding'     => 'utf8mb4',
            'collation'    => 'utf8mb4_general_ci',
        ]);

        $table
            ->addColumn('id', 'integer', [
                'identity' => true,
                'signed'   => false
            ])
            ->addColumn('usuario_id', 'integer', [
                'signed' => false
            ])
            ->addColumn('clave_fiscal_enc', 'text', [
                'null'    => false,
                'comment' => 'Clave Fiscal cifrada (base64)',
            ])
            ->addColumn('created_at', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'comment' => 'Fecha de creación',
            ])
            ->addColumn('updated_at', 'datetime', [
                'null'    => true,
                'default' => null,
                'update'  => 'CURRENT_TIMESTAMP',
                'comment' => 'Fecha de última modificación',
            ])
            // Índice para acelerar búsquedas y cumplir requisito de FK
            ->addIndex(['usuario_id'], ['name' => 'idx_usuario_id'])
            ->addForeignKey('usuario_id', 'usuario', 'IdUsuario', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION',
            ])
            ->create();
    }
}
