<?php

require_once dirname(__DIR__) . "/Config/helpers.php";
require_once dirname(__DIR__) . "/Models/Cotizacion.php";

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

        if (esCliente()) {
            $idCliente = $_SESSION['id_cliente'];
            $nombre    = $_SESSION['nombre'];
            $correo    = $_SESSION['correo'];
            $telefono  = '';
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
        if ($invitados < 1 || $invitados > 500)             $errores[] = 'El número de invitados debe estar entre 1 y 500.';
        if ($presupuesto !== '' && !in_array($presupuesto, PRESUPUESTOS, true)) $errores[] = 'Selecciona un rango de presupuesto válido.';
        if ($fecha !== '' && $fecha < date('Y-m-d'))         $errores[] = 'La fecha tentativa no puede estar en el pasado.';

        if ($errores) return $errores;

        (new Cotizacion())->crear(
            $idCliente, $nombre, $correo, $telefono, $tipo, $fecha ?: null,
            $invitados, implode(', ', $servicios), $presupuesto, $mensaje
        );
        return [];
    }
}
