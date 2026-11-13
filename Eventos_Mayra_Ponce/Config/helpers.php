<?php
/*
=====================================
FUNCIONES QUE USAN TODAS LAS VISTAS
=====================================
*/
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('America/Bogota');

const TIPOS_EVENTO = ['Boda', 'Cumpleaños', 'Evento social', 'Evento empresarial'];
const SERVICIOS    = ['Decoración', 'Mobiliario y menaje', 'Catering', 'Sonido e iluminación', 'Fotografía y video', 'Logística completa'];
const PRESUPUESTOS = ['Hasta $3.000.000', '$3.000.000 - $6.000.000', '$6.000.000 - $10.000.000', 'Más de $10.000.000'];
// ---- DATOS DEL NEGOCIO (cámbialos por los reales; se muestran en el inicio y el pie) ----
const NEGOCIO_TELEFONO  = '+57 300 123 4567';
const NEGOCIO_WHATSAPP  = '573001234567';           // solo números, con indicativo
const NEGOCIO_CORREO    = 'contacto@eventosmayraponce.com';
const NEGOCIO_DIRECCION = 'Dirección del salón, Ciudad';
const NEGOCIO_HORARIO   = 'Lunes a sábado, 9:00 a. m. – 6:00 p. m.';
const NEGOCIO_CAPACIDAD = 250;                       // invitados máximo
const REDES = [
    'facebook'  => 'https://facebook.com/',
    'instagram' => 'https://instagram.com/',
    'tiktok'    => 'https://tiktok.com/',
    'youtube'   => 'https://youtube.com/',
];

const MESES        = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

// Escapar texto antes de imprimirlo en HTML
function e($texto) {
    return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
}

function redirigir($url) {
    header("Location: $url");
    exit();
}

// Mensajes que sobreviven a una redirección (se muestran una sola vez)
function flash($tipo, $mensaje) {
    $_SESSION['flash'] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

function mostrarFlash() {
    if (empty($_SESSION['flash'])) return '';
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return '<div class="alerta alerta-' . e($f['tipo']) . '" role="status">' . e($f['mensaje']) . '</div>';
}

function esCliente() { return isset($_SESSION['id_cliente']); }
function esAdmin()   { return isset($_SESSION['id_admin']); }

function requiereCliente() {
    if (!esCliente()) {
        $_SESSION['volver_a'] = basename($_SERVER['PHP_SELF']);
        flash('info', 'Inicia sesión para continuar.');
        redirigir('Login.php');
    }
}

function requiereAdmin() {
    if (!esAdmin()) {
        flash('info', 'Esta sección es solo para administradores.');
        redirigir('Login.php');
    }
}

// 2026-04-25 -> 25 Abr 2026
function fechaCorta($iso) {
    if (!$iso) return '—';
    $t = strtotime($iso);
    return date('d', $t) . ' ' . substr(MESES[date('n', $t) - 1], 0, 3) . ' ' . date('Y', $t);
}

// 2026-04-25 -> 25/04/2026
function fechaNumerica($iso) {
    return $iso ? date('d/m/Y', strtotime($iso)) : '—';
}

// 18:30:00 -> 6:30 p. m.
function horaBonita($hora) {
    if (!$hora) return '';
    $t = strtotime($hora);
    return date('g:i', $t) . (date('H', $t) < 12 ? ' a. m.' : ' p. m.');
}

function dinero($valor) {
    return '$' . number_format((float)$valor, 0, ',', '.');
}

// Etiqueta de color para los estados. El cliente ve "Confirmada" donde el admin ve "Aprobada".
function etiquetaEstado($estado, $paraCliente = false) {
    $texto = ($paraCliente && $estado === 'Aprobada') ? 'Confirmada' : $estado;
    return '<span class="estado estado-' . strtolower(e($estado)) . '">' . e($texto) . '</span>';
}

function valorPost($campo, $defecto = '') {
    if (!isset($_POST[$campo]) || !is_string($_POST[$campo])) return $defecto;
    $valor = trim($_POST[$campo]);
    // Si llega texto que no es UTF-8 (tildes mal codificadas), se convierte para que MySQL no lo rechace
    if (!mb_check_encoding($valor, 'UTF-8')) {
        $valor = mb_convert_encoding($valor, 'UTF-8', 'Windows-1252');
    }
    return $valor;
}

require_once __DIR__ . '/iconos.php';
