<?php

require_once "Conexion.php";

class Cliente {

    private $conexion;

    public function __construct() {
        $this->conexion = Conexion::conectar();
    }

    /*
    =====================================
    REGISTRAR CLIENTE
    =====================================
    */
    public function registrar($nombres, $apellidos, $correo, $telefono, $password) {

        // Encriptar contraseña
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO clientes
                (nombres, apellidos, correo, telefono, password)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->conexion->prepare($sql);

        $stmt->bind_param(
            "sssss",
            $nombres,
            $apellidos,
            $correo,
            $telefono,
            $passwordHash
        );

        return $stmt->execute();
    }

    /*
    =====================================
    BUSCAR USUARIO POR CORREO
    =====================================
    */
    public function buscarPorCorreo($correo) {

        $sql = "SELECT *
                FROM clientes
                WHERE correo = ?
                AND estado = 'ACTIVO'";

        $stmt = $this->conexion->prepare($sql);

        $stmt->bind_param("s", $correo);

        $stmt->execute();

        return $stmt->get_result();
    }

}