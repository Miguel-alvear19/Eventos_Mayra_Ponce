<?php

require_once "Modelo.php";

class Cotizacion extends Modelo {

    /*
    =====================================
    CREAR (desde el formulario "Solicitar cotización")
    =====================================
    */
    public function crear($idCliente, $nombre, $correo, $telefono, $tipo, $fecha, $invitados, $servicios, $presupuesto, $mensaje) {
        $this->ejecutar(
            "INSERT INTO cotizaciones
             (id_cliente, nombre_contacto, correo_contacto, telefono, tipo_evento,
              fecha_tentativa, invitados, servicios, presupuesto, mensaje)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            "isssssisss", $idCliente, $nombre, $correo, $telefono, $tipo, $fecha, $invitados, $servicios, $presupuesto, $mensaje
        );
        return $this->ultimoId();
    }

    public function listarPorCliente($idCliente) {
        return $this->filas(
            "SELECT * FROM cotizaciones WHERE id_cliente = ? ORDER BY fecha_solicitud DESC",
            "i", $idCliente
        );
    }

    public function pendientesDeCliente($idCliente) {
        return (int)$this->fila(
            "SELECT COUNT(*) AS n FROM cotizaciones WHERE id_cliente = ? AND estado = 'Pendiente'",
            "i", $idCliente
        )['n'];
    }

    /*
    =====================================
    ADMINISTRADOR
    =====================================
    */
    public function listar() {
        return $this->filas(
            "SELECT * FROM cotizaciones ORDER BY (estado <> 'Pendiente'), fecha_solicitud DESC"
        );
    }

    public function responder($id, $monto, $respuesta) {
        return $this->ejecutar(
            "UPDATE cotizaciones SET monto_estimado = ?, respuesta = ?, estado = 'Respondida'
             WHERE id_cotizacion = ?",
            "dsi", $monto, $respuesta, $id
        ) > 0;
    }

    public function contarPendientes() {
        return (int)$this->fila("SELECT COUNT(*) AS n FROM cotizaciones WHERE estado = 'Pendiente'")['n'];
    }
}
