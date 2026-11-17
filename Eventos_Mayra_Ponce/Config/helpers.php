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

// Lista provisional: se ajusta con lo que confirme la dueña del salón
const TIPOS_EVENTO = ['Boda', 'Quinceañera', 'Cumpleaños', 'Baby shower', 'Grado', 'Aniversario', 'Bautizo o primera comunión', 'Evento empresarial', 'Otro'];
const SERVICIOS    = ['Decoración', 'Mobiliario y menaje', 'Catering', 'Sonido e iluminación', 'Fotografía y video', 'Logística completa'];
const PRESUPUESTOS = ['Hasta $3.000.000', '$3.000.000 - $6.000.000', '$6.000.000 - $10.000.000', 'Más de $10.000.000'];
// ---- DATOS DEL NEGOCIO (teléfono, dirección y redes confirmados por la dueña; correo, horario y capacidad pendientes) ----
const NEGOCIO_TELEFONO  = '+57 318 401 6922';
const NEGOCIO_WHATSAPP  = '573184016922';           // solo números, con indicativo
const NEGOCIO_CORREO    = '';                        // pendiente: la dueña no ha dado un correo (vacío = no se muestra)
const NEGOCIO_DIRECCION = 'Calle 1ra, Carrera 6, Antigua Embajada Vallenata, Buenaventura';
const NEGOCIO_HORARIO   = 'Atendemos todos los días, de lunes a domingo';   // pendiente: horas exactas
const NEGOCIO_CAPACIDAD = 250;                       // PENDIENTE de confirmar con la dueña (solo limita el formulario; no se muestra en el inicio)
const REDES = [
    'facebook'  => 'https://www.facebook.com/share/1DsPYyVT3m/',
    'instagram' => 'https://www.instagram.com/salondeeventosmayraponce',
    'tiktok'    => 'https://vt.tiktok.com/ZSaTXF7yn/',
];

// Carpeta raíz del proyecto (para calcular la versión de los archivos)
define('RUTA_PROYECTO', dirname(__DIR__));

/* Devuelve la ruta de un archivo con su versión al final.
   Así el navegador no se queda con una copia vieja cuando cambiamos un estilo. */
function recurso(string $relativa): string {
    $disco  = RUTA_PROYECTO . '/' . ltrim($relativa, '/');
    $version = is_file($disco) ? filemtime($disco) : date('Ymd');
    return '../' . ltrim($relativa, '/') . '?v=' . $version;
}

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

function requiereCliente($mensaje = 'Inicia sesión para continuar.') {
    if (!esCliente()) {
        // Recordamos la página (con sus datos, p. ej. ?tipo=Boda) para volver después de entrar
        $consulta = $_SERVER['QUERY_STRING'] ?? '';
        $_SESSION['volver_a'] = basename($_SERVER['PHP_SELF']) . ($consulta !== '' ? '?' . $consulta : '');
        flash('info', $mensaje);
        redirigir('Login.php');
    }
}

// Número visible de una solicitud: 7 -> #0007
function numeroReserva($id) {
    return '#' . str_pad((string)(int)$id, 4, '0', STR_PAD_LEFT);
}

// Días que faltan para una fecha (negativo si ya pasó)
function diasPara($iso) {
    $hoy = new DateTime('today');
    $dia = new DateTime($iso);
    return (int)$hoy->diff($dia)->format('%r%a');
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
