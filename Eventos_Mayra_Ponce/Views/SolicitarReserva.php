<?php
require_once dirname(__DIR__) . "/Config/helpers.php";
require_once dirname(__DIR__) . "/controllers/ReservaController.php";

requiereCliente('Para reservar tu fecha inicia sesión o crea tu cuenta. Solo te pediremos tus datos una vez.');

$errores = [];
if (isset($_POST['btnReservar'])) {
    $errores = (new ReservaController())->crear();   // si todo sale bien, redirige al panel
}

// Días que no se pueden elegir (para avisar antes de enviar)
$noDisponibles = array_values(array_unique(array_merge(
    (new Reserva())->fechasOcupadas(),
    (new FechaBloqueada())->fechasFuturas()
)));

$serviciosElegidos = $_POST['servicios'] ?? [];

// Datos de la cuenta: se muestran, no se vuelven a pedir
$cliente       = (new Cliente())->obtenerPorId($_SESSION['id_cliente']);
$nombreCuenta  = trim($cliente['nombres'] . ' ' . $cliente['apellidos']);
$faltaTelefono = trim((string)$cliente['telefono']) === '';
$tipoElegido   = valorPost('tipo', $_GET['tipo'] ?? '');
$iconosServicio = [
    'Decoración' => 'flor', 'Mobiliario y menaje' => 'silla', 'Catering' => 'cubiertos',
    'Sonido e iluminación' => 'musica', 'Fotografía y video' => 'camara', 'Logística completa' => 'brillo',
];

$titulo = 'Solicitar reserva';
$estilos = ['panel.css'];
$oscura = true;
require __DIR__ . "/partials/head.php";
require __DIR__ . "/partials/nav.php";
?>
<section class="banner banner-corto">
    <h1>Solicita <span>tu reserva</span></h1>
    <p>Cuéntanos sobre tu evento y te ayudaremos a hacerlo realidad.</p>
</section>

