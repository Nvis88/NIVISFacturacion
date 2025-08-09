<?php

// declare(strict_types=1);

// namespace App\Models;

// use PDO;
// use PDOException;
// use RuntimeException;

// final class Database
// {
//     private static ?Database $instance = null;
//     private PDO $pdo;

//     private function __construct()
//     {
//         // Leer la configuración desde el .env (asegurate de cargar Dotenv antes)
//         $host    = getenv('DB_HOST') ?: '127.0.0.1';
//         $port    = getenv('DB_PORT') ?: '3306';
//         $db      = getenv('DB_NAME') ?: 'nvfacturando';
//         $user    = getenv('DB_USER') ?: 'root';
//         $pass    = getenv('DB_PASS') ?: '';
//         $charset = getenv('DB_CHARSET') ?: 'utf8mb4';

//         $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $host, $port, $db, $charset);

//         try {
//             $this->pdo = new PDO($dsn, $user, $pass, [
//                 PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
//                 PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
//                 PDO::ATTR_EMULATE_PREPARES   => false,
//             ]);
//         } catch (PDOException $e) {
//             throw new RuntimeException('Error de conexión a la base de datos: ' . $e->getMessage(), 0, $e);
//         }
//     }

//     public static function getConnection(): PDO
//     {
//         if (self::$instance === null) {
//             self::$instance = new self();
//         }
//         return self::$instance->pdo;
//     }
// }


// <?php
declare(strict_types=1);

namespace App\Models;

use PDO;
use PDOException;
use RuntimeException;

final class Database
{
    /** @var array<string, PDO> */
    private static array $pool = [];

    /**
     * Obtiene una conexión PDO. Por defecto usa la BD del tenant (sesión).
     * Usa $dbName = 'global' para forzar la base global (DB_NAME del .env).
     */
    public static function getConnection(?string $dbName = null): PDO
    {
        // Resolver nombre real de BD
        if ($dbName === null) {
            // TENANT (por sesión)
            $dbName = $_SESSION['tenant_db'] ?? null;
            if (!$dbName) {
                // fallback a global si aún no seteaste tenant
                $dbName = getenv('DB_NAME') ?: 'nvfacturando';
            }
        } elseif ($dbName === 'global') {
            $dbName = getenv('DB_NAME') ?: 'nvfacturando';
        }

        $host    = getenv('DB_HOST')     ?: '127.0.0.1';
        $port    = getenv('DB_PORT')     ?: '3306';
        $user    = getenv('DB_USER')     ?: 'root';
        $pass    = getenv('DB_PASSWORD') ?: '';
        $charset = getenv('DB_CHARSET')  ?: 'utf8mb4';

        $key = "{$host}:{$port}:{$dbName}";
        if (isset(self::$pool[$key])) {
            return self::$pool[$key];
        }

        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $host, $port, $dbName, $charset);

        try {
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            throw new RuntimeException('Error de conexión a la base de datos: ' . $e->getMessage(), 0, $e);
        }

        self::$pool[$key] = $pdo;
        return $pdo;
    }
}
