<?php
session_start();

if(!isset($_SESSION['id_cliente'])){
    echo "No has iniciado sesión";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inicio</title>
</head>
<body>

    <h1>Bienvenido <?php echo $_SESSION['nombre']; ?></h1>

    <p>Has iniciado sesión correctamente.</p>

</body>
</html>