<?php ob_start(); // permite redirigir con header() aunque ya haya HTML antes ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión | Eventos Mayra Ponce</title>
    <link rel="stylesheet" href="../Styles/auth.css">
    <link rel="stylesheet" href="../Styles/login.css">
</head>
<body>

<header class="nav">
    <div class="marca">Eventos<br>Mayra Ponce</div>
    <ul>
        <li><a href="#">Inicio</a></li>
        <li><a href="#">Servicios</a></li>
        <li><a href="#">Galería</a></li>
        <li><a href="#">El salón</a></li>
        <li><a href="#">Contacto</a></li>
    </ul>
</header>

<main class="escena">

    <div class="foto"></div>

    <form method="POST" class="tarjeta-auth">
        <h1>Iniciar sesión</h1>
        <p class="sub">Bienvenido de nuevo</p>
        <hr>

        <div class="campo">
            <span>✉</span>
            <input type="email" name="correo" placeholder="Correo electrónico" required>
        </div>

        <div class="campo">
            <span>🔒</span>
            <input type="password" name="password" id="clave" placeholder="Contraseña" required>
            <button type="button" onclick="verClave()" title="Mostrar u ocultar contraseña">👁</button>
        </div>

        <div class="fila">
            <label><input type="checkbox"> Recordarme</label>
            <a href="#">¿Olvidaste tu contraseña?</a>
        </div>

        <button type="submit" name="btnLogin" class="btn-principal">Ingresar</button>

        <p class="pie-tarjeta">¿No tienes cuenta? <a href="Registro.php">Crear cuenta</a></p>

        <!-- TEMPORAL: acceso directo para la demo, se elimina mañana -->
        <p class="demo"><a href="PanelCliente.php">Entrar a la demo como cliente</a></p>
    </form>

</main>

<footer class="pie">Eventos inolvidables, hechos a tu medida</footer>

<script>
function verClave() {
    const c = document.getElementById('clave');
    c.type = (c.type === 'password') ? 'text' : 'password';
}
</script>

<?php
require_once "../controllers/ClienteController.php";

if(isset($_POST['btnLogin'])){

    $controlador = new ClienteController();
    $controlador->loginCliente();

}
?>

</body>
</html>