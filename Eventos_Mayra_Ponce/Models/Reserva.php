<?php

require_once "Modelo.php";

class Reserva extends Modelo {

    /*
    =====================================
    CREAR (estado inicial: Pendiente)
    =====================================
    */
    public function crear($idCliente, $nombre, $correo, $fecha, $hora, $tipo, $invitados, $servicios, $notas) {
        $this->ejecutar(
            "INSERT INTO reservas
             (id_cliente, nombre_contacto, correo_contacto, fecha_evento, hora_evento,
              tipo_evento, invitados, servicios, notas)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            "isssssiss", $idCliente, $nombre, $correo, $fecha, $hora, $tipo, $invitados, $servicios, $notas
        );
        return $this->ultimoId();
    }

    /*
    =====================================
    CONSULTAS DEL CLIENTE
    =====================================
    */
    public function listarPorCliente($idCliente) {
        return $this->filas(
            "SELECT * FROM reservas WHERE id_cliente = ?
             ORDER BY (fecha_evento < CURDATE()), fecha_evento",
            "i", $idCliente
        );
    }

    public function proximasPorCliente($idCliente, $limite = 3) {
        return $this->filas(
            "SELECT * FROM reservas
             WHERE id_cliente = ? AND fecha_evento >= CURDATE() AND estado IN ('Pendiente','Aprobada')
             ORDER BY fecha_evento LIMIT ?",
            "ii", $idCliente, $limite
        );
    }

    public function resumenCliente($idCliente) {
        return $this->fila(
            "SELECT SUM(estado IN ('Pendiente','Aprobada') AND fecha_evento >= CURDATE()) AS activas,
                    SUM(estado = 'Aprobada' AND fecha_evento < CURDATE())                AS realizados
             FROM reservas WHERE id_cliente = ?",
            "i", $idCliente
        );
    }

    // Una reserva, solo si pertenece a ese cliente (para el panel de seguimiento)
    public function obtenerDeCliente($idReserva, $idCliente) {
        return $this->fila(
            "SELECT * FROM reservas WHERE id_reserva = ? AND id_cliente = ?",
            "ii", $idReserva, $idCliente
        );
    }

    // Reservas confirmadas y futuras del cliente (las únicas que se pueden cotizar)
    public function confirmadasPorCliente($idCliente) {
        return $this->filas(
            "SELECT * FROM reservas
             WHERE id_cliente = ? AND estado = 'Aprobada' AND fecha_evento >= CURDATE()
             ORDER BY fecha_evento",
            "i", $idCliente
        );
    }

    // El cliente solo puede cancelar sus propias reservas pendientes o aprobadas a futuro
    public function cancelarDeCliente($idReserva, $idCliente) {
        return $this->ejecutar(
            "UPDATE reservas SET estado = 'Cancelada'
             WHERE id_reserva = ? AND id_cliente = ?
               AND estado IN ('Pendiente','Aprobada') AND fecha_evento >= CURDATE()",
            "ii", $idReserva, $idCliente
        ) > 0;
    }

    /*
    =====================================
    CONSULTAS DEL ADMINISTRADOR
    =====================================
    */
    public function obtener($id) {
        return $this->fila(
            "SELECT r.*, c.telefono FROM reservas r
             JOIN clientes c ON c.id_cliente = r.id_cliente
             WHERE r.id_reserva = ?",
            "i", $id
        );
    }

    public function listar($estado = '') {
        $sql = "SELECT r.*, c.telefono FROM reservas r
                JOIN clientes c ON c.id_cliente = r.id_cliente";
        if ($estado !== '') {
            return $this->filas($sql . " WHERE r.estado = ? ORDER BY r.fecha_evento", "s", $estado);
        }
        return $this->filas($sql . " ORDER BY FIELD(r.estado,'Pendiente','Aprobada','Rechazada','Cancelada'), r.fecha_evento");
    }

    public function recientes($limite = 5) {
        return $this->filas(
            "SELECT * FROM reservas
             ORDER BY (estado <> 'Pendiente'), fecha_solicitud DESC LIMIT ?",
            "i", $limite
        );
    }

    public function delDia($fecha) {
        return $this->filas(
            "SELECT * FROM reservas WHERE fecha_evento = ? AND estado IN ('Pendiente','Aprobada')
             ORDER BY hora_evento",
            "s", $fecha
        );
    }

    public function cambiarEstado($id, $estado) {
        return $this->ejecutar("UPDATE reservas SET estado = ? WHERE id_reserva = ?", "si", $estado, $id) > 0;
    }

    // ¿Ya hay una reserva aprobada ese día? (el salón atiende un evento por día)
    public function fechaOcupada($fecha, $excluirId = 0) {
        return $this->fila(
            "SELECT id_reserva FROM reservas
             WHERE fecha_evento = ? AND estado = 'Aprobada' AND id_reserva <> ?",
            "si", $fecha, $excluirId
        ) !== null;
    }

    // Fechas futuras con reserva aprobada (para avisar en el formulario)
    public function fechasOcupadas() {
        $r = $this->filas(
            "SELECT DISTINCT fecha_evento FROM reservas
             WHERE estado = 'Aprobada' AND fecha_evento >= CURDATE()"
        );
        return array_column($r, 'fecha_evento');
    }

