<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use PDOException;

/**
 * Administra operaciones a nivel de servidor MySQL (no a una DB específica):
 * - Verificar existencia de una base
 * - Obtener estadísticas (tamaño, tablas, fecha de creación aprox.)
 * - Crear bases con charset/collation
 */
class DatabaseModel
{
    /** Conexión con permisos para DDL (CREATE DATABASE, etc.) */
    protected PDO $pdoAdmin;

    public function __construct(PDO $pdoAdmin)
    {
        $this->pdoAdmin = $pdoAdmin;
        $this->pdoAdmin->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdoAdmin->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    /**
     * ¿Existe la base de datos?
     */
    public function exists(string $dbName): bool
    {
        $sql = "SELECT 1
                  FROM information_schema.SCHEMATA
                 WHERE SCHEMA_NAME = :db
                 LIMIT 1";
        $st = $this->pdoAdmin->prepare($sql);
        $st->execute([':db' => $dbName]);
        return (bool) $st->fetchColumn();
    }

    /**
     * Estadísticas de la base: tamaño total (bytes), tamaño legible,
     * fecha de creación aprox. (mínimo CREATE_TIME entre tablas) y #tablas.
     *
     * @return array{
     *   bytes:int,
     *   human_size:string,
     *   create_time: (string|null),
     *   tables:int
     * }
     */
    public function getStats(string $dbName): array
    {
        // Tamaño total
        $sqlSize = "
            SELECT COALESCE(SUM(data_length + index_length), 0) AS bytes
            FROM information_schema.TABLES
            WHERE table_schema = :db
        ";
        $st1 = $this->pdoAdmin->prepare($sqlSize);
        $st1->execute([':db' => $dbName]);
        $bytes = (int) ($st1->fetchColumn() ?: 0);

        // Fecha de creación aprox. (mínima create_time entre tablas)
        $sqlCreated = "
            SELECT MIN(create_time)
            FROM information_schema.TABLES
            WHERE table_schema = :db
        ";
        $st2 = $this->pdoAdmin->prepare($sqlCreated);
        $st2->execute([':db' => $dbName]);
        $createTime = $st2->fetchColumn() ?: null;

        // Cantidad de tablas
        $sqlCount = "
            SELECT COUNT(*)
            FROM information_schema.TABLES
            WHERE table_schema = :db
        ";
        $st3 = $this->pdoAdmin->prepare($sqlCount);
        $st3->execute([':db' => $dbName]);
        $tables = (int) $st3->fetchColumn();

        return [
            'bytes'       => $bytes,
            'human_size'  => $this->humanSize($bytes),
            'create_time' => $createTime ?: null,
            'tables'      => $tables,
        ];
    }

    /**
     * Crea la base de datos (si no existe) con charset/collation dados.
     * Devuelve true si la sentencia se ejecuta sin error (MySQL devuelve 1 o 0).
     */
    public function createDatabase(
        string $dbName,
        string $charset = 'utf8mb4',
        string $collation = 'utf8mb4_unicode_ci'
    ): bool {
        // Escapar nombre de DB como identificador
        $safe = str_replace('`', '``', $dbName);
        $sql = "CREATE DATABASE IF NOT EXISTS `{$safe}` CHARACTER SET {$charset} COLLATE {$collation}";
        return $this->pdoAdmin->exec($sql) !== false;
    }

    /**
     * (Opcional) Elimina una base de datos. Úsalo con cuidado.
     */
    public function dropDatabase(string $dbName): bool
    {
        $safe = str_replace('`', '``', $dbName);
        $sql = "DROP DATABASE `{$safe}`";
        return $this->pdoAdmin->exec($sql) !== false;
    }

    /**
     * Convierte bytes a representación legible (KB, MB, GB, …)
     */
    private function humanSize(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }
        $units = ['KB', 'MB', 'GB', 'TB', 'PB'];
        $v = $bytes / 1024;
        foreach ($units as $u) {
            if ($v < 1024) {
                return number_format($v, 2) . ' ' . $u;
            }
            $v /= 1024;
        }
        return number_format($v, 2) . ' EB';
    }
}
