<?php

class Conexion {

    public static function conectar() {

        $host = "127.0.0.1";
        $usuario = "root";
        $password = "";
        $bd = "Eventos_Mayra_Ponce";

        $conexion = new mysqli($host, $usuario, $password, $bd);

        if ($conexion->connect_error) {
            die("Error de conexión");
        }

        return $conexion;
    }
}