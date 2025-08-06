<?php

require_once __DIR__ . '/Database.php';

class UsuarioModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function verificarCredenciales($cuit, $claveIngresada)
    {
        $sql = "SELECT * FROM usuario WHERE CUIT = :cuit AND Estado = 'A' LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['cuit' => $cuit]);
        $usuario = $stmt->fetch();

        if ($usuario) {
            // ✅ Verificar que sea un hash válido
            if (!password_get_info($usuario['Clave'])['algo']) {
                return false; // La clave almacenada no es un hash válido
            }

            // ✅ Verificar si coincide con la ingresada
            if (password_verify($claveIngresada, $usuario['Clave'])) {
                return $usuario;
            }
        }

        return false;
    }
}