    // Para el calendario: [ 'AAAA-MM-DD' => ['aprobadas'=>n, 'pendientes'=>n] ]
    public function estadoPorDia($anio, $mes) {
        $r = $this->filas(
            "SELECT fecha_evento,
                    SUM(estado = 'Aprobada')  AS aprobadas,
                    SUM(estado = 'Pendiente') AS pendientes
             FROM reservas
             WHERE YEAR(fecha_evento) = ? AND MONTH(fecha_evento) = ?
             GROUP BY fecha_evento",
            "ii", $anio, $mes
        );
        $dias = [];
        foreach ($r as $f) {
            $dias[$f['fecha_evento']] = $f;
        }
        return $dias;
    }

    /*
    =====================================
    BANDEJA DE DECISIONES
    =====================================
    */
    // Solicitudes pendientes futuras con datos del cliente (teléfono y cuántas reservas ha hecho)
    public function pendientesFuturas() {
        return $this->filas(
            "SELECT r.*, c.telefono,
                    (SELECT COUNT(*) FROM reservas x WHERE x.id_cliente = r.id_cliente) AS reservas_cliente,
                    (SELECT COUNT(*) FROM reservas x WHERE x.id_cliente = r.id_cliente AND x.estado = 'Aprobada') AS aprobadas_cliente,
                    (SELECT COUNT(*) FROM reservas a WHERE a.fecha_evento = r.fecha_evento AND a.estado = 'Aprobada') AS dia_ocupado
             FROM reservas r JOIN clientes c ON c.id_cliente = r.id_cliente
             WHERE r.estado = 'Pendiente' AND r.fecha_evento >= CURDATE()
             ORDER BY r.fecha_evento, r.fecha_solicitud"
        );
    }

    // Al aprobar una, las demás pendientes de ese día quedan rechazadas
    public function rechazarOtrasDelDia($fecha, $exceptoId) {
        return $this->ejecutar(
            "UPDATE reservas SET estado = 'Rechazada'
             WHERE fecha_evento = ? AND estado = 'Pendiente' AND id_reserva <> ?",
            "si", $fecha, $exceptoId
        );
    }

    /*
    =====================================
    RESUMEN DEL ADMINISTRADOR
    =====================================
    */
    public function indicadores() {
        return $this->fila(
            "SELECT
               SUM(estado = 'Pendiente' AND fecha_evento >= CURDATE())                               AS por_revisar,
               SUM(estado = 'Aprobada' AND fecha_evento >= CURDATE())                                AS confirmados_futuros,
               SUM(estado = 'Aprobada' AND YEAR(fecha_evento) = YEAR(CURDATE())
                                       AND MONTH(fecha_evento) = MONTH(CURDATE()))                   AS confirmados_mes,
               SUM(fecha_solicitud >= CURDATE() - INTERVAL 30 DAY)                                   AS solicitudes_30,
               SUM(estado = 'Aprobada')                                                              AS aprobadas,
               SUM(estado = 'Rechazada')                                                             AS rechazadas,
               SUM(estado = 'Aprobada' AND fecha_evento < CURDATE())                                 AS realizados
             FROM reservas"
        );
    }

    public function proximosConfirmados($limite = 5) {
        return $this->filas(
            "SELECT r.*, c.telefono FROM reservas r JOIN clientes c ON c.id_cliente = r.id_cliente
             WHERE r.estado = 'Aprobada' AND r.fecha_evento >= CURDATE()
             ORDER BY r.fecha_evento, r.hora_evento LIMIT ?",
            "i", $limite
        );
    }

    // Últimos 6 meses: solicitudes recibidas y eventos confirmados (por mes de solicitud)
    public function actividadSeisMeses() {
        $r = $this->filas(
            "SELECT DATE_FORMAT(fecha_solicitud, '%Y-%m') AS ym,
                    COUNT(*) AS recibidas, SUM(estado = 'Aprobada') AS confirmadas
             FROM reservas
             WHERE fecha_solicitud >= DATE_FORMAT(CURDATE() - INTERVAL 5 MONTH, '%Y-%m-01')
             GROUP BY ym"
        );
        $porMes = [];
        for ($i = 5; $i >= 0; $i--) {
            $ym = date('Y-m', strtotime("first day of -$i month"));
            $porMes[$ym] = ['recibidas' => 0, 'confirmadas' => 0];
        }
        foreach ($r as $f) {
            if (isset($porMes[$f['ym']])) {
                $porMes[$f['ym']] = ['recibidas' => (int)$f['recibidas'], 'confirmadas' => (int)$f['confirmadas']];
            }
        }
        return $porMes;
    }

    /*
    =====================================
    REPORTES
    =====================================
    */
    public function contarPorEstado() {
        $r = $this->filas("SELECT estado, COUNT(*) AS n FROM reservas GROUP BY estado");
        $total = ['Pendiente' => 0, 'Aprobada' => 0, 'Rechazada' => 0, 'Cancelada' => 0];
        foreach ($r as $f) $total[$f['estado']] = (int)$f['n'];
        return $total;
    }

    public function contarPorTipo() {
        return $this->filas(
            "SELECT tipo_evento, COUNT(*) AS n FROM reservas
             WHERE estado <> 'Rechazada' GROUP BY tipo_evento ORDER BY n DESC"
        );
    }

    // Eventos aprobados en cada mes del año indicado (12 posiciones)
    public function aprobadasPorMes($anio) {
        $r = $this->filas(
            "SELECT MONTH(fecha_evento) AS mes, COUNT(*) AS n FROM reservas
             WHERE estado = 'Aprobada' AND YEAR(fecha_evento) = ? GROUP BY mes",
            "i", $anio
        );
        $meses = array_fill(1, 12, 0);
        foreach ($r as $f) $meses[(int)$f['mes']] = (int)$f['n'];
        return $meses;
    }
}
