<?php

require_once "Modelo.php";

class Administrador extends Modelo {

    public function buscarPorCorreo($correo) {
        return $this->fila("SELECT * FROM administradores WHERE correo = ?", "s", $correo);
    }

    public function obtenerPorId($id) {
        return $this->fila("SELECT * FROM administradores WHERE id_admin = ?", "i", $id);
    }

    public function cambiarPassword($id, $nueva) {
        $hash = password_hash($nueva, PASSWORD_DEFAULT);
        return $this->ejecutar("UPDATE administradores SET password = ? WHERE id_admin = ?", "si", $hash, $id) > 0;
    }
}
