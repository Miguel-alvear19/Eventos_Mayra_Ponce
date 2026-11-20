<?php
require_once dirname(__DIR__) . "/Config/helpers.php";
$_SESSION = [];
// Borra también la cookie de sesión (por si se usó "Recordarme")
$p = session_get_cookie_params();
setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], true);
session_destroy();
redirigir('login');
