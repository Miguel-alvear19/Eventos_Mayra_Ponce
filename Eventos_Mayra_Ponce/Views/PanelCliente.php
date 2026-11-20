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
    redirigir('mi-panel');
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
$confirmadas  = $reservaM->confirmadasPorCliente($idCliente);

$secciones = [
    'inicio'       => ['Mi panel',     'casa'],
    'reservas'     => ['Mis reservas', 'calendario'],
    'cotizaciones' => ['Cotizaciones', 'documento'],
    'perfil'       => ['Mi perfil',    'usuario'],
];
$seccion = $_GET['seccion'] ?? 'inicio';

// Seguimiento de una reserva (no aparece en el menú; se abre desde las listas)
$detalle = null;
if ($seccion === 'reserva') {
    $detalle = $reservaM->obtenerDeCliente((int)($_GET['id'] ?? 0), $idCliente);
    if (!$detalle) {
        flash('error', 'No encontramos esa reserva.');
        redirigir('mi-panel?seccion=reservas');
    }
} elseif (!isset($secciones[$seccion])) {
    $seccion = 'inicio';
}
$menuActivo = $seccion === 'reserva' ? 'reservas' : $seccion;

$whatsappBase = 'https://wa.me/' . NEGOCIO_WHATSAPP . '?text=';

/*
==========================
LÍNEA DE TIEMPO DE UNA RESERVA
Cada paso: [título, texto, estado]  estado = hecho | actual | pendiente | error | omitido
==========================
*/
function pasosReserva($r, $cotizacion) {
    $estado    = $r['estado'];
    $yaPaso    = $r['fecha_evento'] < date('Y-m-d');
    $recibida  = ['Solicitud recibida', 'La recibimos el ' . fechaCorta($r['fecha_solicitud']) . '.', 'hecho'];

    if ($estado === 'Cancelada') {
        return [$recibida,
            ['Solicitud cancelada', 'Esta reserva se canceló y la fecha quedó libre.', 'error'],
            ['Fecha confirmada', '', 'omitido'], ['Cotización', '', 'omitido'], ['Anticipo y comprobante', '', 'omitido']];
    }
    if ($estado === 'Rechazada') {
        return [$recibida,
            ['Revisión', 'Revisamos la disponibilidad del salón para ese día.', 'hecho'],
            ['Fecha no disponible', 'Ese día no podemos atender tu evento. Puedes elegir otra fecha.', 'error'],
            ['Cotización', '', 'omitido'], ['Anticipo y comprobante', '', 'omitido']];
    }
    if ($estado === 'Pendiente') {
        return [$recibida,
            ['En revisión', 'Estamos revisando la disponibilidad del salón y los detalles de tu evento.', 'actual'],
            ['Fecha confirmada', 'Te confirmamos si el día queda reservado para ti.', 'pendiente'],
            ['Cotización', 'Armamos contigo el paquete y el menú.', 'pendiente'],
            ['Anticipo y comprobante', 'Con el anticipo del 50 % tu fecha queda asegurada.', 'pendiente']];
    }

    // Aprobada
    $pasos = [$recibida,
        ['Revisión', 'Revisamos la disponibilidad y los detalles.', 'hecho'],
        ['Fecha confirmada', 'El salón reservó este día para tu evento.', 'hecho']];

    if ($yaPaso) {
        $pasos[] = ['Cotización', 'Paquete acordado.', 'hecho'];
        $pasos[] = ['Evento realizado', '¡Gracias por celebrar con nosotros!', 'hecho'];
    } elseif (!$cotizacion) {
        $pasos[] = ['Cotización', 'Siguiente paso: pide la cotización de tu paquete (desde 30 personas).', 'actual'];
        $pasos[] = ['Anticipo y comprobante', 'Con el anticipo del 50 % tu fecha queda asegurada y recibes tu comprobante.', 'pendiente'];
    } elseif ($cotizacion['estado'] === 'Pendiente') {
        $pasos[] = ['Cotización', 'Estamos preparando tu cotización.', 'actual'];
        $pasos[] = ['Anticipo y comprobante', 'Con el anticipo del 50 % tu fecha queda asegurada y recibes tu comprobante.', 'pendiente'];
    } else {
        $pasos[] = ['Cotización lista', 'Valor estimado: ' . dinero($cotizacion['monto_estimado']) . '.', 'hecho'];
        $pasos[] = ['Anticipo y comprobante', 'Haz el anticipo del 50 % para asegurar tu fecha. El evento se paga completo mínimo 8 días antes.', 'actual'];
    }
    return $pasos;
}

