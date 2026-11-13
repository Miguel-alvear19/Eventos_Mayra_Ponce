<?php
require_once dirname(__DIR__) . "/Config/helpers.php";
require_once dirname(__DIR__) . "/controllers/ClienteController.php";
require_once dirname(__DIR__) . "/controllers/ReservaController.php";
require_once dirname(__DIR__) . "/Models/Cotizacion.php";

requiereCliente();

/*
==========================
ACCIONES (formularios de esta página)
==========================
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($_POST['accion'] ?? '') {
        case 'cancelar': (new ReservaController())->cancelar();        break;
        case 'perfil':   (new ClienteController())->actualizarPerfil(); break;
        case 'password': (new ClienteController())->cambiarPassword();  break;
    }
    redirigir('PanelCliente.php');
}

/*
==========================
DATOS
==========================
*/
$idCliente    = $_SESSION['id_cliente'];
$cliente      = (new Cliente())->obtenerPorId($idCliente);
$reservaM     = new Reserva();
$cotizacionM  = new Cotizacion();
$resumen      = $reservaM->resumenCliente($idCliente);
$proximas     = $reservaM->proximasPorCliente($idCliente);
$reservas     = $reservaM->listarPorCliente($idCliente);
$cotizaciones = $cotizacionM->listarPorCliente($idCliente);
$pendientes   = $cotizacionM->pendientesDeCliente($idCliente);

$secciones = [
    'inicio'       => ['Mi panel',     'casa'],
    'reservas'     => ['Mis reservas', 'calendario'],
    'cotizaciones' => ['Cotizaciones', 'documento'],
    'perfil'       => ['Mi perfil',    'usuario'],
];
$seccion = isset($secciones[$_GET['seccion'] ?? '']) ? $_GET['seccion'] : 'inicio';

// Fila de la tabla de reservas (se usa en "Mi panel" y en "Mis reservas")
function filaReserva($r, $conCancelar) {
    $hoy = date('Y-m-d');
    $puedeCancelar = $conCancelar && in_array($r['estado'], ['Pendiente', 'Aprobada']) && $r['fecha_evento'] >= $hoy;
    $datos = e(json_encode([
        'Evento'       => $r['tipo_evento'],
        'Fecha'        => fechaCorta($r['fecha_evento']) . ', ' . horaBonita($r['hora_evento']),
        'Invitados'    => $r['invitados'],
        'Servicios'    => $r['servicios'] ?: 'Ninguno',
        'Notas'        => $r['notas'] ?: '—',
        'Solicitada'   => fechaCorta($r['fecha_solicitud']),
    ]));
    ob_start(); ?>
    <tr>
        <td><?= fechaCorta($r['fecha_evento']) ?><span class="sub-dato"><?= horaBonita($r['hora_evento']) ?></span></td>
        <td><?= e($r['tipo_evento']) ?><span class="sub-dato"><?= (int)$r['invitados'] ?> invitados</span></td>
        <td><?= etiquetaEstado($r['estado'], true) ?></td>
        <td class="acciones-celda">
            <button type="button" class="btn btn-borde btn-sm" onclick="verReserva(this)" data-reserva="<?= $datos ?>">Ver</button>
            <?php if ($puedeCancelar): ?>
                <form method="POST" onsubmit="return confirm('¿Seguro que quieres cancelar esta reserva?')">
                    <input type="hidden" name="accion" value="cancelar">
                    <input type="hidden" name="id_reserva" value="<?= (int)$r['id_reserva'] ?>">
                    <button class="btn btn-oscuro btn-sm">Cancelar</button>
                </form>
            <?php endif; ?>
        </td>
    </tr>
    <?php return ob_get_clean();
}

$titulo = 'Mi panel';
$estilos = ['panel.css'];
$claseBody = 'panel';
$oscura = true;
require "partials/head.php";
require "partials/nav.php";
?>
<section class="banner">
    <h1>Hola, <?= e($cliente['nombres']) ?> <span aria-hidden="true">👋</span></h1>
    <p>Bienvenida(o) a tu panel de cliente</p>
</section>

