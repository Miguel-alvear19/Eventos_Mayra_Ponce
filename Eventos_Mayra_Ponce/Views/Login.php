<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>

<h2>Iniciar Sesión</h2>

<form method="POST">

    <input type="email" name="correo" required>

    <input type="password" name="password" required>

    <button type="submit" name="btnLogin">
        Ingresar
    </button>

</form>

<?php

require_once "../Controllers/ClienteController.php";

if(isset($_POST['btnLogin'])){

    $controlador = new ClienteController();
    $controlador->loginCliente();

}
?>

</body>
</html>