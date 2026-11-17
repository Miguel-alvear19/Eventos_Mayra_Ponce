<?php

require_once dirname(__DIR__) . "/Config/helpers.php";
require_once dirname(__DIR__) . "/Models/Reserva.php";
require_once dirname(__DIR__) . "/Models/Cotizacion.php";
require_once dirname(__DIR__) . "/Models/Cliente.php";
require_once dirname(__DIR__) . "/Models/FechaBloqueada.php";
require_once dirname(__DIR__) . "/Models/Administrador.php";

class AdminController {

    /*
    ==========================
    Atiende todos los formularios del panel según el campo "accion"
    y vuelve a la misma sección (patrón POST -> redirección -> GET)
    ==========================
    */
    public function procesar() {
        $accion = $_POST['accion'] ?? '';
        $volver = $_POST['volver'] ?? 'PanelAdmin.php';

        switch ($accion) {
            case 'aprobar':        $this->cambiarEstadoReserva('Aprobada');  break;
            case 'rechazar':       $this->cambiarEstadoReserva('Rechazada'); break;
            case 'responder':      $this->responderCotizacion();             break;
            case 'bloquear':       $this->bloquearFecha();                   break;
            case 'desbloquear':    $this->desbloquearFecha();                break;
            case 'estado_cliente': $this->cambiarEstadoCliente();            break;
            case 'password':       $this->cambiarPassword();                 break;
        }
        // Solo se permite volver a una página del propio panel
        redirigir(str_starts_with($volver, 'PanelAdmin.php') ? $volver : 'PanelAdmin.php');
    }

    private function cambiarEstadoReserva($estado) {
        $id      = (int)($_POST['id_reserva'] ?? 0);
        $reserva = new Reserva();
        $datos   = $reserva->obtener($id);

        if (!$datos) {
            flash('error', 'Selecciona una solicitud de la lista.');
            return;
        }
        if ($estado === 'Aprobada') {
            if ($reserva->fechaOcupada($datos['fecha_evento'], $id)) {
                flash('error', 'No se puede aprobar: ya hay otro evento aprobado el ' . fechaNumerica($datos['fecha_evento']) . '.');
                return;
            }
            if ((new FechaBloqueada())->estaBloqueada($datos['fecha_evento'])) {
                flash('error', 'No se puede aprobar: ese día está marcado como mantenimiento.');
                return;
            }
        }
        $reserva->cambiarEstado($id, $estado);

        // El salón atiende un evento por día: al confirmar uno, las demás solicitudes
        // pendientes de esa fecha se rechazan y el cliente lo ve como "Fecha no disponible"
        $otras = 0;
        if ($estado === 'Aprobada') {
            $otras = $reserva->rechazarOtrasDelDia($datos['fecha_evento'], $id);
        }

        $mensaje = 'La reserva de ' . $datos['nombre_contacto'] . ' quedó ' . ($estado === 'Aprobada' ? 'confirmada' : 'rechazada') . '.';
        if ($otras > 0) {
            $mensaje .= ' ' . ($otras === 1 ? 'La otra solicitud' : "Las otras $otras solicitudes")
                      . ' del ' . fechaNumerica($datos['fecha_evento']) . ($otras === 1 ? ' se rechazó' : ' se rechazaron')
                      . ' automáticamente.';
        }
        flash('exito', $mensaje);
    }

    private function responderCotizacion() {
        $id        = (int)($_POST['id_cotizacion'] ?? 0);
        $monto     = (float)str_replace(['.', ',', '$', ' '], ['', '.', '', ''], $_POST['monto'] ?? '0');
        $respuesta = valorPost('respuesta');

        if ($monto <= 0 || $respuesta === '') {
            flash('error', 'Escribe el valor estimado y un mensaje para el cliente.');
            return;
        }
        (new Cotizacion())->responder($id, $monto, $respuesta);
        flash('exito', 'Cotización respondida. El cliente la verá en su panel.');
    }

    private function bloquearFecha() {
        $fecha  = valorPost('fecha');
        $motivo = valorPost('motivo') ?: 'Mantenimiento';
        if (!DateTime::createFromFormat('Y-m-d', $fecha)) {
            flash('error', 'Fecha no válida.');
            return;
        }
        if ((new Reserva())->fechaOcupada($fecha)) {
            flash('error', 'Ese día ya tiene un evento aprobado; no se puede bloquear.');
            return;
        }
        (new FechaBloqueada())->bloquear($fecha, $motivo);
        flash('exito', 'El ' . fechaNumerica($fecha) . ' quedó marcado como ' . strtolower($motivo) . '.');
    }

    private function desbloquearFecha() {
        $fecha = valorPost('fecha');
        (new FechaBloqueada())->desbloquear($fecha);
        flash('exito', 'El ' . fechaNumerica($fecha) . ' vuelve a estar disponible.');
    }

    private function cambiarEstadoCliente() {
        $estado = ($_POST['estado'] ?? '') === 'INACTIVO' ? 'INACTIVO' : 'ACTIVO';
        (new Cliente())->cambiarEstado((int)($_POST['id_cliente'] ?? 0), $estado);
        flash('exito', $estado === 'ACTIVO' ? 'Cliente activado.' : 'Cliente desactivado: ya no podrá iniciar sesión.');
    }

    private function cambiarPassword() {
        $admin = new Administrador();
        $datos = $admin->obtenerPorId($_SESSION['id_admin']);
        $nueva = $_POST['nueva'] ?? '';

        if (!password_verify($_POST['actual'] ?? '', $datos['password'])) {
            flash('error', 'La contraseña actual no es correcta.');
        } elseif (strlen($nueva) < 8 || $nueva !== ($_POST['confirmar'] ?? '')) {
            flash('error', 'La nueva contraseña debe tener 8 caracteres o más y coincidir con la confirmación.');
        } else {
            $admin->cambiarPassword($_SESSION['id_admin'], $nueva);
            flash('exito', 'Contraseña actualizada.');
        }
    }
}
