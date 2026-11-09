<?php
session_start();
// DEMO: sin verificación de sesión. Si hay sesión usa tus datos; si no, un nombre de ejemplo.
$nombre = $_SESSION['nombre'] ?? 'Dayana';
$correo = $_SESSION['correo'] ?? 'dayana@correo.com';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mi panel | Eventos Mayra Ponce</title>
    <link rel="stylesheet" href="../Styles/panel.css">
</head>
<body>

<header class="topbar">
    <span class="marca">Eventos Mayra Ponce</span>
    <a class="btn" href="SolicitarCotizacion.php">Solicitar cotización</a>
</header>

<section class="hero">
    <h1>Hola, <?php echo htmlspecialchars($nombre); ?></h1>
    <p>Bienvenida a tu panel de cliente</p>
</section>

<div class="layout">

    <nav class="menu">
        <a data-ir="inicio" onclick="mostrar('inicio')">Mi panel</a>
        <a data-ir="reservas" onclick="mostrar('reservas')">Mis reservas</a>
        <a data-ir="cotizaciones" onclick="mostrar('cotizaciones')">Cotizaciones</a>
        <a data-ir="perfil" onclick="mostrar('perfil')">Mi perfil</a>
        <a href="Logout.php">Cerrar sesión</a>
    </nav>

    <main class="contenido">

        <!-- INICIO -->
        <section id="inicio" class="seccion">
            <div class="tarjetas">
                <div class="tarjeta">Mis reservas<b id="n-reservas"></b></div>
                <div class="tarjeta">Cotizaciones<b id="n-cotizaciones"></b></div>
                <div class="tarjeta">Eventos realizados<b>5+</b></div>
            </div>

            <div class="cabecera">
                <h2 class="titulo">Mis próximas reservas</h2>
                <a onclick="mostrar('reservas')">Ver todas</a>
            </div>
            <table>
                <thead><tr><th>Fecha</th><th>Evento</th><th>Estado</th><th>Acciones</th></tr></thead>
                <tbody id="tabla-proximas"></tbody>
            </table>

            <div class="salon">
                <h2 class="titulo">Nuestro salón</h2>
                <p>Un espacio elegante y versátil, perfecto para todo tipo de eventos.</p>
                <a class="btn btn-claro" href="#">Conocer más</a>
            </div>
        </section>

        <!-- RESERVAS -->
        <section id="reservas" class="seccion">
            <div class="cabecera">
                <h2 class="titulo">Mis reservas</h2>
                <a class="btn" href="SolicitarReserva.php">Nueva reserva</a>
            </div>
            <table>
                <thead><tr><th>Fecha</th><th>Evento</th><th>Estado</th><th>Acciones</th></tr></thead>
                <tbody id="tabla-reservas"></tbody>
            </table>
        </section>

        <!-- COTIZACIONES -->
        <section id="cotizaciones" class="seccion">
            <div class="cabecera">
                <h2 class="titulo">Cotizaciones</h2>
                <a class="btn" href="SolicitarCotizacion.php">Solicitar cotización</a>
            </div>
            <table>
                <thead><tr><th>Fecha</th><th>Evento</th><th>Estado</th></tr></thead>
                <tbody id="tabla-cotizaciones"></tbody>
            </table>
        </section>

        <!-- PERFIL -->
        <section id="perfil" class="seccion perfil">
            <h2 class="titulo">Mi perfil</h2>
            <p><strong>Nombre:</strong> <?php echo htmlspecialchars($nombre); ?></p>
            <p><strong>Correo:</strong> <?php echo htmlspecialchars($correo); ?></p>
        </section>

    </main>
</div>

<dialog id="modal">
    <div id="detalle"></div>
    <button class="btn" onclick="document.getElementById('modal').close()">Cerrar</button>
</dialog>

<script src="../Js/panel.js"></script>
</body>
</html>