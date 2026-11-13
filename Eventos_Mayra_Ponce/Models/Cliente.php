<?php

require_once "Modelo.php";

class Cliente extends Modelo {

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

        return $this->ejecutar($sql, "sssss", $nombres, $apellidos, $correo, $telefono, $passwordHash) > 0;
    }

    /*
    =====================================
    BUSCAR USUARIO POR CORREO (solo activos)
    =====================================
    */
    public function buscarPorCorreo($correo) {
        return $this->fila(
            "SELECT * FROM clientes WHERE correo = ? AND estado = 'ACTIVO'",
            "s", $correo
        );
    }

    public function existeCorreo($correo) {
        return $this->fila("SELECT id_cliente FROM clientes WHERE correo = ?", "s", $correo) !== null;
    }

    public function obtenerPorId($id) {
        return $this->fila("SELECT * FROM clientes WHERE id_cliente = ?", "i", $id);
    }

    /*
    =====================================
    PERFIL
    =====================================
    */
    public function actualizarPerfil($id, $nombres, $apellidos, $telefono) {
        $this->ejecutar(
            "UPDATE clientes SET nombres = ?, apellidos = ?, telefono = ? WHERE id_cliente = ?",
            "sssi", $nombres, $apellidos, $telefono, $id
        );
        return true;
    }

    public function cambiarPassword($id, $nueva) {
        $hash = password_hash($nueva, PASSWORD_DEFAULT);
        return $this->ejecutar("UPDATE clientes SET password = ? WHERE id_cliente = ?", "si", $hash, $id) > 0;
    }

    /*
    =====================================
    LISTADO PARA EL ADMINISTRADOR
    =====================================
    */
    public function listarConResumen() {
        return $this->filas(
            "SELECT c.id_cliente, c.nombres, c.apellidos, c.correo, c.telefono, c.estado,
                    COUNT(r.id_reserva) AS total_reservas,
                    SUM(r.estado = 'Aprobada') AS aprobadas
             FROM clientes c
             LEFT JOIN reservas r ON r.id_cliente = c.id_cliente
             GROUP BY c.id_cliente
             ORDER BY c.nombres"
        );
    }

    public function cambiarEstado($id, $estado) {
        return $this->ejecutar("UPDATE clientes SET estado = ? WHERE id_cliente = ?", "si", $estado, $id) > 0;
    }

    public function contar() {
        return (int)$this->fila("SELECT COUNT(*) AS n FROM clientes")['n'];
    }
}
