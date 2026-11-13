<?php

require_once "Modelo.php";

/*
=====================================
DÍAS EN QUE EL SALÓN NO SE ALQUILA (mantenimiento, etc.)
=====================================
*/
class FechaBloqueada extends Modelo {

    public function bloquear($fecha, $motivo) {
        return $this->ejecutar(
            "INSERT INTO fechas_bloqueadas (fecha, motivo) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE motivo = VALUES(motivo)",
            "ss", $fecha, $motivo
        );
    }

    public function desbloquear($fecha) {
        return $this->ejecutar("DELETE FROM fechas_bloqueadas WHERE fecha = ?", "s", $fecha) > 0;
    }

    public function estaBloqueada($fecha) {
        return $this->fila("SELECT fecha FROM fechas_bloqueadas WHERE fecha = ?", "s", $fecha) !== null;
    }

    // [ 'AAAA-MM-DD' => 'motivo' ]
    public function delMes($anio, $mes) {
        $r = $this->filas(
            "SELECT fecha, motivo FROM fechas_bloqueadas WHERE YEAR(fecha) = ? AND MONTH(fecha) = ?",
            "ii", $anio, $mes
        );
        return array_column($r, 'motivo', 'fecha');
    }

    public function futuras() {
        return $this->filas("SELECT * FROM fechas_bloqueadas WHERE fecha >= CURDATE() ORDER BY fecha");
    }

    public function fechasFuturas() {
        return array_column($this->futuras(), 'fecha');
    }
}
