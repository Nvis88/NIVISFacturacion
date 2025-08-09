<?php

namespace App\Utils;

use RuntimeException;

class Crypto
{
    private static function masterKey(): string
    {
        // Intentamos varias fuentes para la variable de entorno
        $b64 = getenv('APP_MASTER_KEY') ?: ($_ENV['APP_MASTER_KEY'] ?? null);
        if (empty($b64)) {
            throw new RuntimeException('No se encontró APP_MASTER_KEY en el entorno');
        }
        $key = base64_decode($b64, true);
        if ($key === false || strlen($key) !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
            throw new RuntimeException('APP_MASTER_KEY inválida o mal formateada');
        }
        return $key;
    }

    public static function encrypt(string $plaintext): string
    {
        $key   = self::masterKey();
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $cipher = sodium_crypto_secretbox($plaintext, $nonce, $key);
        return base64_encode($nonce . $cipher);
    }

    public static function decrypt(string $b64): string
    {
        $key     = self::masterKey();
        $decoded = base64_decode($b64, true);
        $nonce   = mb_substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
        $cipher  = mb_substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');
        $plain   = sodium_crypto_secretbox_open($cipher, $nonce, $key);
        if ($plain === false) {
            throw new RuntimeException('Desencriptación fallida: datos corruptos o clave inválida');
        }
        return $plain;
    }
}
