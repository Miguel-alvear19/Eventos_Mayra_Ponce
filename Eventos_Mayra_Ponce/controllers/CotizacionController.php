<?php

require_once dirname(__DIR__) . "/Config/helpers.php";
require_once dirname(__DIR__) . "/Models/Cotizacion.php";
require_once dirname(__DIR__) . "/Models/Cliente.php";
require_once dirname(__DIR__) . "/Models/Reserva.php";

class CotizacionController {

    /*
    ==========================
    CREAR COTIZACIÓN
    Si hay sesión de cliente se usan sus datos; si no, se piden nombre y correo.
    Devuelve una lista de errores; si todo sale bien devuelve [] y la vista muestra el mensaje.
    ==========================
    */
    public function crear() {

        $tipo        = valorPost('tipo');
        $fecha       = valorPost('fecha');
        $invitados   = (int)valorPost('invitados');
        $presupuesto = valorPost('presupuesto');
        $mensaje     = valorPost('mensaje');
        $servicios   = array_intersect($_POST['servicios'] ?? [], SERVICIOS);
        $reserva     = null;

        if (esCliente()) {
            // Con sesión: los datos salen de la cuenta, no del formulario
            $cliente   = (new Cliente())->obtenerPorId($_SESSION['id_cliente']);
            $idCliente = $_SESSION['id_cliente'];
            $nombre    = trim($cliente['nombres'] . ' ' . $cliente['apellidos']);
            $correo    = $cliente['correo'];
            $telefono  = (string)$cliente['telefono'];

            // ¿Viene de una reserva confirmada? Tipo y fecha se toman de ella
            $idReserva = (int)($_POST['id_reserva'] ?? 0);
            if ($idReserva) {
                $reserva = (new Reserva())->obtenerDeCliente($idReserva, $idCliente);
                if (!$reserva || $reserva['estado'] !== 'Aprobada') {
                    return ['Esa reserva no está confirmada todavía; la cotización se pide cuando el salón la confirma.'];
                }
                $tipo  = $reserva['tipo_evento'];
                $fecha = $reserva['fecha_evento'];
                // PROVISIONAL hasta que "cotizaciones" tenga id_reserva (ver Cotizacion::deReserva)
                $mensaje = 'Reserva ' . numeroReserva($idReserva) . ($mensaje !== '' ? ' · ' . $mensaje : '');
            }
        } else {
            $idCliente = null;
            $nombre    = valorPost('nombre');
            $correo    = valorPost('correo');
            $telefono  = valorPost('telefono');
        }

        $errores = [];
        if ($nombre === '')                                 $errores[] = 'Escribe tu nombre.';
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL))    $errores[] = 'Escribe un correo válido para enviarte la cotización.';
        if (!in_array($tipo, TIPOS_EVENTO, true))           $errores[] = 'Selecciona el tipo de evento.';
        if ($invitados < 30 || $invitados > 500)            $errores[] = 'Cotizamos por paquete desde 30 personas (máximo 500).';
        if ($presupuesto !== '' && !in_array($presupuesto, PRESUPUESTOS, true)) $errores[] = 'Selecciona un rango de presupuesto válido.';
        if ($fecha !== '' && $fecha < date('Y-m-d'))         $errores[] = 'La fecha tentativa no puede estar en el pasado.';

        if ($errores) return $errores;

        (new Cotizacion())->crear(
            $idCliente, $nombre, $correo, $telefono, $tipo, $fecha ?: null,
            $invitados, implode(', ', $servicios), $presupuesto, $mensaje
        );

        if ($reserva) {
            flash('exito', 'Recibimos tu solicitud de cotización. Te avisamos en cuanto esté lista.');
            redirigir('mi-panel?seccion=reserva&id=' . (int)$reserva['id_reserva']);
        }
        return [];
    }
}
