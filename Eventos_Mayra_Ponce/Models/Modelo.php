<?php

require_once "Conexion.php";

/*
=====================================
CLASE BASE DE LOS MODELOS
Evita repetir prepare / bind_param / execute en cada método.
=====================================
*/
abstract class Modelo {

    protected $conexion;

    public function __construct() {
        $this->conexion = Conexion::conectar();
    }

    // Ejecuta un INSERT / UPDATE / DELETE. Devuelve las filas afectadas.
    protected function ejecutar($sql, $tipos = '', ...$params) {
        $stmt = $this->conexion->prepare($sql);
        if ($tipos !== '') {
            $stmt->bind_param($tipos, ...$params);
        }
        $stmt->execute();
        return $stmt->affected_rows;
    }

    // Ejecuta un SELECT y devuelve todas las filas como arreglos asociativos
    protected function filas($sql, $tipos = '', ...$params) {
        $stmt = $this->conexion->prepare($sql);
        if ($tipos !== '') {
            $stmt->bind_param($tipos, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Igual que filas() pero devuelve solo la primera (o null)
    protected function fila($sql, $tipos = '', ...$params) {
        $r = $this->filas($sql, $tipos, ...$params);
        return $r[0] ?? null;
    }

    protected function ultimoId() {
        return $this->conexion->insert_id;
    }
}
