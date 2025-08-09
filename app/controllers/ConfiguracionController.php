<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Database;
use App\Utils\Crypto;

class ConfiguracionController
{
    /**
     * Dashboard de Configuración
     * - Clave Fiscal más reciente
     * - Certificado activo más reciente
     * - Existencia/estadísticas de BD sistema y tenant (nv_<CUIT>)
     */
    public function index(): void
    {
        // La sesión ya está iniciada en public/index.php
        if (!isset($_SESSION['usuario']['IdUsuario'])) {
            header('Location: /login');
            exit;
        }

        $uid = (int) $_SESSION['usuario']['IdUsuario'];
        $pdo = Database::getConnection();

        // Resolver CUIT del usuario: primero sesión, si no, DB y cachear en sesión
        $cuit = $_SESSION['usuario']['cuit'] ?? null;
        if (!$cuit) {
            $cuit = $this->getUserCuit($pdo, $uid);
            if ($cuit) {
                $_SESSION['usuario']['cuit'] = $cuit; // cache para próximos requests
            }
        }
        $ten = $this->tenantDbNameFromCuit($cuit);  // nv_<CUIT> o null

        // 1) Última clave fiscal (historial)
        $stmt = $pdo->prepare("
            SELECT clave_fiscal_enc, created_at, updated_at
              FROM usuario_clavefiscal
             WHERE usuario_id = :uid
             ORDER BY created_at DESC
             LIMIT 1
        ");
        $stmt->execute([':uid' => $uid]);
        $hist = $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;

        $claveFiscalSet = (bool) $hist;
        $fechaCreacion  = $hist['created_at'] ?? null;
        $fechaUpdate    = $hist['updated_at'] ?? null;

        // 2) Último certificado ACTIVO
        $stmt = $pdo->prepare("
            SELECT subject_cn, issuer_cn, fingerprint_sha256,
                   not_before, not_after, ambito, created_at, updated_at
              FROM usuario_certificado
             WHERE usuario_id = :uid
               AND activo = 1
             ORDER BY created_at DESC
             LIMIT 1
        ");
        $stmt->execute([':uid' => $uid]);
        $cert = $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;

        $tieneCert = (bool) $cert;

        // 3) Existencia/estadísticas de bases (sistema y tenant)
        $pdoAdmin = $this->pdoAdmin();

        $sys = $this->systemDbName();

        $sysExists = $this->dbExists($pdoAdmin, $sys);
        $sysStats  = $sysExists ? $this->enrichStats($pdoAdmin, $sys) : null;

        $tenExists = null;
        $tenStats  = null;
        if ($ten !== null) {
            $tenExists = $this->dbExists($pdoAdmin, $ten);
            $tenStats  = $tenExists ? $this->enrichStats($pdoAdmin, $ten) : null;
        }

        // 4) Flash y render
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        $this->view('configuracion/index', [
            // Flash
            'flash'          => $flash,

            // Clave fiscal
            'claveFiscalSet' => $claveFiscalSet,
            'fechaCreacion'  => $fechaCreacion,
            'fechaUpdate'    => $fechaUpdate,

            // Certificado
            'tieneCert'      => $tieneCert,
            'cert'           => $cert, // subject_cn, issuer_cn, fingerprint_sha256, not_before, not_after, ambito

            // Bases
            'sysName'        => $sys,
            'sysExists'      => $sysExists,
            'sysStats'       => $sysStats,

            'tenName'        => $ten,
            'tenExists'      => $tenExists,
            'tenStats'       => $tenStats,
        ]);
    }

    /**
     * POST /configuracion/clave-fiscal
     */
    public function updateClaveFiscal(): void
    {
        if (!isset($_SESSION['usuario']['IdUsuario'])) {
            header('Location: /login');
            exit;
        }

        $uid   = (int) $_SESSION['usuario']['IdUsuario'];
        $clave = trim($_POST['clave_fiscal'] ?? '');

        if ($clave === '') {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Debe ingresar una clave válida.'];
            header('Location: /configuracion');
            exit;
        }

        try {
            $enc  = Crypto::encrypt($clave);
            $pdo  = Database::getConnection();
            $stmt = $pdo->prepare("
                INSERT INTO usuario_clavefiscal (usuario_id, clave_fiscal_enc)
                VALUES (:uid, :enc)
            ");
            $stmt->execute([':uid' => $uid, ':enc' => $enc]);

            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Clave Fiscal actualizada correctamente.'];
        } catch (\Throwable $e) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Error al guardar la clave: ' . $e->getMessage()];
        }

        header('Location: /configuracion');
        exit;
    }

    /**
     * POST /configuracion/certificado
     */
    public function updateCertificado(): void
    {
        if (!isset($_SESSION['usuario']['IdUsuario'])) {
            header('Location: /login');
            exit;
        }

        if (!function_exists('openssl_x509_parse')) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'OpenSSL no está habilitado en PHP.'];
            header('Location: /configuracion');
            exit;
        }

        $uid  = (int) $_SESSION['usuario']['IdUsuario'];
        $pass = trim($_POST['passphrase'] ?? '');
        $amb  = $_POST['ambito'] ?? 'prod';

        // Archivos requeridos
        $certFile = $_FILES['certificado'] ?? null;
        $keyFile  = $_FILES['privada']     ?? null;

        if (
            !$certFile || $certFile['error'] !== UPLOAD_ERR_OK ||
            !$keyFile  || $keyFile['error']  !== UPLOAD_ERR_OK
        ) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Debes subir Certificado y Clave Privada.'];
            header('Location: /configuracion');
            exit;
        }

