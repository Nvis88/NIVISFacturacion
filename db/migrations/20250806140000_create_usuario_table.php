<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateUsuarioTable extends AbstractMigration
{
    public function change(): void
    {
        // Forzamos InnoDB y utf8mb4 para soportar FKs y tener un charset moderno
        $table = $this->table('usuario', [
            'id'           => false,
            'primary_key'  => ['IdUsuario'],
            'engine'       => 'InnoDB',
            'encoding'     => 'utf8mb4',
            'collation'    => 'utf8mb4_general_ci',
        ]);

        $table
            ->addColumn('IdUsuario', 'integer', [
                'identity' => true,
                'signed'   => false,
            ])
            ->addColumn('CUIT', 'string', [
                'limit' => 11,
                'null'  => true,
            ])
            ->addColumn('TPersona', 'string', [
                'limit' => 1,
                'null'  => true,
            ])
            ->addColumn('Apodo', 'string', [
                'limit' => 25,
                'null'  => true,
            ])
            ->addColumn('ClaveFiscalEnc', 'text', [
                'null'    => true,
                'comment' => 'Clave Fiscal cifrada con libsodium (base64)',
            ])
            ->addColumn('Regimen', 'string', [
                'limit'   => 2,
                'null'    => true,
                'default' => null,
            ])
            ->addColumn('Apellidos', 'string', [
                'limit' => 50,
                'null'  => true,
            ])
            ->addColumn('Nombres', 'string', [
                'limit' => 50,
                'null'  => false,
            ])
            ->addColumn('RazonSocial', 'string', [
                'limit' => 100,
                'null'  => true,
            ])
            ->addColumn('Email', 'string', [
                'limit' => 100,
                'null'  => true,
            ])
            ->addColumn('ExisteCertificado', 'string', [
                'limit'   => 1,
                'null'    => false,
                'default' => 'N',
            ])
            ->addColumn('FechaCertificado', 'datetime', [
                'null'    => true,
                'default' => null,
            ])
            ->addColumn('Privada', 'text', [
                'null'    => false,
            ])
            ->addColumn('Certificado', 'text', [
                'null'    => false,
            ])
            ->addColumn('Clave', 'string', [
                'limit' => 255,
                'null'  => false,
            ])
            ->addColumn('Estado', 'string', [
                'limit'   => 1,
                'null'    => true,
                'default' => 'A',
            ])
            ->create();
    }
}
