<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateUsuarioCertificado extends AbstractMigration
{
    public function change(): void
    {
        $t = $this->table('usuario_certificado', [
            'id'           => false,
            'primary_key'  => ['id'],
            'engine'       => 'InnoDB',
            'encoding'     => 'utf8mb4',
            'collation'    => 'utf8mb4_general_ci',
        ]);

        $t->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
            ->addColumn('usuario_id', 'integer', ['signed' => false])
            // Guardamos cifrado (Base64) para mantener consistencia con Crypto::encrypt
            ->addColumn('cert_pem_enc',  'text', ['null' => false, 'comment' => 'Certificado X.509 en PEM (cifrado, base64)'])
            ->addColumn('key_pem_enc',   'text', ['null' => false, 'comment' => 'Clave privada en PEM (cifrada, base64)'])
            ->addColumn('passphrase_enc', 'text', ['null' => true,  'comment' => 'Passphrase de la clave privada (cifrada, base64)'])
            // Metadatos útiles
            ->addColumn('ambito', 'string', ['limit' => 10, 'default' => 'prod', 'comment' => 'prod|homo'])
            ->addColumn('subject_cn', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('issuer_cn',  'string', ['limit' => 255, 'null' => true])
            ->addColumn('serial_hex', 'string', ['limit' => 64,  'null' => true])
            ->addColumn('fingerprint_sha256', 'string', ['limit' => 64, 'null' => true])
            ->addColumn('not_before', 'datetime', ['null' => true])
            ->addColumn('not_after',  'datetime', ['null' => true])
            ->addColumn('activo', 'boolean', ['default' => 1])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'datetime', ['null' => true, 'default' => null, 'update' => 'CURRENT_TIMESTAMP'])

            ->addIndex(['usuario_id'], ['name' => 'idx_uc_usuario'])
            ->addForeignKey('usuario_id', 'usuario', 'IdUsuario', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])

            ->create();
    }
}