<main class="reserva" style="background:var(--marfil)">

    <ol class="pasos" aria-label="Pasos de la solicitud">
        <span class="progreso" id="progreso"></span>
        <li class="activo"><b>1</b>Datos del evento</li>
        <li><b>2</b>Servicios</li>
        <li><b>3</b>Confirmación</li>
    </ol>

    <?php if ($errores): ?>
        <div class="alerta alerta-error" role="alert" style="margin:0 0 16px">
            <ul><?php foreach ($errores as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <form method="POST" id="f" class="tarjeta-form" data-un-envio>

        <!-- PASO 1 -->
        <div class="contenido-paso">
            <div class="cuenta-ficha">
                <span class="avatar-mini"><?= e(mb_strtoupper(mb_substr($cliente['nombres'], 0, 1))) ?></span>
                <div class="datos">
                    <span class="rotulo">Reservando como</span>
                    <strong><?= e($nombreCuenta) ?></strong>
                    <span><?= e($cliente['correo']) ?><?= $faltaTelefono ? '' : ' · ' . e($cliente['telefono']) ?></span>
                </div>
                <a class="cambiar" href="<?= url('mi-panel') ?>?seccion=perfil">Editar mis datos</a>
            </div>

            <?php if ($faltaTelefono): ?>
                <div class="pide-telefono">
                    <label class="etiqueta" for="telefono">Tu teléfono o WhatsApp <span class="req">*</span></label>
                    <div class="campo"><?= icono('whatsapp') ?><input type="tel" id="telefono" name="telefono" value="<?= e(valorPost('telefono')) ?>"
                         placeholder="Ej. 318 000 0000" pattern="[0-9 +()\-]{7,20}" required></div>
                    <p class="ayuda">Lo guardamos en tu cuenta, así no te lo volvemos a pedir.</p>
                </div>
            <?php endif; ?>

            <h2>Información del evento</h2>
            <div class="rejilla">
                <div><label class="etiqueta" for="fecha">Fecha del evento <span class="req">*</span></label>
                    <div class="campo"><input type="date" id="fecha" name="fecha" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" value="<?= e(valorPost('fecha')) ?>" required></div>
                    <p class="aviso-fecha" id="aviso-fecha">Ese día el salón no está disponible. Por favor elige otra fecha.</p></div>
                <div><label class="etiqueta" for="hora">Hora del evento <span class="req">*</span></label>
                    <div class="campo"><input type="time" id="hora" name="hora" value="<?= e(valorPost('hora')) ?>" required></div></div>
                <div><label class="etiqueta" for="tipo">Tipo de evento <span class="req">*</span></label>
                    <div class="campo"><select id="tipo" name="tipo" required>
                        <option value="">Selecciona un tipo</option>
                        <?php foreach (TIPOS_EVENTO as $t): ?>
                            <option <?= $tipoElegido === $t ? 'selected' : '' ?>><?= $t ?></option>
                        <?php endforeach; ?>
                    </select></div></div>
                <div><label class="etiqueta" for="invitados">Número de invitados <span class="req">*</span></label>
                    <div class="campo"><input type="number" id="invitados" name="invitados" min="1" max="500" placeholder="Ej. 80" value="<?= e(valorPost('invitados')) ?>" required></div>
                    <p class="ayuda">Nuestros paquetes son desde 30 personas.</p></div>
                <div class="ancho"><label class="etiqueta" for="tematica">Temática que imaginas</label>
                    <div class="campo"><input type="text" id="tematica" name="tematica" maxlength="120" value="<?= e(valorPost('tematica')) ?>"
                         placeholder="Ej. Princesas, mediterráneo, dorado y blanco, vintage..."></div></div>
                <div class="ancho"><label class="etiqueta" for="notas">Notas adicionales</label>
                    <textarea class="area" id="notas" name="notas" rows="3" maxlength="1000" placeholder="Cuéntanos más sobre tu evento..."><?= e(valorPost('notas')) ?></textarea></div>
            </div>
            <div class="fotos">
                <img src="<?= recurso('Imagenes/evento_boda_altar.jpg') ?>" alt="" loading="lazy">
                <img src="<?= recurso('Imagenes/arco_globos.jpg') ?>" alt="" loading="lazy">
                <img src="<?= recurso('Imagenes/fondo_castillo.jpg') ?>" alt="" loading="lazy">
            </div>
            <div class="acciones"><button type="button" class="btn" onclick="siguiente()">Continuar <?= icono('flecha') ?></button></div>
        </div>

        <!-- PASO 2 -->
        <div class="contenido-paso" hidden>
            <h2>¿Qué servicios necesitas?</h2>
            <p class="ayuda" style="margin-top:-8px">Marca los que te interesan. Los cotizamos contigo cuando tu fecha quede confirmada.</p>
            <div class="opciones-servicio">
                <?php foreach (SERVICIOS as $s): ?>
                    <label class="opcion"><input type="checkbox" name="servicios[]" value="<?= $s ?>" <?= in_array($s, $serviciosElegidos, true) ? 'checked' : '' ?>>
                        <span><?= icono($iconosServicio[$s]) ?> <?= $s ?></span></label>
                <?php endforeach; ?>
            </div>
            <div class="acciones">
                <button type="button" class="btn btn-borde" onclick="ir(1)"><?= icono('izquierda') ?> Atrás</button>
                <button type="button" class="btn" onclick="siguiente()">Continuar <?= icono('flecha') ?></button>
            </div>
        </div>

        <!-- PASO 3 -->
        <div class="contenido-paso" hidden>
            <h2>Confirma tu solicitud</h2>
            <div class="resumen"><dl id="resumen"></dl></div>
            <p class="ayuda" style="margin:14px 0 0">Tu solicitud quedará <b>Pendiente</b> mientras la revisamos. Al enviarla verás su seguimiento paso a paso en tu panel.</p>
            <div class="acciones">
                <button type="button" class="btn btn-borde" onclick="ir(2)"><?= icono('izquierda') ?> Atrás</button>
                <button type="submit" name="btnReservar" class="btn"><?= icono('enviar') ?> Enviar solicitud</button>
            </div>
        </div>
    </form>
</main>

<script>
const NO_DISPONIBLES = <?= json_encode($noDisponibles) ?>;
const MESES = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
const CUENTA = <?= json_encode(['nombre' => $nombreCuenta, 'correo' => $cliente['correo'], 'telefono' => (string)$cliente['telefono']], JSON_UNESCAPED_UNICODE) ?>;
const pasos = document.querySelectorAll('.contenido-paso');
const fecha = document.getElementById('fecha');
let paso = 1;

function valor(id) { return document.getElementById(id).value; }

function fechaBonita(iso) {                 // 2026-04-25 -> 25 Abr 2026
    const p = iso.split('-');
    return p[2] + ' ' + MESES[parseInt(p[1], 10) - 1] + ' ' + p[0];
}

function horaBonita(h) {                    // 18:30 -> 6:30 p. m.
    let [hh, mm] = h.split(':').map(Number);
    const sufijo = hh < 12 ? ' a. m.' : ' p. m.';
    hh = hh % 12 || 12;
    return hh + ':' + String(mm).padStart(2, '0') + sufijo;
}

// Avisar en cuanto elijan un día ocupado o en mantenimiento
function revisarFecha() {
    const ocupada = NO_DISPONIBLES.includes(fecha.value);
    fecha.setCustomValidity(ocupada ? 'El salón no está disponible ese día' : '');
    document.getElementById('aviso-fecha').classList.toggle('visible', ocupada);
}
fecha.addEventListener('change', revisarFecha);

function ir(n) {
    paso = n;
    pasos.forEach((d, i) => d.hidden = (i + 1 !== n));
    document.querySelectorAll('.pasos li').forEach((li, i) => li.classList.toggle('activo', i + 1 <= n));
    document.getElementById('progreso').style.width = ((n - 1) * 38) + '%';
    if (n === 3) resumen();
    window.scrollTo({ top: document.querySelector('.pasos').offsetTop - 20, behavior: 'smooth' });
}

// Solo deja avanzar si los campos del paso actual están bien
function siguiente() {
    const campos = pasos[paso - 1].querySelectorAll('input, select, textarea');
    for (const c of campos) {
        if (!c.checkValidity()) { c.reportValidity(); return; }
    }
    ir(paso + 1);
}

function texto(t) { const s = document.createElement('span'); s.textContent = t; return s.innerHTML; }

function resumen() {
    const servicios = [...document.querySelectorAll('.opcion input:checked')].map(c => c.value);
    const tel = CUENTA.telefono || (document.getElementById('telefono') ? valor('telefono') : '');
    const filas = [
        ['A nombre de', CUENTA.nombre],
        ['Contacto', CUENTA.correo + (tel ? ' · ' + tel : '')],
        ['Evento', valor('tipo') + ' · ' + valor('invitados') + ' invitados'],
        ['Temática', valor('tematica') || 'Por definir'],
        ['Fecha y hora', fechaBonita(valor('fecha')) + ', ' + horaBonita(valor('hora'))],
        ['Servicios', servicios.length ? servicios.join(', ') : 'Ninguno seleccionado'],
        ['Notas', valor('notas') || '—'],
    ];
    document.getElementById('resumen').innerHTML =
        filas.map(([k, v]) => '<dt>' + k + '</dt><dd>' + texto(v) + '</dd>').join('');
}

revisarFecha();
</script>

<?php require __DIR__ . "/partials/pie.php"; ?>
