<?php ob_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Crear cuenta | Eventos Mayra Ponce</title>
    <link rel="stylesheet" href="../Styles/auth.css">
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

    <div class="foto ancha"></div>

    <form method="POST" class="tarjeta-auth" onsubmit="return revisarClaves()">
        <h1>Crear cuenta</h1>
        <p class="sub">Regístrate para gestionar tus solicitudes</p>
        <hr>

        <div class="campo"><span>👤</span>
            <input type="text" name="nombre" placeholder="Nombre completo" required></div>

        <div class="campo"><span>✉</span>
            <input type="email" name="correo" placeholder="Correo electrónico" required></div>

        <div class="campo"><span>📞</span>
            <input type="text" name="telefono" placeholder="Teléfono (opcional)"></div>

        <div class="campo"><span>🔒</span>
            <input type="password" name="password" id="clave1" placeholder="Contraseña" required></div>

        <div class="campo"><span>🔒</span>
            <input type="password" name="confirmar" id="clave2" placeholder="Confirmar contraseña" required></div>

        <label class="terminos"><input type="checkbox" required> Acepto los términos y condiciones</label>

        <button type="submit" name="btnRegistrar" class="btn-principal">Crear cuenta</button>

        <p class="pie-tarjeta">¿Ya tienes cuenta? <a href="Login.php">Iniciar sesión</a></p>
    </form>

</main>

<?php
require_once "../controllers/ClienteController.php";

if(isset($_POST['btnRegistrar'])){

    if($_POST['password'] !== $_POST['confirmar']){
        echo '<p class="aviso">Las contraseñas no coinciden</p>';
    }else{
        // El mockup pide "Nombre completo": lo partimos en nombres y apellidos
        $partes = explode(' ', trim($_POST['nombre']), 2);
        $_POST['nombres'] = $partes[0];
        $_POST['apellidos'] = $partes[1] ?? '';

        $controlador = new ClienteController();
        $controlador->registrarCliente();
    }
}
?>

<footer class="pie">Eventos Mayra Ponce · Hacemos realidad tus momentos</footer>

<script>
function revisarClaves() {
    if (document.getElementById('clave1').value !== document.getElementById('clave2').value) {
        alert('Las contraseñas no coinciden');
        return false;
    }
    return true;
}
</script>
</body>
</html>