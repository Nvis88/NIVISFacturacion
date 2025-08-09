<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
// Opcional, por claridad (están en el mismo namespace):
use App\Models\Database;

final class UsuarioModel
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getConnection();
    }

    /**
     * Devuelve los datos del usuario (array) si las credenciales son válidas,
     * o null si no coinciden.
     */
    public function verificarCredenciales(string $cuit, string $claveIngresada): ?array
    {
        // Normalizamos CUIT a solo dígitos
        $cuit = preg_replace('/\D+/', '', $cuit ?? '');

        $sql = "SELECT IdUsuario, Usuario, CUIT, Clave, Estado
                  FROM usuario
                 WHERE CUIT = :cuit AND Estado = 'A'
                 LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':cuit' => $cuit]);

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$usuario) {
            return null;
        }

        // Verificamos que la columna Clave sea un hash válido y que coincida
        $hashInfo = password_get_info($usuario['Clave'] ?? '');
        if (empty($hashInfo['algo'])) {
            // La clave guardada no es un hash -> tratamos como credencial inválida
            return null;
        }

        if (!password_verify($claveIngresada, $usuario['Clave'])) {
            return null;
        }

        // Por seguridad, no devolver el hash
        unset($usuario['Clave']);

        return $usuario;
    }
}
