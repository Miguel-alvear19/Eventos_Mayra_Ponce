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

/* ---- RUTAS CORTAS (las atiende index.php gracias al .htaccess) ----
   dirección => vista dentro de Views/ */
const RUTAS = [
    'inicio'   => 'Home.php',
    'login'    => 'Login.php',
    'registro' => 'Registro.php',
    'salir'    => 'Logout.php',
    'mi-panel' => 'PanelCliente.php',
    'admin'    => 'PanelAdmin.php',
    'reservar' => 'SolicitarReserva.php',
    'cotizar'  => 'SolicitarCotizacion.php',
];

// Dirección web de la carpeta del proyecto, p. ej. "/Eventos_Mayra_Ponce-1/Eventos_Mayra_Ponce"
$__script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$__base   = defined('ENRUTADOR') ? dirname($__script) : dirname(dirname($__script));
define('BASE_URL', rtrim(str_replace('\\', '/', $__base), '/'));
unset($__script, $__base);

/* Arma la dirección completa de una ruta corta:
   url('inicio') -> /.../Eventos_Mayra_Ponce/
   url('mi-panel?seccion=perfil') -> /.../Eventos_Mayra_Ponce/mi-panel?seccion=perfil
   url('inicio#salon') -> /.../Eventos_Mayra_Ponce/#salon */
function url(string $destino = 'inicio'): string {
    if (preg_match('~^(https?:|/)~', $destino)) return $destino;   // ya es una dirección completa
    $destino = preg_replace('~^inicio(?=$|[?#])~', '', $destino);
    return BASE_URL . '/' . $destino;
}

// Si alguien entra por la dirección vieja (Views/Login.php), lo mandamos a la corta (/login)
if (!defined('ENRUTADOR') && PHP_SAPI !== 'cli') {
    $__vista = basename($_SERVER['SCRIPT_NAME'] ?? '');
    $__ruta  = array_search($__vista, RUTAS, true);
    if ($__ruta !== false && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
        $consulta = $_SERVER['QUERY_STRING'] ?? '';
        header('Location: ' . url($__ruta) . ($consulta !== '' ? '?' . $consulta : ''), true, 301);
        exit();
    }
    unset($__vista, $__ruta);
}

/* Devuelve la ruta de un archivo con su versión al final.
   Así el navegador no se queda con una copia vieja cuando cambiamos un estilo. */
function recurso(string $relativa): string {
    $disco  = RUTA_PROYECTO . '/' . ltrim($relativa, '/');
    $version = is_file($disco) ? filemtime($disco) : date('Ymd');
    return BASE_URL . '/' . ltrim($relativa, '/') . '?v=' . $version;
}

const MESES        = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

// Escapar texto antes de imprimirlo en HTML
function e($texto) {
    return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
}

function redirigir($url) {
    header('Location: ' . url($url));
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
        $_SESSION['volver_a'] = (defined('RUTA_ACTUAL') ? RUTA_ACTUAL : 'inicio') . ($consulta !== '' ? '?' . $consulta : '');
        flash('info', $mensaje);
        redirigir('login');
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
        redirigir('login');
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
