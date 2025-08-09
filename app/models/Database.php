<?php

declare(strict_types=1);

class Database
{
    private static ?Database $instance = null;
    private \PDO $pdo;

    private function __construct()
    {
        // Leer la configuración desde el .env (asegúrate de cargar Dotenv antes)
        $host    = getenv('DB_HOST')     ?: '127.0.0.1';
        $port    = getenv('DB_PORT')     ?: '3306';
        $db      = getenv('DB_NAME')     ?: 'nvfacturando';
        $user    = getenv('DB_USER')     ?: 'root';
        $pass    = getenv('DB_PASSWORD') ?: '';
        $charset = getenv('DB_CHARSET')  ?: 'utf8mb4';

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $host,
            $port,
            $db,
            $charset
        );

        try {
            $this->pdo = new \PDO($dsn, $user, $pass, [
                \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (\PDOException $e) {
            // En lugar de die(), lanzamos excepción para manejarla desde el front controller
            throw new \RuntimeException('Error de conexión a la base de datos.');
        }
    }

    /**
     * Retorna la instancia única de PDO
     *
     * @return \PDO
     */
    public static function getConnection(): \PDO
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->pdo;
    }
}
