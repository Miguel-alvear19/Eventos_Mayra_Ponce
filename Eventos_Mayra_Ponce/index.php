<?php
/*
=====================================
ENRUTADOR
Convierte direcciones cortas en la vista que corresponde:
  /            -> Views/Home.php
  /login       -> Views/Login.php
  /mi-panel    -> Views/PanelCliente.php   ... (lista completa en RUTAS, Config/helpers.php)
=====================================
*/
define('ENRUTADOR', true);
require_once __DIR__ . '/Config/helpers.php';

// Parte de la dirección después de la carpeta del proyecto, p. ej. "mi-panel"
$ruta = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$ruta = rawurldecode($ruta);
if (BASE_URL !== '' && stripos($ruta, BASE_URL) === 0) {
    $ruta = substr($ruta, strlen(BASE_URL));
}
$ruta = strtolower(trim($ruta, '/'));
if ($ruta === '' || $ruta === 'index.php') $ruta = 'inicio';

if (!isset(RUTAS[$ruta])) {
    flash('info', 'La página que buscabas no existe.');
    redirigir('inicio');
}

define('RUTA_ACTUAL', $ruta);
require __DIR__ . '/Views/' . RUTAS[$ruta];
