<?php

require_once dirname(__DIR__) . "/Config/helpers.php";
require_once dirname(__DIR__) . "/Models/Reserva.php";
require_once dirname(__DIR__) . "/Models/FechaBloqueada.php";

class ReservaController {

    /*
    ==========================
    CREAR RESERVA (cliente con sesión)
    Devuelve una lista de errores; si todo sale bien redirige al panel.
    ==========================
    */
    public function crear() {

        $nombre    = valorPost('nombre');
        $correo    = valorPost('correo');
        $fecha     = valorPost('fecha');
        $hora      = valorPost('hora');
        $tipo      = valorPost('tipo');
        $invitados = (int)valorPost('invitados');
        $notas     = valorPost('notas');
        $servicios = array_intersect($_POST['servicios'] ?? [], SERVICIOS);

        $errores = [];
        if ($nombre === '')                                  $errores[] = 'Escribe tu nombre.';
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL))     $errores[] = 'El correo no es válido.';
        if (!in_array($tipo, TIPOS_EVENTO, true))            $errores[] = 'Selecciona el tipo de evento.';
        if ($invitados < 1 || $invitados > 500)              $errores[] = 'El número de invitados debe estar entre 1 y 500.';
        if (!preg_match('/^\d{2}:\d{2}$/', $hora))           $errores[] = 'Selecciona la hora del evento.';

        $f = DateTime::createFromFormat('Y-m-d', $fecha);
        if (!$f || $f->format('Y-m-d') !== $fecha) {
            $errores[] = 'Selecciona la fecha del evento.';
        } elseif ($fecha <= date('Y-m-d')) {
            $errores[] = 'La fecha del evento debe ser a partir de mañana.';
        } else {
            $reserva = new Reserva();
            if ($reserva->fechaOcupada($fecha))                  $errores[] = 'Lo sentimos, el salón ya está reservado ese día. Elige otra fecha.';
            if ((new FechaBloqueada())->estaBloqueada($fecha))   $errores[] = 'Ese día el salón está en mantenimiento. Elige otra fecha.';
        }

        if ($errores) return $errores;

        (new Reserva())->crear(
            $_SESSION['id_cliente'], $nombre, $correo, $fecha, $hora, $tipo,
            $invitados, implode(', ', $servicios), $notas
        );

        flash('exito', '¡Solicitud enviada! Tu reserva quedó en estado Pendiente mientras la revisamos.');
        redirigir('PanelCliente.php?seccion=reservas');
    }

    /*
    ==========================
    CANCELAR (el cliente cancela una reserva suya)
    ==========================
    */
    public function cancelar() {
        $ok = (new Reserva())->cancelarDeCliente((int)($_POST['id_reserva'] ?? 0), $_SESSION['id_cliente']);
        flash($ok ? 'exito' : 'error', $ok ? 'La reserva fue cancelada.' : 'No se pudo cancelar esa reserva.');
        redirigir('PanelCliente.php?seccion=reservas');
    }
}
