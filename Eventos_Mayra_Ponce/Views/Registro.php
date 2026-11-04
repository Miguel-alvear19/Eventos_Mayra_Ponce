<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro</title>
</head>
<body>

<h2>Registro de Cliente</h2>

<form method="POST">

    <input type="text" name="nombres" required>

    <input type="text" name="apellidos" required>

    <input type="email" name="correo" required>

    <input type="text" name="telefono">

    <input type="password" name="password" required>

    <button type="submit" name="btnRegistrar">
        Registrarse
    </button>

</form>

<?php

require_once "../Controllers/ClienteController.php";

if(isset($_POST['btnRegistrar'])){

    $controlador = new ClienteController();
    $controlador->registrarCliente();

}
?>

</body>
</html>