// Barra corta de progreso para las listas
function miniProgreso($r) {
    global $cotizacionM, $idCliente;
    $cot    = $r['estado'] === 'Aprobada' ? $cotizacionM->deReserva($idCliente, $r['id_reserva']) : null;
    $pasos  = pasosReserva($r, $cot);
    $hechos = count(array_filter($pasos, fn($p) => $p[2] === 'hecho'));
    $error  = in_array('error', array_column($pasos, 2), true);
    ob_start(); ?>
    <span class="mini-progreso <?= $error ? 'con-error' : '' ?>" aria-hidden="true">
        <?php foreach ($pasos as $p): ?><i class="<?= $p[2] ?>"></i><?php endforeach; ?>
    </span>
    <?php return ob_get_clean();
}

// Fila de la tabla de reservas (se usa en "Mi panel" y en "Mis reservas")
function filaReserva($r, $conCancelar) {
    $hoy = date('Y-m-d');
    $puedeCancelar = $conCancelar && in_array($r['estado'], ['Pendiente', 'Aprobada']) && $r['fecha_evento'] >= $hoy;
    ob_start(); ?>
    <tr>
        <td><?= fechaCorta($r['fecha_evento']) ?><span class="sub-dato"><?= horaBonita($r['hora_evento']) ?></span></td>
        <td><?= e($r['tipo_evento']) ?><span class="sub-dato"><?= (int)$r['invitados'] ?> invitados · <?= numeroReserva($r['id_reserva']) ?></span></td>
        <td><?= etiquetaEstado($r['estado'], true) ?><?= miniProgreso($r) ?></td>
        <td class="acciones-celda">
            <a class="btn btn-borde btn-sm" href="?seccion=reserva&amp;id=<?= (int)$r['id_reserva'] ?>">Seguimiento</a>
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

$titulo = $seccion === 'reserva' ? 'Seguimiento de tu reserva' : 'Mi panel';
$estilos = ['panel.css'];
$claseBody = 'panel';
$oscura = true;
require __DIR__ . "/partials/head.php";
require __DIR__ . "/partials/nav.php";
?>
<section class="banner">
    <h1>Hola, <?= e($cliente['nombres']) ?> <span aria-hidden="true">👋</span></h1>
    <p>Bienvenida(o) a tu panel de cliente</p>
</section>

<div class="layout">

    <nav class="menu" aria-label="Menú del panel">
        <?php foreach ($secciones as $clave => [$texto, $ico]): ?>
            <a href="?seccion=<?= $clave ?>" class="<?= $menuActivo === $clave ? 'activo' : '' ?>"><?= icono($ico) ?> <?= $texto ?></a>
        <?php endforeach; ?>
        <a href="<?= url('salir') ?>"><?= icono('salir') ?> Cerrar sesión</a>
        <div class="agradecimiento">Gracias por<br>confiar en nosotros <?= icono('corazon') ?><?= icono('flor', 'hojas') ?></div>
    </nav>

    <main class="contenido">
        <?php if ($seccion !== 'reserva') echo mostrarFlash(); ?>

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

        <?php if ($proximas): $p = $proximas[0]; $faltan = diasPara($p['fecha_evento']); ?>
            <a class="proximo" href="?seccion=reserva&amp;id=<?= (int)$p['id_reserva'] ?>">
                <div class="proximo-fecha">
                    <span class="dia"><?= date('d', strtotime($p['fecha_evento'])) ?></span>
                    <span class="mes"><?= MESES[date('n', strtotime($p['fecha_evento'])) - 1] ?></span>
                </div>
                <div class="proximo-info">
                    <span class="rotulo">Tu próximo evento · <?= numeroReserva($p['id_reserva']) ?></span>
                    <strong><?= e($p['tipo_evento']) ?></strong>
                    <span><?= horaBonita($p['hora_evento']) ?> · <?= (int)$p['invitados'] ?> invitados</span>
                    <?= miniProgreso($p) ?>
                </div>
                <div class="proximo-cuenta">
                    <b><?= $faltan ?></b><span><?= $faltan === 1 ? 'día' : 'días' ?></span>
                    <?= etiquetaEstado($p['estado'], true) ?>
                </div>
                <span class="proximo-ir"><?= icono('flecha') ?></span>
            </a>
        <?php endif; ?>

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
                        <a href="<?= url('reservar') ?>">¡Solicita la primera!</a></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="salon-card">
            <img src="<?= recurso('Imagenes/evento_flores_blancas.jpg') ?>" alt="Salón de eventos decorado">
            <div>
                <h2>Nuestro salón</h2>
                <p>Un espacio elegante y versátil, perfecto para todo tipo de eventos.</p>
                <a class="btn btn-borde" href="<?= url('inicio') ?>#salon">Conocer más <?= icono('flecha') ?></a>
            </div>
        </div>

        <?php elseif ($seccion === 'reserva'):
            $r          = $detalle;
            $cotizacion = $cotizacionM->deReserva($idCliente, $r['id_reserva']);
            $pasos      = pasosReserva($r, $cotizacion);
            $faltan     = diasPara($r['fecha_evento']);
            $esNueva    = isset($_GET['nueva']);
            $activa     = in_array($r['estado'], ['Pendiente', 'Aprobada']) && $faltan >= 0;
            $refTexto   = numeroReserva($r['id_reserva']) . ': ' . $r['tipo_evento'] . ' el ' . fechaCorta($r['fecha_evento']);
            $tematica   = '';
            $notas      = (string)$r['notas'];
            if (str_starts_with($notas, 'Temática: ')) {          // ver nota en ReservaController
                [$primera, $resto] = array_pad(explode("\n", $notas, 2), 2, '');
                $tematica = substr($primera, strlen('Temática: '));
                $notas    = $resto;
            }
        ?>
        <!-- ================= SEGUIMIENTO DE UNA RESERVA ================= -->
        <a class="enlace-flecha volver" href="?seccion=reservas"><?= icono('izquierda') ?> Mis reservas</a>
        <?= mostrarFlash() ?>

        <?php if ($esNueva): ?>
            <div class="recibida" role="status">
                <span class="sello-ok"><?= icono('check') ?></span>
                <div>
                    <h2>¡Recibimos tu solicitud!</h2>
                    <p>Tu número es <b><?= numeroReserva($r['id_reserva']) ?></b>. Ya la estamos revisando; aquí puedes ver cada paso.</p>
                </div>
            </div>
        <?php endif; ?>

        <section class="seg-cabeza">
            <div class="seg-fecha">
                <span class="dia"><?= date('d', strtotime($r['fecha_evento'])) ?></span>
                <span class="mes"><?= MESES[date('n', strtotime($r['fecha_evento'])) - 1] ?> <?= date('Y', strtotime($r['fecha_evento'])) ?></span>
            </div>
            <div class="seg-titulo">
                <span class="rotulo">Reserva <?= numeroReserva($r['id_reserva']) ?></span>
                <h2><?= e($r['tipo_evento']) ?><?= $tematica !== '' ? ' <em>· ' . e($tematica) . '</em>' : '' ?></h2>
                <span><?= horaBonita($r['hora_evento']) ?> · <?= (int)$r['invitados'] ?> invitados</span>
            </div>
            <div class="seg-estado">
                <?= etiquetaEstado($r['estado'], true) ?>
                <?php if ($activa): ?>
                    <span class="cuenta"><b><?= $faltan ?></b> <?= $faltan === 1 ? 'día' : 'días' ?> para tu evento</span>
                <?php endif; ?>
            </div>
        </section>

        <ol class="linea" aria-label="Estado de tu reserva">
            <?php foreach ($pasos as $i => [$tit, $txt, $est]): ?>
                <li class="<?= $est ?>" style="--i:<?= $i ?>">
                    <span class="nodo">
                        <?php if ($est === 'hecho'): ?><?= icono('check') ?>
                        <?php elseif ($est === 'error'): ?><?= icono('x') ?>
                        <?php else: ?><?= $i + 1 ?><?php endif; ?>
                    </span>
                    <div>
                        <strong><?= e($tit) ?></strong>
                        <?php if ($txt !== ''): ?><p><?= e($txt) ?></p><?php endif; ?>
                        <?php if ($est === 'actual'): ?><span class="ahora">Ahora</span><?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ol>

        <div class="seg-grid">
            <!-- Qué sigue -->
            <section class="caja que-sigue">
                <h3>¿Qué sigue?</h3>
                <?php if ($r['estado'] === 'Pendiente'): ?>
                    <p>Revisamos tu solicitud y te confirmamos si la fecha queda para ti. Si quieres agilizarlo o
                       conocer el salón antes, escríbenos.</p>
                    <div class="botones">
                        <a class="btn" target="_blank" rel="noopener" href="<?= $whatsappBase . rawurlencode('Hola, soy ' . $cliente['nombres'] . '. Quiero saber sobre mi solicitud ' . $refTexto . '.') ?>"><?= icono('whatsapp') ?> Preguntar por WhatsApp</a>
                        <a class="btn btn-borde" target="_blank" rel="noopener" href="<?= $whatsappBase . rawurlencode('Hola, soy ' . $cliente['nombres'] . '. Quisiera agendar una visita al salón para mi solicitud ' . $refTexto . '.') ?>"><?= icono('ubicacion') ?> Agendar visita</a>
                    </div>

                <?php elseif ($r['estado'] === 'Aprobada' && $faltan >= 0): ?>
                    <?php if (!$cotizacion): ?>
                        <p>¡Tu fecha está confirmada! Ahora armamos tu paquete. Pide la cotización y te respondemos con el valor;
                           después, con el anticipo del 50 %, la fecha queda asegurada.</p>
                        <div class="botones">
                            <a class="btn" href="<?= url('cotizar') ?>?reserva=<?= (int)$r['id_reserva'] ?>"><?= icono('documento') ?> Cotizar mi evento</a>
                            <button type="button" class="btn btn-borde" id="agregar-calendario"><?= icono('calendario') ?> Agregar a mi calendario</button>
                        </div>
                    <?php elseif ($cotizacion['estado'] === 'Pendiente'): ?>
                        <p>Recibimos tu solicitud de cotización el <?= fechaCorta($cotizacion['fecha_solicitud']) ?>. Te avisamos en
                           cuanto esté lista; también la verás en <a href="?seccion=cotizaciones">Cotizaciones</a>.</p>
                        <div class="botones">
                            <a class="btn btn-borde" target="_blank" rel="noopener" href="<?= $whatsappBase . rawurlencode('Hola, soy ' . $cliente['nombres'] . '. Quiero saber sobre la cotización de mi reserva ' . $refTexto . '.') ?>"><?= icono('whatsapp') ?> Preguntar por WhatsApp</a>
                            <button type="button" class="btn btn-borde" id="agregar-calendario"><?= icono('calendario') ?> Agregar a mi calendario</button>
                        </div>
                    <?php else: ?>
                        <div class="monto-cot"><span>Valor estimado</span><b><?= dinero($cotizacion['monto_estimado']) ?></b>
                            <span>Anticipo del 50 %: <?= dinero($cotizacion['monto_estimado'] / 2) ?></span></div>
                        <?php if ($cotizacion['respuesta']): ?><p><?= nl2br(e($cotizacion['respuesta'])) ?></p><?php endif; ?>
                        <p>Escríbenos para coordinar el anticipo. El evento debe quedar pagado por completo mínimo 8 días antes.</p>
                        <div class="botones">
                            <a class="btn" target="_blank" rel="noopener" href="<?= $whatsappBase . rawurlencode('Hola, soy ' . $cliente['nombres'] . '. Quiero hacer el anticipo de mi reserva ' . $refTexto . '.') ?>"><?= icono('whatsapp') ?> Coordinar anticipo</a>
                            <button type="button" class="btn btn-borde" id="agregar-calendario"><?= icono('calendario') ?> Agregar a mi calendario</button>
                        </div>
                    <?php endif; ?>

                <?php elseif ($r['estado'] === 'Rechazada'): ?>
                    <p>Lo sentimos, ese día no podemos atender tu evento. Elige otra fecha y te ayudamos con gusto.</p>
                    <div class="botones">
                        <a class="btn" href="<?= url('reservar') ?>?tipo=<?= urlencode($r['tipo_evento']) ?>"><?= icono('calendario') ?> Elegir otra fecha</a>
                        <a class="btn btn-borde" target="_blank" rel="noopener" href="<?= $whatsappBase . rawurlencode('Hola, soy ' . $cliente['nombres'] . '. Mi solicitud ' . $refTexto . ' no quedó disponible. ¿Qué fechas cercanas tienen?') ?>"><?= icono('whatsapp') ?> Preguntar fechas</a>
                    </div>

                <?php elseif ($r['estado'] === 'Cancelada'): ?>
                    <p>Esta reserva fue cancelada. Si fue por un caso fortuito o de fuerza mayor y ya habías pagado,
                       escríbenos para mover la fecha o cambiar el tipo de evento.</p>
                    <div class="botones">
                        <a class="btn" href="<?= url('reservar') ?>?tipo=<?= urlencode($r['tipo_evento']) ?>"><?= icono('mas') ?> Nueva solicitud</a>
                    </div>

                <?php else: ?>
                    <p>¡Gracias por celebrar con nosotros! Si quieres repetir la experiencia, aquí estamos.</p>
                    <div class="botones"><a class="btn" href="<?= url('reservar') ?>"><?= icono('mas') ?> Nueva reserva</a></div>
                <?php endif; ?>
            </section>

            <!-- Detalles -->
            <section class="caja seg-detalles">
                <h3>Detalles</h3>
                <dl>
                    <dt>Fecha</dt><dd><?= fechaCorta($r['fecha_evento']) ?>, <?= horaBonita($r['hora_evento']) ?></dd>
                    <dt>Evento</dt><dd><?= e($r['tipo_evento']) ?> · <?= (int)$r['invitados'] ?> invitados</dd>
                    <dt>Temática</dt><dd><?= $tematica !== '' ? e($tematica) : 'Por definir' ?></dd>
                    <dt>Servicios</dt><dd><?= $r['servicios'] ? e($r['servicios']) : 'Ninguno seleccionado' ?></dd>
                    <?php if (trim($notas) !== ''): ?><dt>Notas</dt><dd><?= nl2br(e($notas)) ?></dd><?php endif; ?>
                    <dt>Contacto</dt><dd><?= e($r['nombre_contacto']) ?><br><?= e($r['correo_contacto']) ?><?= $cliente['telefono'] ? ' · ' . e($cliente['telefono']) : '' ?></dd>
                </dl>
                <?php if ($activa): ?>
                    <form method="POST" class="cancelar-linea" onsubmit="return confirm('¿Seguro que quieres cancelar esta reserva?')">
                        <input type="hidden" name="accion" value="cancelar">
                        <input type="hidden" name="volver" value="detalle">
                        <input type="hidden" name="id_reserva" value="<?= (int)$r['id_reserva'] ?>">
                        <button class="enlace-cancelar">Cancelar esta reserva</button>
                    </form>
                <?php endif; ?>
            </section>
        </div>

        <?php if ($r['estado'] === 'Aprobada'): ?>
        <script>
        // Descarga un archivo .ics para guardar el evento en el calendario del celular o del correo
        document.getElementById('agregar-calendario')?.addEventListener('click', () => {
            const inicio = <?= json_encode(date('Ymd\THis', strtotime($r['fecha_evento'] . ' ' . $r['hora_evento']))) ?>;
            const fin    = <?= json_encode(date('Ymd\THis', strtotime($r['fecha_evento'] . ' ' . $r['hora_evento'] . ' +5 hours'))) ?>;
            const ics = ['BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//Eventos Mayra Ponce//ES', 'BEGIN:VEVENT',
                'UID:reserva-<?= (int)$r['id_reserva'] ?>@eventosmayraponce',
                'DTSTART:' + inicio, 'DTEND:' + fin,
                'SUMMARY:' + <?= json_encode($r['tipo_evento'] . ' · Eventos Mayra Ponce', JSON_UNESCAPED_UNICODE) ?>,
                'LOCATION:' + <?= json_encode(str_replace(',', '\,', NEGOCIO_DIRECCION), JSON_UNESCAPED_UNICODE) ?>,
                'END:VEVENT', 'END:VCALENDAR'].join('\r\n');
            const a = document.createElement('a');
            a.href = URL.createObjectURL(new Blob([ics], { type: 'text/calendar' }));
            a.download = 'mi-evento-mayra-ponce.ics';
            a.click();
            URL.revokeObjectURL(a.href);
        });
        </script>
        <?php endif; ?>

        <?php elseif ($seccion === 'reservas'): ?>
        <!-- ================= MIS RESERVAS ================= -->
        <div class="cabecera">
            <h2><?= icono('calendario') ?> Mis reservas</h2>
            <a class="btn" href="<?= url('reservar') ?>"><?= icono('mas') ?> Nueva reserva</a>
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
        </div>

        <?php if ($confirmadas): ?>
            <div class="cotizar-desde">
                <p><b>Cotiza un evento confirmado.</b> Usamos los datos de tu reserva, no tienes que escribir nada de nuevo.</p>
                <?php foreach ($confirmadas as $c):
                    $yaPidio = $cotizacionM->deReserva($idCliente, $c['id_reserva']); ?>
                    <div class="cotizar-item">
                        <span><b><?= e($c['tipo_evento']) ?></b> · <?= fechaCorta($c['fecha_evento']) ?> · <?= (int)$c['invitados'] ?> invitados
                              <span class="sub-dato"><?= numeroReserva($c['id_reserva']) ?></span></span>
                        <?php if ($yaPidio): ?>
                            <?= etiquetaEstado($yaPidio['estado']) ?>
                        <?php else: ?>
                            <a class="btn btn-sm" href="<?= url('cotizar') ?>?reserva=<?= (int)$c['id_reserva'] ?>">Cotizar este evento</a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="cotizar-desde vacio-guiado">
                <p><b>Primero reservamos tu fecha.</b> Cuando el salón confirme tu reserva, desde aquí pides la cotización
                   de tu paquete con un clic.</p>
                <a class="btn btn-sm" href="<?= url('reservar') ?>"><?= icono('calendario') ?> Reservar mi fecha</a>
            </div>
        <?php endif; ?>

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
                <p class="sub-dato" style="margin:-8px 0 14px">Los usamos en tus reservas y cotizaciones; no te los volvemos a pedir.</p>
                <input type="hidden" name="accion" value="perfil">
                <label class="etiqueta" for="p-nombres">Nombres</label>
                <div class="campo"><input id="p-nombres" name="nombres" value="<?= e($cliente['nombres']) ?>" required></div>
                <label class="etiqueta" for="p-apellidos">Apellidos</label>
                <div class="campo"><input id="p-apellidos" name="apellidos" value="<?= e($cliente['apellidos']) ?>"></div>
                <label class="etiqueta" for="p-tel">Teléfono o WhatsApp</label>
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

<?php require __DIR__ . "/partials/pie.php"; ?>