        // Validaciones básicas de tamaño (opcional)
        if (($certFile['size'] ?? 0) > 1024 * 1024 * 2 || ($keyFile['size'] ?? 0) > 1024 * 1024 * 2) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Los archivos no deben superar 2 MB.'];
            header('Location: /configuracion');
            exit;
        }

        $certRaw = @file_get_contents($certFile['tmp_name']);
        $keyRaw  = @file_get_contents($keyFile['tmp_name']);

        if ($certRaw === false || $keyRaw === false) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'No se pudo leer el/los archivo(s) subido(s).'];
            header('Location: /configuracion');
            exit;
        }

        // Normalizamos el certificado a PEM (acepta DER o PEM)
        $x509 = @openssl_x509_read($certRaw);
        if ($x509 === false) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Certificado inválido. Debe ser X.509 (PEM o DER).'];
            header('Location: /configuracion');
            exit;
        }

        $certPem = '';
        if (!@openssl_x509_export($x509, $certPem)) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'No se pudo exportar el certificado a PEM.'];
            header('Location: /configuracion');
            exit;
        }

        // Validar la private key (PEM) y passphrase si corresponde
        $privKey = @openssl_pkey_get_private($keyRaw, $pass !== '' ? $pass : null);
        if ($privKey === false) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'No se pudo abrir la clave privada. Verifique passphrase y formato PEM.'];
            header('Location: /configuracion');
            exit;
        }

        // Verificar correspondencia cert <-> private key
        if (@openssl_x509_check_private_key($x509, $privKey) !== true) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'La clave privada no corresponde al certificado.'];
            header('Location: /configuracion');
            exit;
        }

        // Parseo de metadatos
        $info      = @openssl_x509_parse($x509) ?: [];
        $subjectCn = $info['subject']['CN'] ?? null;
        $issuerCn  = $info['issuer']['CN']  ?? null;
        $serialHex = isset($info['serialNumberHex']) ? strtoupper($info['serialNumberHex']) : null;
        $nb        = isset($info['validFrom_time_t']) ? date('Y-m-d H:i:s', (int) $info['validFrom_time_t']) : null;
        $na        = isset($info['validTo_time_t'])   ? date('Y-m-d H:i:s', (int) $info['validTo_time_t'])   : null;

        // Fingerprint SHA-256 (hash del DER)
        $certPemTemp = '';
        @openssl_x509_export($x509, $certPemTemp);
        $pemBody = trim(preg_replace('/-----BEGIN CERTIFICATE-----|-----END CERTIFICATE-----/i', '', $certPemTemp));
        $der     = base64_decode(str_replace(["\r", "\n"], '', $pemBody));
        $fpSha256 = $der ? hash('sha256', $der) : null;

        // Cifrar contenidos
        $certEnc = Crypto::encrypt($certPem);
        $keyEnc  = Crypto::encrypt($keyRaw);
        $passEnc = $pass !== '' ? Crypto::encrypt($pass) : null;

        // Guardar: desactivar anteriores y crear registro nuevo activo
        $pdo = Database::getConnection();
        $pdo->beginTransaction();

        try {
            $up = $pdo->prepare("
                UPDATE usuario_certificado
                   SET activo = 0
                 WHERE usuario_id = :uid
                   AND activo = 1
            ");
            $up->execute([':uid' => $uid]);

            $ins = $pdo->prepare("
                INSERT INTO usuario_certificado
                    (usuario_id, cert_pem_enc, key_pem_enc, passphrase_enc,
                     ambito, subject_cn, issuer_cn, serial_hex, fingerprint_sha256,
                     not_before, not_after, activo)
                VALUES
                    (:uid, :cert, :pkey, :pph,
                     :amb, :sub, :iss, :ser, :fp,
                     :nb, :na, 1)
            ");

            $ins->execute([
                ':uid' => $uid,
                ':cert' => $certEnc,
                ':pkey' => $keyEnc,
                ':pph' => $passEnc,
                ':amb' => $amb,
                ':sub' => $subjectCn,
                ':iss' => $issuerCn,
                ':ser' => $serialHex,
                ':fp'  => $fpSha256,
                ':nb'  => $nb,
                ':na'  => $na,
            ]);

            $pdo->commit();
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Certificado cargado/actualizado correctamente.'];
        } catch (\Throwable $e) {
            $pdo->rollBack();
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Error guardando certificado: ' . $e->getMessage()];
        }

        header('Location: /configuracion');
        exit;
    }

    /**
     * POST /configuracion/crear-base-datos
     * Crea la base de datos del sistema o la del usuario (tenant) actual.
     */
    public function crearBaseDatos(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /configuracion');
            exit;
        }

        if (!isset($_SESSION['usuario']['IdUsuario'])) {
            header('Location: /login');
            exit;
        }

        $tipo = strtolower(trim($_POST['tipo'] ?? 'sistema')); // 'sistema' | 'usuario'
        $name = null;

        if ($tipo === 'usuario') {
            // Resolver CUIT: sesión -> DB -> cachear en sesión
            $pdo = Database::getConnection();
            $uid = (int) $_SESSION['usuario']['IdUsuario'];

            $cuit = $_SESSION['usuario']['cuit'] ?? null;
            if (!$cuit) {
                $cuit = $this->getUserCuit($pdo, $uid); // lee CUIT de la tabla usuario
                if ($cuit) {
                    $_SESSION['usuario']['cuit'] = $cuit; // cache
                }
            }

            $name = $this->tenantDbNameFromCuit($cuit); // nv_<CUIT> o null si no hay CUIT
            if (!$name) {
                $_SESSION['flash'] = [
                    'type' => 'danger',
                    'msg'  => 'No se pudo determinar el nombre de la base del usuario: falta CUIT.'
                ];
                header('Location: /configuracion');
                exit;
            }
        } else {
            $name = $this->systemDbName();
        }

        try {
            $pdoAdmin = $this->pdoAdmin();

            if ($this->dbExists($pdoAdmin, $name)) {
                $_SESSION['flash'] = ['type' => 'info', 'msg' => "La base '{$name}' ya existe."];
            } else {
                $ok = $this->dbCreate($pdoAdmin, $name);
                $_SESSION['flash'] = $ok
                    ? ['type' => 'success', 'msg' => "Base de datos '{$name}' creada correctamente."]
                    : ['type' => 'danger',  'msg' => "No se pudo crear la base de datos '{$name}'."];
            }
        } catch (\Throwable $e) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Error: ' . $e->getMessage()];
        }

        header('Location: /configuracion');
        exit;
    }

    // =========================
    // Helpers internos
    // =========================

    /**
     * Conexión con privilegios suficientes para DDL (CREATE DATABASE, SHOW DATABASES, etc.)
     */
    protected function pdoAdmin(): \PDO
    {
        $pdo = Database::getConnection();
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        return $pdo;
    }

    /**
     * Verifica si existe una base de datos
     */
    protected function dbExists(\PDO $pdo, string $dbName): bool
    {
        $stmt = $pdo->query("SHOW DATABASES LIKE " . $pdo->quote($dbName));
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Crea una base de datos con charset/collation razonables
     */
    protected function dbCreate(\PDO $pdo, string $dbName): bool
    {
        $sql = "CREATE DATABASE `" . str_replace('`', '``', $dbName) . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        return $pdo->exec($sql) !== false;
    }

    /**
     * Obtiene estadísticas simples de la base (tablas y tamaños)
     * Devuelve: ['tables' => int, 'data_length' => int, 'index_length' => int, 'total_bytes' => int]
     */
    protected function dbGetStats(\PDO $pdo, string $dbName): array
    {
        // Cantidad de tablas
        $q1 = $pdo->prepare("SELECT COUNT(*) AS cnt FROM information_schema.tables WHERE table_schema = :db");
        $q1->execute([':db' => $dbName]);
        $tables = (int) ($q1->fetch()['cnt'] ?? 0);

        // Tamaños
        $q2 = $pdo->prepare("
            SELECT
                COALESCE(SUM(data_length),0)  AS data_length,
                COALESCE(SUM(index_length),0) AS index_length
            FROM information_schema.tables
            WHERE table_schema = :db
        ");
        $q2->execute([':db' => $dbName]);
        $row = $q2->fetch() ?: ['data_length' => 0, 'index_length' => 0];

        $data  = (int) $row['data_length'];
        $index = (int) $row['index_length'];

        return [
            'tables'       => $tables,
            'data_length'  => $data,
            'index_length' => $index,
            'total_bytes'  => $data + $index,
        ];
    }

    /**
     * Nombre de la base de datos del sistema
     */
    protected function systemDbName(): string
    {
        return 'nvfacturando';
    }

    /**
     * Nombre de la base de datos del tenant usando CUIT de sesión (sin fallback)
     */
    protected function tenantDbName(): ?string
    {
        $cuit = $_SESSION['usuario']['cuit'] ?? null;
        if (!$cuit) {
            return null; // sin CUIT, no inventamos nombres
        }
        $cuit = preg_replace('/\D+/', '', (string) $cuit);
        return $cuit !== '' ? "nv_{$cuit}" : null;
    }

    /**
     * Render simple de vistas con tu MVC (convención: app/views/<ruta>.php)
     */
    protected function view(string $view, array $data = []): void
    {
        $file = __DIR__ . "/../views/{$view}.php";
        if (!is_file($file)) {
            http_response_code(500);
            echo "Vista no encontrada: " . htmlspecialchars($view);
            return;
        }
        extract($data, EXTR_SKIP);
        require $file;
    }

    protected function humanBytes(int $bytes): string
    {
        $u = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $i = 0;
        $n = max($bytes, 0);
        while ($n >= 1024 && $i < count($u) - 1) {
            $n /= 1024;
            $i++;
        }
        return round($n, 2) . ' ' . $u[$i];
    }

    protected function dbGetCreateTime(\PDO $pdo, string $dbName): ?string
    {
        // Tomamos la tabla más antigua como aprox. de "creación"
        $q = $pdo->prepare("
            SELECT MIN(CREATE_TIME)
            FROM information_schema.tables
            WHERE table_schema = :db
        ");
        $q->execute([':db' => $dbName]);
        $t = $q->fetchColumn();
        return $t ?: null;
    }

    /** Combina stats + campos esperados por la vista */
    protected function enrichStats(\PDO $pdo, string $dbName): array
    {
        $s = $this->dbGetStats($pdo, $dbName); // tables, data_length, index_length, total_bytes
        $bytes = (int)($s['total_bytes'] ?? 0);
        return [
            'tables'       => (int)($s['tables'] ?? 0),
            'data_length'  => (int)($s['data_length'] ?? 0),
            'index_length' => (int)($s['index_length'] ?? 0),
            'total_bytes'  => $bytes,
            // Claves que la vista usa:
            'bytes'        => $bytes,
            'human_size'   => $this->humanBytes($bytes),
            'create_time'  => $this->dbGetCreateTime($pdo, $dbName),
        ];
    }

    /** Lee el CUIT del usuario desde la base y lo normaliza a solo dígitos. */
    protected function getUserCuit(\PDO $pdo, int $uid): ?string
    {
        // Ajustá nombres de tabla/campo a tu esquema real:
        $q = $pdo->prepare("SELECT cuit FROM usuario WHERE IdUsuario = :uid LIMIT 1");
        $q->execute([':uid' => $uid]);
        $cuit = $q->fetchColumn();
        if (!$cuit) return null;
        $cuit = preg_replace('/\D+/', '', (string)$cuit);
        return $cuit !== '' ? $cuit : null;
    }

    /** Arma el nombre de la base del tenant a partir de un CUIT normalizado. */
    protected function tenantDbNameFromCuit(?string $cuit): ?string
    {
        if (!$cuit) return null;
        return "nv_{$cuit}";
    }
}