<div class="layout">

    <nav class="menu" aria-label="Menú del panel">
        <?php foreach ($secciones as $clave => [$texto, $ico]): ?>
            <a href="?seccion=<?= $clave ?>" class="<?= $seccion === $clave ? 'activo' : '' ?>"><?= icono($ico) ?> <?= $texto ?></a>
        <?php endforeach; ?>
        <a href="Logout.php"><?= icono('salir') ?> Cerrar sesión</a>
        <div class="agradecimiento">Gracias por<br>confiar en nosotros <?= icono('corazon') ?><?= icono('flor', 'hojas') ?></div>
    </nav>

    <main class="contenido">
        <?= mostrarFlash() ?>

        <?php if ($seccion === 'inicio'): ?>
        <!-- ================= MI PANEL ================= -->
        <div class="tarjetas">
            <a class="tarjeta" href="?seccion=reservas" style="text-decoration:none">
                <?= icono('calendario') ?><span class="nombre">Mis reservas</span>
                <b><?= (int)$resumen['activas'] ?> <?= (int)$resumen['activas'] === 1 ? 'activa' : 'activas' ?></b></a>
            <a class="tarjeta" href="?seccion=cotizaciones" style="text-decoration:none">
                <?= icono('documento') ?><span class="nombre">Cotizaciones</span>
                <b><?= $pendientes ?> <?= $pendientes === 1 ? 'pendiente' : 'pendientes' ?></b></a>
            <div class="tarjeta">
                <?= icono('estrella') ?><span class="nombre">Eventos realizados</span>
                <b><?= (int)$resumen['realizados'] ?></b></div>
        </div>

        <div class="cabecera">
            <h2><?= icono('calendario') ?> Mis próximas reservas</h2>
            <a class="enlace-flecha" href="?seccion=reservas">Ver todas <?= icono('flecha') ?></a>
        </div>
        <div class="tabla-caja">
            <table>
                <thead><tr><th>Fecha</th><th>Evento</th><th>Estado</th><th>Acciones</th></tr></thead>
                <tbody>
                <?php foreach ($proximas as $r) echo filaReserva($r, false); ?>
                <?php if (!$proximas): ?>
                    <tr><td colspan="4" class="vacio">Aún no tienes reservas próximas.
                        <a href="SolicitarReserva.php">¡Solicita la primera!</a></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="salon-card">
            <img src="../Imagenes/salon_mini.jpg" alt="Salón de eventos decorado">
            <div>
                <h2>Nuestro salón</h2>
                <p>Un espacio elegante y versátil, perfecto para todo tipo de eventos.</p>
                <a class="btn btn-borde" href="Home.php#salon">Conocer más <?= icono('flecha') ?></a>
            </div>
        </div>

        <?php elseif ($seccion === 'reservas'): ?>
        <!-- ================= MIS RESERVAS ================= -->
        <div class="cabecera">
            <h2><?= icono('calendario') ?> Mis reservas</h2>
            <a class="btn" href="SolicitarReserva.php"><?= icono('mas') ?> Nueva reserva</a>
        </div>
        <div class="tabla-caja">
            <table>
                <thead><tr><th>Fecha</th><th>Evento</th><th>Estado</th><th>Acciones</th></tr></thead>
                <tbody>
                <?php foreach ($reservas as $r) echo filaReserva($r, true); ?>
                <?php if (!$reservas): ?>
                    <tr><td colspan="4" class="vacio">Todavía no has hecho ninguna reserva.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php elseif ($seccion === 'cotizaciones'): ?>
        <!-- ================= COTIZACIONES ================= -->
        <div class="cabecera">
            <h2><?= icono('documento') ?> Mis cotizaciones</h2>
            <a class="btn" href="SolicitarCotizacion.php"><?= icono('mas') ?> Solicitar cotización</a>
        </div>
        <div class="cotizaciones">
            <?php foreach ($cotizaciones as $c): ?>
                <article class="cotizacion">
                    <div class="cabeza">
                        <h3><?= e($c['tipo_evento']) ?></h3>
                        <?= etiquetaEstado($c['estado']) ?>
                    </div>
                    <div class="detalles">
                        <span><?= icono('calendario') ?> <?= $c['fecha_tentativa'] ? fechaCorta($c['fecha_tentativa']) : 'Fecha por definir' ?></span>
                        <span><?= icono('usuarios') ?> <?= (int)$c['invitados'] ?> invitados</span>
                        <?php if ($c['presupuesto']): ?><span><?= icono('dinero') ?> <?= e($c['presupuesto']) ?></span><?php endif; ?>
                        <span><?= icono('reloj') ?> Solicitada el <?= fechaCorta($c['fecha_solicitud']) ?></span>
                    </div>
                    <?php if ($c['servicios']): ?><p class="sub-dato" style="margin:8px 0 0">Servicios: <?= e($c['servicios']) ?></p><?php endif; ?>
                    <?php if ($c['estado'] === 'Respondida'): ?>
                        <div class="respuesta">
                            <span class="sub-dato">Valor estimado</span>
                            <div class="monto"><?= dinero($c['monto_estimado']) ?></div>
                            <p style="margin:6px 0 0"><?= nl2br(e($c['respuesta'])) ?></p>
                        </div>
                    <?php else: ?>
                        <p class="sub-dato" style="margin:12px 0 0">Estamos preparando tu cotización. Te responderemos pronto.</p>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
            <?php if (!$cotizaciones): ?>
                <div class="caja vacio">No has solicitado cotizaciones todavía.</div>
            <?php endif; ?>
        </div>

        <?php else: ?>
        <!-- ================= MI PERFIL ================= -->
        <div class="cabecera"><h2><?= icono('usuario') ?> Mi perfil</h2></div>
        <div class="perfil-grid">
            <form method="POST" class="caja">
                <div class="avatar"><?= e(mb_strtoupper(mb_substr($cliente['nombres'], 0, 1))) ?></div>
                <h3>Mis datos</h3>
                <input type="hidden" name="accion" value="perfil">
                <label class="etiqueta" for="p-nombres">Nombres</label>
                <div class="campo"><input id="p-nombres" name="nombres" value="<?= e($cliente['nombres']) ?>" required></div>
                <label class="etiqueta" for="p-apellidos">Apellidos</label>
                <div class="campo"><input id="p-apellidos" name="apellidos" value="<?= e($cliente['apellidos']) ?>"></div>
                <label class="etiqueta" for="p-tel">Teléfono</label>
                <div class="campo"><?= icono('telefono') ?><input id="p-tel" type="tel" name="telefono" value="<?= e($cliente['telefono']) ?>"></div>
                <label class="etiqueta">Correo</label>
                <div class="campo" style="background:var(--crema)"><?= icono('correo') ?><input value="<?= e($cliente['correo']) ?>" disabled></div>
                <button class="btn"><?= icono('check') ?> Guardar cambios</button>
            </form>

            <form method="POST" class="caja" oninput="nueva2.setCustomValidity(nueva2.value && nueva.value !== nueva2.value ? 'No coinciden' : '')">
                <h3>Cambiar contraseña</h3>
                <input type="hidden" name="accion" value="password">
                <label class="etiqueta" for="actual">Contraseña actual</label>
                <div class="campo"><?= icono('candado') ?><input id="actual" type="password" name="actual" autocomplete="current-password" required></div>
                <label class="etiqueta" for="nueva">Nueva contraseña</label>
                <div class="campo"><?= icono('candado') ?><input id="nueva" type="password" name="nueva" minlength="6" autocomplete="new-password" required></div>
                <label class="etiqueta" for="nueva2">Confirmar nueva contraseña</label>
                <div class="campo"><?= icono('candado') ?><input id="nueva2" type="password" name="confirmar" autocomplete="new-password" required></div>
                <button class="btn"><?= icono('candado') ?> Actualizar contraseña</button>
            </form>
        </div>
        <?php endif; ?>
    </main>
</div>

<dialog id="modal-reserva" class="modal">
    <div class="modal-cabeza"><h3>Detalle de la reserva</h3>
        <button type="button" onclick="this.closest('dialog').close()" aria-label="Cerrar"><?= icono('x') ?></button></div>
    <div class="modal-cuerpo"><dl id="detalle-reserva"></dl></div>
    <div class="modal-pie"><button type="button" class="btn" onclick="this.closest('dialog').close()">Cerrar</button></div>
</dialog>

<script>
function verReserva(boton) {
    const datos = JSON.parse(boton.dataset.reserva);
    const dl = document.getElementById('detalle-reserva');
    dl.innerHTML = '';
    for (const [k, v] of Object.entries(datos)) {
        const dt = document.createElement('dt'); dt.textContent = k;
        const dd = document.createElement('dd'); dd.textContent = v;
        dl.append(dt, dd);
    }
    document.getElementById('modal-reserva').showModal();
}
</script>

<?php require "partials/pie.php"; ?>
