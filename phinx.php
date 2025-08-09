<?php

declare(strict_types=1);

// 1) Carga de Composer Autoload (para Dotenv)
require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

// 2) Carga de las variables de entorno desde .env
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// 3) Configuración de Phinx
return [
    'paths' => [
        // Carpeta donde vivirán tus migraciones
        'migrations' => __DIR__ . '/db/migrations',
        // (Opcional) si usas seeds
        //'seeds'      => __DIR__ . '/db/seeds',
    ],
    'environments' => [
        // Nombre de la tabla interna que Phinx crea para llevar el log de migraciones
        'default_migration_table' => 'phinxlog',
        // Entorno por defecto al invocar phinx sin --environment
        'default_environment'    => 'development',

        'development' => [
            'adapter' => 'mysql',
            'host'    => getenv('DB_HOST')     ?: '127.0.0.1',
            'name'    => getenv('DB_NAME')     ?: 'nvfacturando',
            'user'    => getenv('DB_USER')     ?: 'root',
            'pass'    => getenv('DB_PASSWORD') ?: '',
            'port'    => getenv('DB_PORT')     ?: '3306',
            'charset' => getenv('DB_CHARSET')  ?: 'utf8mb4',
        ],

        // Si luego configuras un entorno de producción:
        'production' => [
            'adapter' => 'mysql',
            'host'    => getenv('DB_HOST'),
            'name'    => getenv('DB_NAME'),
            'user'    => getenv('DB_USER'),
            'pass'    => getenv('DB_PASSWORD'),
            'port'    => getenv('DB_PORT'),
            'charset' => getenv('DB_CHARSET'),
        ],
        // phinx.php (agrega este bloque dentro de 'environments')
        'tenant' => [
            'adapter' => 'mysql',
            'host'    => getenv('DB_HOST')     ?: '127.0.0.1',
            'name'    => getenv('TENANT_DB')   ?: (getenv('DB_NAME') ?: 'nvfacturando'),
            'user'    => getenv('DB_USER')     ?: 'root',
            'pass'    => getenv('DB_PASSWORD') ?: '',
            'port'    => getenv('DB_PORT')     ?: '3306',
            'charset' => getenv('DB_CHARSET')  ?: 'utf8mb4',
        ],

    ],
];
