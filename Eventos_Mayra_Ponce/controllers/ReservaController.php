<?php

require_once dirname(__DIR__) . "/Config/helpers.php";
require_once dirname(__DIR__) . "/Models/Reserva.php";
require_once dirname(__DIR__) . "/Models/Cliente.php";
require_once dirname(__DIR__) . "/Models/FechaBloqueada.php";

class ReservaController {

    /*
    ==========================
    CREAR RESERVA (cliente con sesión)
    Los datos de contacto salen de la cuenta: no se vuelven a pedir.
    Solo se pide el teléfono si la cuenta todavía no tiene uno (y se guarda en el perfil).
    Devuelve una lista de errores; si todo sale bien lleva al panel de seguimiento.
    ==========================
    */
    public function crear() {

        $clienteM = new Cliente();
        $cliente  = $clienteM->obtenerPorId($_SESSION['id_cliente']);
        if (!$cliente) return ['No encontramos tu cuenta. Inicia sesión de nuevo.'];

        $nombre   = trim($cliente['nombres'] . ' ' . $cliente['apellidos']);
        $correo   = $cliente['correo'];
        $telefono = trim((string)$cliente['telefono']);

        $fecha     = valorPost('fecha');
        $hora      = valorPost('hora');
        $tipo      = valorPost('tipo');
        $invitados = (int)valorPost('invitados');
        $tematica  = valorPost('tematica');
        $notas     = valorPost('notas');
        $servicios = array_intersect($_POST['servicios'] ?? [], SERVICIOS);

        $errores = [];

        // Teléfono: solo si la cuenta no lo tiene
        $telefonoNuevo = '';
        if ($telefono === '') {
            $telefonoNuevo = valorPost('telefono');
            if (!preg_match('/^[0-9 +()-]{7,20}$/', $telefonoNuevo)) {
                $errores[] = 'Escribe un teléfono o WhatsApp para coordinar tu evento.';
            }
        }

        if (!in_array($tipo, TIPOS_EVENTO, true))            $errores[] = 'Selecciona el tipo de evento.';
        if ($invitados < 1 || $invitados > 500)              $errores[] = 'El número de invitados debe estar entre 1 y 500.';
        if (!preg_match('/^\d{2}:\d{2}$/', $hora))           $errores[] = 'Selecciona la hora del evento.';
        if (mb_strlen($tematica) > 120)                      $errores[] = 'La temática es muy larga (máximo 120 caracteres).';

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

        // Guardamos el teléfono en el perfil para no pedirlo nunca más
        if ($telefonoNuevo !== '') {
            $clienteM->actualizarPerfil($cliente['id_cliente'], $cliente['nombres'], $cliente['apellidos'], $telefonoNuevo);
        }

        // PROVISIONAL: la tabla "reservas" aún no tiene columna para la temática.
        // Mientras Miguel la agrega, va en la primera línea de las notas.
        if ($tematica !== '') {
            $notas = 'Temática: ' . $tematica . ($notas !== '' ? "\n" . $notas : '');
        }

        $id = (new Reserva())->crear(
            $_SESSION['id_cliente'], $nombre, $correo, $fecha, $hora, $tipo,
            $invitados, implode(', ', $servicios), $notas
        );

        redirigir('PanelCliente.php?seccion=reserva&id=' . (int)$id . '&nueva=1');
    }

    /*
    ==========================
    CANCELAR (el cliente cancela una reserva suya)
    Vuelve al seguimiento si se canceló desde ahí; si no, a la lista.
    ==========================
    */
    public function cancelar() {
        $id = (int)($_POST['id_reserva'] ?? 0);
        $ok = (new Reserva())->cancelarDeCliente($id, $_SESSION['id_cliente']);
        flash($ok ? 'exito' : 'error', $ok ? 'La reserva fue cancelada.' : 'No se pudo cancelar esa reserva.');

        if (($_POST['volver'] ?? '') === 'detalle') {
            redirigir('PanelCliente.php?seccion=reserva&id=' . $id);
        }
        redirigir('PanelCliente.php?seccion=reservas');
    }
}
