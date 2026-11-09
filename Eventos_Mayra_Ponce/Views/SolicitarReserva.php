<?php
session_start();
$nombre = $_SESSION['nombre'] ?? '';
$correo = $_SESSION['correo'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Solicitar reserva | Eventos Mayra Ponce</title>
    <link rel="stylesheet" href="../Styles/panel.css">
    <link rel="stylesheet" href="../Styles/reserva.css">
</head>
<body>

<header class="topbar">
    <span class="marca">Eventos Mayra Ponce</span>
    <a class="btn" href="PanelCliente.php">Volver a mi panel</a>
</header>

<section class="hero">
    <h1>Solicita tu reserva</h1>
    <p>Cuéntanos sobre tu evento y te ayudaremos a hacerlo realidad.</p>
</section>

<main class="reserva">

    <ol class="pasos">
        <li class="activo"><b>1</b>Datos del evento</li>
        <li><b>2</b>Servicios</li>
        <li><b>3</b>Confirmación</li>
    </ol>

    <form id="f" onsubmit="return false">

        <!-- PASO 1 -->
        <div class="contenido-paso">
            <h2 class="titulo">Información del evento</h2>
            <div class="rejilla">
                <div><label>Nombre completo *</label>
                    <input type="text" id="nombre" value="<?php echo htmlspecialchars($nombre); ?>" placeholder="Tu nombre" required></div>
                <div><label>Correo electrónico *</label>
                    <input type="email" id="correo" value="<?php echo htmlspecialchars($correo); ?>" placeholder="tu@correo.com" required></div>
                <div><label>Fecha del evento *</label>
                    <input type="date" id="fecha" required></div>
                <div><label>Hora del evento *</label>
                    <input type="time" id="hora" required></div>
                <div><label>Tipo de evento *</label>
                    <select id="tipo" required>
                        <option value="">Selecciona un tipo</option>
                        <option>Boda</option><option>Cumpleaños</option>
                        <option>Evento social</option><option>Evento empresarial</option>
                    </select></div>
                <div><label>Número de invitados *</label>
                    <input type="number" id="invitados" min="1" placeholder="Ej. 80" required></div>
                <div class="ancho"><label>Notas adicionales</label>
                    <textarea id="notas" rows="3" placeholder="Cuéntanos más sobre tu evento..."></textarea></div>
            </div>
            <div class="fotos"><div style="background-position:0 50%"></div><div style="background-position:50% 50%"></div><div style="background-position:100% 50%"></div></div>
            <div class="acciones"><button type="button" class="btn" onclick="siguiente()">Continuar</button></div>
        </div>

        <!-- PASO 2 -->
        <div class="contenido-paso" style="display:none">
            <h2 class="titulo">Servicios que necesitas</h2>
            <div class="servicios">
                <label><input type="checkbox" value="Decoración"> Decoración</label>
                <label><input type="checkbox" value="Mobiliario"> Mobiliario y menaje</label>
                <label><input type="checkbox" value="Catering"> Catering</label>
                <label><input type="checkbox" value="Sonido e iluminación"> Sonido e iluminación</label>
                <label><input type="checkbox" value="Logística completa"> Logística completa</label>
            </div>
            <div class="acciones">
                <button type="button" class="btn btn-claro" onclick="ir(1)">Atrás</button>
                <button type="button" class="btn" onclick="siguiente()">Continuar</button>
            </div>
        </div>

        <!-- PASO 3 -->
        <div class="contenido-paso" style="display:none">
            <h2 class="titulo">Confirma tu solicitud</h2>
            <div class="resumen" id="resumen"></div>
            <div class="acciones">
                <button type="button" class="btn btn-claro" onclick="ir(2)">Atrás</button>
                <button type="button" class="btn" onclick="enviar()">Enviar solicitud</button>
            </div>
        </div>

    </form>

    <!-- DEMO: confirmación al enviar -->
    <div id="ok" class="exito" style="display:none">
        <h2 class="titulo" style="text-align:center">¡Solicitud enviada!</h2>
        <p>Recibimos tu solicitud de reserva. Quedó en estado <strong>Pendiente</strong> mientras la revisamos.</p>
        <a class="btn" href="PanelCliente.php#reservas">Ver mis reservas</a>
    </div>

</main>

<script>
let paso = 1;
const meses = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];

function valor(id) { return document.getElementById(id).value; }

function fechaBonita(iso) {            // 2026-04-25  ->  25 Abr 2026
    const p = iso.split('-');
    return p[2] + ' ' + meses[parseInt(p[1], 10) - 1] + ' ' + p[0];
}

function servicios() {
    return Array.from(document.querySelectorAll('.servicios input:checked')).map(c => c.value);
}

function ir(n) {
    paso = n;
    document.querySelectorAll('.contenido-paso').forEach((d, i) =>
        d.style.display = (i + 1 === n) ? 'block' : 'none');
    document.querySelectorAll('.pasos li').forEach((li, i) =>
        li.classList.toggle('activo', i + 1 <= n));
    if (n === 3) resumen();
}

function siguiente() {
    if (paso === 1 && !document.getElementById('f').reportValidity()) return;
    ir(paso + 1);
}

function resumen() {
    const s = servicios();
    document.getElementById('resumen').innerHTML =
        '<p><strong>Nombre:</strong> ' + valor('nombre') + '</p>' +
        '<p><strong>Correo:</strong> ' + valor('correo') + '</p>' +
        '<p><strong>Evento:</strong> ' + valor('tipo') + ' · ' + valor('invitados') + ' invitados</p>' +
        '<p><strong>Fecha y hora:</strong> ' + fechaBonita(valor('fecha')) + ', ' + valor('hora') + '</p>' +
        '<p><strong>Servicios:</strong> ' + (s.length ? s.join(', ') : 'Ninguno seleccionado') + '</p>';
}

function enviar() {
    // DEMO: se guarda en el navegador para que aparezca en "Mis reservas"
    const lista = JSON.parse(sessionStorage.getItem('reservasNuevas') || '[]');
    lista.push({ fecha: fechaBonita(valor('fecha')), evento: valor('tipo'), estado: 'Pendiente' });
    sessionStorage.setItem('reservasNuevas', JSON.stringify(lista));
    document.getElementById('f').style.display = 'none';
    document.querySelector('.pasos').style.display = 'none';
    document.getElementById('ok').style.display = 'block';
}
</script>
</body>
</html>