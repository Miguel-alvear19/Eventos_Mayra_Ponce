<?php

class Conexion {

    private static $conexion = null;

    /*
    =====================================
    CONECTAR (una sola conexión por página)
    =====================================
    */
    public static function conectar() {

        if (self::$conexion !== null) {
            return self::$conexion;
        }

        $cfg = require dirname(__DIR__) . "/Config/Database.php";

        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            self::$conexion = new mysqli(
                $cfg['host'], $cfg['usuario'], $cfg['password'], $cfg['bd'], $cfg['puerto']
            );
            self::$conexion->set_charset("utf8mb4");
        } catch (mysqli_sql_exception $e) {
            http_response_code(500);
            die('<div style="font-family:sans-serif;max-width:560px;margin:60px auto;padding:24px;
                 border:1px solid #B8913F;border-radius:12px;background:#FAF6EE;color:#5A3E2B">
                 <h2 style="margin-top:0">No se pudo conectar a la base de datos</h2>
                 <p>Revisa que MySQL esté encendido, que exista la base <b>Eventos_Mayra_Ponce</b>
                 (ejecuta <code>Database/eventos_mayra_ponce.sql</code>) y los datos de
                 <code>Config/Database.php</code>.</p>
                 <p style="font-size:13px;color:#8a7360">Detalle: ' . htmlspecialchars($e->getMessage()) . '</p></div>');
        }

        return self::$conexion;
    }
}
