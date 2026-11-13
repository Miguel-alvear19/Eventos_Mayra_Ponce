<?php
session_start();
$_SESSION = [];
// Borra también la cookie de sesión (por si se usó "Recordarme")
$p = session_get_cookie_params();
setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], true);
session_destroy();
header("Location: Login.php");
exit();
