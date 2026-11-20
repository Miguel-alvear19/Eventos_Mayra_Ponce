<?php
require_once dirname(__DIR__) . "/Config/helpers.php";
require_once dirname(__DIR__) . "/controllers/AdminController.php";

requiereAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    (new AdminController())->procesar();   // siempre redirige
}

$reservaM    = new Reserva();
$cotizacionM = new Cotizacion();
$clienteM    = new Cliente();
$bloqueoM    = new FechaBloqueada();

$porEstado          = $reservaM->contarPorEstado();
$cotizacionesPend   = $cotizacionM->contarPendientes();

// Pendientes agrupadas por fecha: si un día tiene 2 o más (o ya está ocupado) es un conflicto
$pendientesFut   = $reservaM->pendientesFuturas();
$porFecha        = [];
foreach ($pendientesFut as $r) $porFecha[$r['fecha_evento']][] = $r;
$diasConflicto   = array_filter($porFecha, fn($g) => count($g) > 1 || (int)$g[0]['dia_ocupado'] > 0);
$pendientesSolas = array_merge([], ...array_values(array_diff_key($porFecha, $diasConflicto)));

$secciones = [
    'inicio'        => ['Resumen',       'casa',       0],
    'decisiones'    => ['Decisiones',    'check',      count($pendientesFut)],
    'calendario'    => ['Calendario',    'calendario', 0],
    'reservas'      => ['Reservas',      'lista',      0],
    'cotizaciones'  => ['Cotizaciones',  'documento',  $cotizacionesPend],
    'clientes'      => ['Clientes',      'usuarios',   0],
    'reportes'      => ['Reportes',      'grafica',    0],
    'configuracion' => ['Configuración', 'ajustes',    0],
];
$seccion = isset($secciones[$_GET['seccion'] ?? '']) ? $_GET['seccion'] : 'inicio';
$query   = $_SERVER['QUERY_STRING'] ?? '';
$volver  = 'admin' . ($query !== '' ? '?' . $query : '');
$hoy     = date('Y-m-d');

// Datos de una reserva para el modal "Ver detalles"
function datosReserva($r) {
    return e(json_encode([
        'id'     => $r['id_reserva'],
        'estado' => $r['estado'],
        'filas'  => [
            'Cliente'    => $r['nombre_contacto'],
            'Correo'     => $r['correo_contacto'],
            'Teléfono'   => $r['telefono'] ?? '—',
            'Evento'     => $r['tipo_evento'] . ' · ' . $r['invitados'] . ' invitados',
            'Fecha'      => fechaCorta($r['fecha_evento']) . ', ' . horaBonita($r['hora_evento']),
            'Servicios'  => $r['servicios'] ?: 'Ninguno',
            'Notas'      => $r['notas'] ?: '—',
            'Solicitada' => fechaCorta($r['fecha_solicitud']),
        ],
    ]));
}

// Botones Aprobar / Rechazar de una fila
function botonesEstado($r, $volver) {
    if ($r['estado'] !== 'Pendiente') return '';
    ob_start(); ?>
    <form method="POST" style="display:contents">
        <input type="hidden" name="id_reserva" value="<?= (int)$r['id_reserva'] ?>">
        <input type="hidden" name="volver" value="<?= e($volver) ?>">
        <button class="btn btn-sm" name="accion" value="aprobar" title="Aprobar"><?= icono('check') ?></button>
        <button class="btn btn-borde btn-sm" name="accion" value="rechazar" title="Rechazar"
                onclick="return confirm('¿Rechazar esta solicitud?')"><?= icono('x') ?></button>
    </form>
    <?php return ob_get_clean();
}

$titulo = 'Panel administrativo';
$estilos = ['panel.css'];
$claseBody = 'panel';
$oscura = true;
require __DIR__ . "/partials/head.php";
require __DIR__ . "/partials/nav.php";
?>
<div class="layout">

    <nav class="menu" aria-label="Menú de administración">
        <?php foreach ($secciones as $clave => [$texto, $ico, $contador]): ?>
            <a href="?seccion=<?= $clave ?>" class="<?= $seccion === $clave ? 'activo' : '' ?>">
                <?= icono($ico) ?> <?= $texto ?>
                <?php if ($contador): ?><span class="contador" title="Pendientes"><?= $contador ?></span><?php endif; ?>
            </a>
        <?php endforeach; ?>
        <a href="<?= url('salir') ?>"><?= icono('salir') ?> Cerrar sesión</a>
        <div class="agradecimiento">Hola, <?= e($_SESSION['nombre_admin']) ?><?= icono('flor', 'hojas') ?></div>
    </nav>

    <main class="contenido">
        <?= mostrarFlash() ?>

<?php
/* =====================================================
   RESUMEN (lo primero que ve el administrador)
   ===================================================== */
if ($seccion === 'inicio'):
    $ind        = $reservaM->indicadores();
    $decididas  = (int)$ind['aprobadas'] + (int)$ind['rechazadas'];
    $tasa       = $decididas ? round($ind['aprobadas'] / $decididas * 100) : null;
    $proximos   = $reservaM->proximosConfirmados(5);
    $actividad  = $reservaM->actividadSeisMeses();
    $maxAct     = max(1, ...array_values(array_map(fn($m) => $m["recibidas"], $actividad)));
    $porTipo    = $reservaM->contarPorTipo();
    $totalTipos = array_sum(array_column($porTipo, 'n')) ?: 1;
?>
        <div class="cabecera">
            <h2><?= icono('casa') ?> Resumen</h2>
            <span class="sub-dato">Actualizado <?= fechaCorta($hoy) ?>, <?= date('g:i a') ?></span>
        </div>

        <?php if ($diasConflicto): ?>
            <a class="aviso-conflicto" href="?seccion=decisiones">
                <span class="icono-aviso">!</span>
                <span><b><?= count($diasConflicto) ?> <?= count($diasConflicto) === 1 ? 'fecha tiene' : 'fechas tienen' ?> varias solicitudes a la vez.</b>
                      Decide cuál se queda con el día.</span>
                <span class="ir">Ir a decisiones <?= icono('flecha') ?></span>
            </a>
        <?php endif; ?>

        <div class="kpis">
            <a class="kpi destacado" href="?seccion=decisiones">
                <span class="kpi-nombre">Por revisar</span>
                <b><?= (int)$ind['por_revisar'] ?></b>
                <span class="kpi-nota">solicitudes pendientes</span>
            </a>
            <div class="kpi">
                <span class="kpi-nombre">Confirmados este mes</span>
                <b><?= (int)$ind['confirmados_mes'] ?></b>
                <span class="kpi-nota"><?= (int)$ind['confirmados_futuros'] ?> eventos próximos en total</span>
            </div>
            <div class="kpi">
                <span class="kpi-nombre">Tasa de aprobación</span>
                <b><?= $tasa === null ? '—' : $tasa . '%' ?></b>
                <span class="kpi-nota"><?= (int)$ind['aprobadas'] ?> aprobadas de <?= $decididas ?> decididas</span>
            </div>
            <div class="kpi">
                <span class="kpi-nombre">Solicitudes · 30 días</span>
                <b><?= (int)$ind['solicitudes_30'] ?></b>
                <span class="kpi-nota"><?= (int)$ind['realizados'] ?> eventos realizados en total</span>
            </div>
        </div>

        <div class="resumen-grid">
            <section class="caja">
                <div class="caja-cabeza">
                    <h3>Solicitudes y confirmaciones</h3>
                    <span class="leyenda-mini"><i class="s1"></i> Recibidas <i class="s2"></i> Confirmadas</span>
                </div>
                <div class="actividad" role="img" aria-label="Solicitudes recibidas y confirmadas en los últimos seis meses">
                    <?php foreach ($actividad as $ym => $m): [$a, $mm] = explode('-', $ym); ?>
                        <div class="act-mes" title="<?= MESES[(int)$mm - 1] ?>: <?= $m['recibidas'] ?> recibidas, <?= $m['confirmadas'] ?> confirmadas">
                            <div class="act-barras">
                                <span class="s1" style="--h:<?= round($m['recibidas'] / $maxAct * 100) ?>%"><em><?= $m['recibidas'] ?: '' ?></em></span>
                                <span class="s2" style="--h:<?= round($m['confirmadas'] / $maxAct * 100) ?>%"><em><?= $m['confirmadas'] ?: '' ?></em></span>
                            </div>
                            <small><?= substr(MESES[(int)$mm - 1], 0, 3) ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="caja">
                <div class="caja-cabeza"><h3>Tipos de evento</h3></div>
                <div class="tipos-lista">
                    <?php foreach (array_slice($porTipo, 0, 6) as $t): $pct = round($t['n'] / $totalTipos * 100); ?>
                        <div class="tipo-fila">
                            <span><?= e($t['tipo_evento']) ?></span>
                            <div class="pista"><div class="relleno" style="width:<?= $pct ?>%"></div></div>
                            <b><?= (int)$t['n'] ?></b>
                        </div>
                    <?php endforeach; ?>
                    <?php if (!$porTipo): ?><p class="vacio">Sin datos todavía.</p><?php endif; ?>
                </div>
            </section>
        </div>

        <div class="cabecera" style="margin-top:8px">
            <h2 style="font-size:22px"><?= icono('calendario') ?> Próximos eventos confirmados</h2>
            <a class="enlace-flecha" href="?seccion=calendario">Ver calendario <?= icono('flecha') ?></a>
        </div>
        <div class="agenda">
            <?php foreach ($proximos as $p): $faltan = diasPara($p['fecha_evento']); ?>
                <div class="agenda-item">
                    <div class="agenda-fecha"><b><?= date('d', strtotime($p['fecha_evento'])) ?></b><span><?= substr(MESES[date('n', strtotime($p['fecha_evento'])) - 1], 0, 3) ?></span></div>
                    <div class="agenda-info">
                        <strong><?= e($p['tipo_evento']) ?> · <?= e($p['nombre_contacto']) ?></strong>
                        <span><?= horaBonita($p['hora_evento']) ?> · <?= (int)$p['invitados'] ?> invitados · <?= numeroReserva($p['id_reserva']) ?></span>
                    </div>
                    <span class="agenda-cuenta <?= $faltan <= 8 ? 'pronto' : '' ?>"><?= $faltan === 0 ? 'Hoy' : ($faltan === 1 ? 'Mañana' : 'En ' . $faltan . ' días') ?></span>
                    <?php if ($p['telefono']): ?>
                        <a class="agenda-wa" target="_blank" rel="noopener" title="Escribir por WhatsApp"
                           href="https://wa.me/57<?= preg_replace('/\D/', '', $p['telefono']) ?>"><?= icono('whatsapp') ?></a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <?php if (!$proximos): ?><div class="caja vacio">No hay eventos confirmados próximamente.</div><?php endif; ?>
        </div>

<?php
/* =====================================================
   BANDEJA DE DECISIONES
   Fechas con varias solicitudes: se comparan lado a lado y al aprobar
   una, las demás de ese día se rechazan solas (AdminController).
   ===================================================== */
elseif ($seccion === 'decisiones'):
?>
        <div class="cabecera">
            <h2><?= icono('check') ?> Bandeja de decisiones</h2>
            <span class="sub-dato"><?= count($pendientesFut) ?> <?= count($pendientesFut) === 1 ? 'solicitud pendiente' : 'solicitudes pendientes' ?></span>
        </div>

        <p class="regla-salon"><?= icono('dinero') ?>
            <span><b>Regla del salón:</b> la fecha es para el primer cliente que hace el anticipo del 50 %.
            Si dos piden el mismo día, confirma por WhatsApp quién pagó primero y aprueba esa solicitud.</span></p>

        <?php foreach ($diasConflicto as $fecha => $grupo):
            $ocupado = (int)$grupo[0]['dia_ocupado'] > 0;
            $primera = min(array_column($grupo, 'fecha_solicitud')); ?>
            <section class="conflicto <?= $ocupado ? 'ocupado' : '' ?>">
                <header class="conflicto-cabeza">
                    <div class="conflicto-fecha"><b><?= date('d', strtotime($fecha)) ?></b><span><?= substr(MESES[date('n', strtotime($fecha)) - 1], 0, 3) ?> <?= date('Y', strtotime($fecha)) ?></span></div>
                    <div>
                        <h3><?= $ocupado ? 'Fecha ya confirmada para otro evento' : count($grupo) . ' solicitudes para el mismo día' ?></h3>
                        <p><?= $ocupado
                            ? 'Estas solicitudes llegaron para un día que ya está ocupado. Recházalas para avisar a los clientes.'
                            : (count($grupo) === 2
                                ? 'Al darle la fecha a una, la otra se rechaza automáticamente y el cliente lo ve en su panel.'
                                : 'Al darle la fecha a una, las otras ' . (count($grupo) - 1) . ' se rechazan automáticamente y los clientes lo ven en su panel.') ?></p>
                    </div>
                    <span class="faltan">En <?= diasPara($fecha) ?> días</span>
                </header>
                <div class="candidatos">
                    <?php foreach ($grupo as $c): ?>
                        <article class="candidato">
                            <div class="cand-top">
                                <span class="avatar-mini"><?= e(mb_strtoupper(mb_substr($c['nombre_contacto'], 0, 1))) ?></span>
                                <div>
                                    <strong><?= e($c['nombre_contacto']) ?></strong>
                                    <span class="sub-dato"><?= numeroReserva($c['id_reserva']) ?> · solicitada <?= fechaCorta($c['fecha_solicitud']) ?>, <?= date('g:i a', strtotime($c['fecha_solicitud'])) ?></span>
                                </div>
                                <?php if ($c['fecha_solicitud'] === $primera && !$ocupado): ?><span class="sello-primera">1.ª en pedir</span><?php endif; ?>
                            </div>
                            <dl>
                                <dt>Evento</dt><dd><?= e($c['tipo_evento']) ?> · <?= (int)$c['invitados'] ?> invitados</dd>
                                <dt>Hora</dt><dd><?= horaBonita($c['hora_evento']) ?></dd>
                                <dt>Servicios</dt><dd><?= $c['servicios'] ? e($c['servicios']) : '—' ?></dd>
                                <?php if ($c['notas']): ?><dt>Notas</dt><dd><?= nl2br(e($c['notas'])) ?></dd><?php endif; ?>
                                <dt>Cliente</dt><dd><?= (int)$c['reservas_cliente'] ?> <?= (int)$c['reservas_cliente'] === 1 ? 'solicitud' : 'solicitudes' ?>
                                    · <?= (int)$c['aprobadas_cliente'] ?> <?= (int)$c['aprobadas_cliente'] === 1 ? 'confirmada' : 'confirmadas' ?></dd>
                            </dl>
                            <div class="cand-acciones">
                                <?php if ($c['telefono']): ?>
                                    <a class="btn btn-borde btn-sm" target="_blank" rel="noopener"
                                       href="https://wa.me/57<?= preg_replace('/\D/', '', $c['telefono']) ?>?text=<?= rawurlencode('Hola ' . $c['nombre_contacto'] . ', te escribimos de Eventos Mayra Ponce por tu solicitud ' . numeroReserva($c['id_reserva']) . ' para el ' . fechaCorta($fecha) . '. ¿Ya realizaste el anticipo del 50 %?') ?>">
                                        <?= icono('whatsapp') ?> ¿Pagó anticipo?</a>
                                <?php endif; ?>
                                <form method="POST">
                                    <input type="hidden" name="id_reserva" value="<?= (int)$c['id_reserva'] ?>">
                                    <input type="hidden" name="volver" value="admin?seccion=decisiones">
                                    <?php if ($ocupado): ?>
                                        <button class="btn btn-oscuro btn-sm" name="accion" value="rechazar"><?= icono('x') ?> Rechazar</button>
                                    <?php else: ?>
                                        <button class="btn btn-sm" name="accion" value="aprobar"
                                                onclick="return confirm('Se confirmará la solicitud de <?= e(addslashes($c['nombre_contacto'])) ?> y se <?= count($grupo) === 2 ? 'rechazará la otra solicitud' : 'rechazarán las otras ' . (count($grupo) - 1) ?> del <?= fechaNumerica($fecha) ?>. ¿Continuar?')">
                                            <?= icono('check') ?> Darle la fecha</button>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>

        <div class="cabecera" style="margin-top:<?= $diasConflicto ? '30px' : '0' ?>">
            <h2 style="font-size:22px">Solicitudes sin conflicto</h2>
        </div>
        <div class="tabla-caja">
            <table>
                <thead><tr><th>Fecha</th><th>Cliente</th><th>Evento</th><th>Solicitada</th><th>Acciones</th></tr></thead>
                <tbody>
                <?php foreach ($pendientesSolas as $r): ?>
                    <tr>
                        <td><?= fechaCorta($r['fecha_evento']) ?><span class="sub-dato"><?= horaBonita($r['hora_evento']) ?> · en <?= diasPara($r['fecha_evento']) ?> días</span></td>
                        <td><?= e($r['nombre_contacto']) ?><span class="sub-dato"><?= e($r['telefono'] ?: $r['correo_contacto']) ?></span></td>
                        <td><?= e($r['tipo_evento']) ?><span class="sub-dato"><?= (int)$r['invitados'] ?> invitados · <?= numeroReserva($r['id_reserva']) ?></span></td>
                        <td><?= fechaCorta($r['fecha_solicitud']) ?></td>
                        <td class="acciones-celda">
                            <button type="button" class="btn btn-oscuro btn-sm" onclick="verDetalles(this)" data-reserva="<?= datosReserva($r) ?>">Ver</button>
                            <?= botonesEstado($r, 'admin?seccion=decisiones') ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$pendientesSolas): ?><tr><td colspan="5" class="vacio">No hay más solicitudes por revisar. ¡Todo al día!</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>

<?php
/* =====================================================
   CALENDARIO DE DISPONIBILIDAD
   ===================================================== */
elseif ($seccion === 'calendario'):
    $mesParam = $_GET['mes'] ?? date('Y-m');
    if (!preg_match('/^\d{4}-\d{2}$/', $mesParam)) $mesParam = date('Y-m');
    [$anio, $mes] = array_map('intval', explode('-', $mesParam));
    $primero      = mktime(0, 0, 0, $mes, 1, $anio);
    $diasMes      = (int)date('t', $primero);
    $inicioSemana = (int)date('w', $primero);           // 0 = domingo
    $anterior     = date('Y-m', strtotime('-1 month', $primero));
    $siguiente    = date('Y-m', strtotime('+1 month', $primero));

    $estadoDias = $reservaM->estadoPorDia($anio, $mes);
    $bloqueados = $bloqueoM->delMes($anio, $mes);

    $diaElegido = $_GET['dia'] ?? (date('Y-m') === $mesParam ? $hoy : '');
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $diaElegido)) $diaElegido = '';
    $recientes = $reservaM->recientes(6);
?>
        <div class="cabecera">
            <h2><?= icono('calendario') ?> Calendario de Disponibilidad</h2>
            <div class="cal-nav">
                <a href="?seccion=calendario&mes=<?= $anterior ?>" aria-label="Mes anterior"><?= icono('izquierda') ?></a>
                <form method="GET">
                    <input type="hidden" name="seccion" value="calendario">
                    <select name="mes" onchange="this.form.submit()" aria-label="Elegir mes">
                        <?php for ($i = -3; $i <= 12; $i++):
                            $m = date('Y-m', strtotime("$i month", mktime(0, 0, 0, (int)date('n'), 1))); ?>
                            <option value="<?= $m ?>" <?= $m === $mesParam ? 'selected' : '' ?>>
                                <?= MESES[(int)substr($m, 5) - 1] . ' ' . substr($m, 0, 4) ?></option>
                        <?php endfor; ?>
                        <?php if ($mesParam < date('Y-m', strtotime('-3 month')) || $mesParam > date('Y-m', strtotime('+12 month'))): ?>
                            <option selected><?= MESES[$mes - 1] . " $anio" ?></option>
                        <?php endif; ?>
                    </select>
                </form>
                <a href="?seccion=calendario&mes=<?= $siguiente ?>" aria-label="Mes siguiente"><?= icono('derecha') ?></a>
            </div>
        </div>

        <div class="calendario-caja">
            <div class="calendario">
                <?php foreach (['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'] as $d): ?>
                    <div class="dia-semana"><?= $d ?></div>
                <?php endforeach; ?>

                <?php // Días del mes anterior para completar la primera semana
                $diasAnterior = (int)date('t', strtotime('-1 month', $primero));
                for ($i = $inicioSemana - 1; $i >= 0; $i--): ?>
                    <div class="dia fuera"><?= $diasAnterior - $i ?></div>
                <?php endfor; ?>

                <?php for ($d = 1; $d <= $diasMes; $d++):
                    $fecha = sprintf('%04d-%02d-%02d', $anio, $mes, $d);
                    $info  = $estadoDias[$fecha] ?? null;
                    if (isset($bloqueados[$fecha]))        { $clase = 'mantenimiento'; $txt = $bloqueados[$fecha]; }
                    elseif ($info && $info['aprobadas'])   { $clase = 'reservado';     $txt = 'Reservado'; }
                    else                                   { $clase = 'disponible';    $txt = 'Disponible'; }
                    if ($fecha < $hoy)          $clase .= ' pasado';
                    if ($fecha === $hoy)        $clase .= ' hoy';
                    if ($fecha === $diaElegido) $clase .= ' elegido';
                    $pend = $info ? (int)$info['pendientes'] : 0;
                ?>
                    <a class="dia <?= $clase ?>" href="?seccion=calendario&mes=<?= $mesParam ?>&dia=<?= $fecha ?>"
                       title="<?= fechaNumerica($fecha) ?>: <?= e($txt) ?><?= $pend ? " · $pend pendiente(s)" : '' ?>">
                        <?= $d ?><?php if ($pend): ?><span class="punto"></span><?php endif; ?>
                    </a>
                <?php endfor; ?>

                <?php $resto = (7 - ($inicioSemana + $diasMes) % 7) % 7;
                for ($d = 1; $d <= $resto; $d++): ?>
                    <div class="dia fuera"><?= $d ?></div>
                <?php endfor; ?>
            </div>
        </div>
        <div class="leyenda">
            <span><i style="background:var(--verde)"></i> Disponible</span>
            <span><i style="background:var(--rojo)"></i> Reservado</span>
            <span><i style="background:var(--amarillo)"></i> Mantenimiento</span>
            <span><i class="punto-l"></i> Con solicitudes pendientes</span>
        </div>

        <?php if ($diaElegido):
            $delDia = $reservaM->delDia($diaElegido);
            $bloqueado = $bloqueoM->estaBloqueada($diaElegido); ?>
        <div class="detalle-dia">
            <h3><?= fechaCorta($diaElegido) ?></h3>
            <?php if ($delDia): ?>
                <ul>
                    <?php foreach ($delDia as $r): ?>
                        <li><?= e($r['tipo_evento']) ?> de <b><?= e($r['nombre_contacto']) ?></b>,
                            <?= horaBonita($r['hora_evento']) ?> · <?= (int)$r['invitados'] ?> invitados — <?= etiquetaEstado($r['estado']) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p style="margin:0 0 12px">No hay eventos ni solicitudes para este día.</p>
            <?php endif; ?>

            <?php if ($diaElegido >= $hoy): ?>
                <form method="POST" class="form-linea">
                    <input type="hidden" name="fecha" value="<?= e($diaElegido) ?>">
                    <input type="hidden" name="volver" value="<?= e($volver) ?>">
                    <?php if ($bloqueado): ?>
                        <span>Este día está bloqueado (<?= e($bloqueados[$diaElegido] ?? 'Mantenimiento') ?>).</span>
                        <button class="btn btn-sm" name="accion" value="desbloquear"><?= icono('check') ?> Marcar disponible</button>
                    <?php else: ?>
                        <input name="motivo" placeholder="Motivo (ej. Mantenimiento)" maxlength="120">
                        <button class="btn btn-borde btn-sm" name="accion" value="bloquear"><?= icono('ajustes') ?> Bloquear día</button>
                    <?php endif; ?>
                </form>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="cabecera">
            <h2 style="font-size:22px">Solicitudes de Reserva</h2>
            <a class="enlace-flecha" href="?seccion=reservas">Ver todas <?= icono('flecha') ?></a>
        </div>
        <form method="POST" id="form-solicitudes">
            <input type="hidden" name="volver" value="<?= e($volver) ?>">
            <div class="tabla-caja">
                <table>
                    <thead><tr><th style="width:40px"></th><th>Cliente</th><th>Fecha</th><th>Evento</th><th>Estado</th><th>Acciones</th></tr></thead>
                    <tbody>
                    <?php foreach ($recientes as $r): ?>
                        <tr>
                            <td><?php if ($r['estado'] === 'Pendiente'): ?>
                                <input type="radio" name="id_reserva" value="<?= (int)$r['id_reserva'] ?>" aria-label="Seleccionar solicitud de <?= e($r['nombre_contacto']) ?>"
                                       onchange="document.querySelectorAll('#form-solicitudes tr').forEach(f=>f.classList.remove('seleccionada'));this.closest('tr').classList.add('seleccionada')">
                            <?php endif; ?></td>
                            <td><?= e($r['nombre_contacto']) ?></td>
                            <td><?= fechaNumerica($r['fecha_evento']) ?></td>
                            <td><?= e($r['tipo_evento']) ?></td>
                            <td><?= etiquetaEstado($r['estado']) ?></td>
                            <td><button type="button" class="btn btn-oscuro btn-sm btn-cuadrado" onclick="verDetalles(this)" data-reserva="<?= datosReserva($r) ?>">Ver detalles</button></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$recientes): ?><tr><td colspan="6" class="vacio">Todavía no hay solicitudes de reserva.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="botones-centro">
                <button class="btn btn-cuadrado" name="accion" value="aprobar"><?= icono('check') ?> Aprobar</button>
                <button class="btn btn-borde btn-cuadrado" name="accion" value="rechazar"
                        onclick="return confirm('¿Rechazar la solicitud seleccionada?')"><?= icono('x') ?> Rechazar</button>
            </div>
        </form>

<?php
/* =====================================================
   RESERVAS (todas, con filtro por estado)
   ===================================================== */
elseif ($seccion === 'reservas'):
    $filtro = in_array($_GET['estado'] ?? '', ['Pendiente','Aprobada','Rechazada','Cancelada']) ? $_GET['estado'] : '';
    $lista  = $reservaM->listar($filtro);
?>
        <div class="cabecera"><h2><?= icono('lista') ?> Reservas</h2></div>
        <div class="filtros">
            <a href="?seccion=reservas" class="<?= $filtro === '' ? 'activo' : '' ?>">Todas (<?= array_sum($porEstado) ?>)</a>
            <?php foreach ($porEstado as $est => $n): ?>
                <a href="?seccion=reservas&estado=<?= $est ?>" class="<?= $filtro === $est ? 'activo' : '' ?>"><?= $est ?> (<?= $n ?>)</a>
            <?php endforeach; ?>
        </div>
        <div class="tabla-caja">
            <table>
                <thead><tr><th>Cliente</th><th>Fecha del evento</th><th>Evento</th><th>Estado</th><th>Acciones</th></tr></thead>
                <tbody>
                <?php foreach ($lista as $r): ?>
                    <tr>
                        <td><?= e($r['nombre_contacto']) ?><span class="sub-dato"><?= e($r['correo_contacto']) ?></span></td>
                        <td><?= fechaCorta($r['fecha_evento']) ?><span class="sub-dato"><?= horaBonita($r['hora_evento']) ?></span></td>
                        <td><?= e($r['tipo_evento']) ?><span class="sub-dato"><?= (int)$r['invitados'] ?> invitados</span></td>
                        <td><?= etiquetaEstado($r['estado']) ?></td>
                        <td class="acciones-celda">
                            <button type="button" class="btn btn-oscuro btn-sm" onclick="verDetalles(this)" data-reserva="<?= datosReserva($r) ?>">Ver</button>
                            <?= botonesEstado($r, $volver) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$lista): ?><tr><td colspan="5" class="vacio">No hay reservas<?= $filtro ? ' en estado ' . strtolower($filtro) : '' ?>.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>

<?php
/* =====================================================
   COTIZACIONES
   ===================================================== */
elseif ($seccion === 'cotizaciones'):
    $lista = $cotizacionM->listar();
?>
        <div class="cabecera"><h2><?= icono('documento') ?> Cotizaciones</h2></div>
        <div class="cotizaciones">
            <?php foreach ($lista as $c): ?>
                <article class="cotizacion">
                    <div class="cabeza">
                        <h3><?= e($c['tipo_evento']) ?> · <?= e($c['nombre_contacto']) ?></h3>
                        <?= etiquetaEstado($c['estado']) ?>
                    </div>
                    <div class="detalles">
                        <span><?= icono('correo') ?> <a href="mailto:<?= e($c['correo_contacto']) ?>"><?= e($c['correo_contacto']) ?></a></span>
                        <?php if ($c['telefono']): ?><span><?= icono('telefono') ?> <?= e($c['telefono']) ?></span><?php endif; ?>
                        <span><?= icono('calendario') ?> <?= $c['fecha_tentativa'] ? fechaCorta($c['fecha_tentativa']) : 'Sin fecha' ?></span>
                        <span><?= icono('usuarios') ?> <?= (int)$c['invitados'] ?> invitados</span>
                        <?php if ($c['presupuesto']): ?><span><?= icono('dinero') ?> <?= e($c['presupuesto']) ?></span><?php endif; ?>
                        <span><?= icono('reloj') ?> <?= fechaCorta($c['fecha_solicitud']) ?></span>
                        <?php if (!$c['id_cliente']): ?><span><?= icono('usuario') ?> Sin cuenta</span><?php endif; ?>
                    </div>
                    <?php if ($c['servicios']): ?><p class="sub-dato" style="margin:8px 0 0">Servicios: <?= e($c['servicios']) ?></p><?php endif; ?>
                    <?php if ($c['mensaje']): ?><p style="margin:8px 0 0;font-style:italic">“<?= e($c['mensaje']) ?>”</p><?php endif; ?>

                    <?php if ($c['estado'] === 'Respondida'): ?>
                        <div class="respuesta">
                            <div class="monto"><?= dinero($c['monto_estimado']) ?></div>
                            <p style="margin:6px 0 0"><?= nl2br(e($c['respuesta'])) ?></p>
                        </div>
                    <?php else: ?>
                        <form method="POST" class="respuesta">
                            <input type="hidden" name="accion" value="responder">
                            <input type="hidden" name="id_cotizacion" value="<?= (int)$c['id_cotizacion'] ?>">
                            <input type="hidden" name="volver" value="<?= e($volver) ?>">
                            <label class="etiqueta" for="m<?= (int)$c['id_cotizacion'] ?>">Valor estimado</label>
                            <div class="campo"><?= icono('dinero') ?><input id="m<?= (int)$c['id_cotizacion'] ?>" name="monto" inputmode="numeric" placeholder="Ej. 4.500.000" required></div>
                            <label class="etiqueta" for="r<?= (int)$c['id_cotizacion'] ?>">Mensaje para el cliente</label>
                            <textarea class="area" id="r<?= (int)$c['id_cotizacion'] ?>" name="respuesta" placeholder="Qué incluye la cotización, condiciones, forma de pago..." required></textarea>
                            <button class="btn btn-sm"><?= icono('enviar') ?> Enviar respuesta</button>
                        </form>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
            <?php if (!$lista): ?><div class="caja vacio">Todavía no hay cotizaciones.</div><?php endif; ?>
        </div>

<?php
/* =====================================================
   CLIENTES
   ===================================================== */
elseif ($seccion === 'clientes'):
    $lista = $clienteM->listarConResumen();
?>
        <div class="cabecera"><h2><?= icono('usuarios') ?> Clientes (<?= count($lista) ?>)</h2></div>
        <div class="tabla-caja">
            <table>
                <thead><tr><th>Nombre</th><th>Contacto</th><th>Reservas</th><th>Estado</th><th>Acciones</th></tr></thead>
                <tbody>
                <?php foreach ($lista as $c): ?>
                    <tr>
                        <td><?= e(trim($c['nombres'] . ' ' . $c['apellidos'])) ?></td>
                        <td><?= e($c['correo']) ?><span class="sub-dato"><?= e($c['telefono'] ?: 'Sin teléfono') ?></span></td>
                        <td><?= (int)$c['total_reservas'] ?><span class="sub-dato"><?= (int)$c['aprobadas'] ?> aprobadas</span></td>
                        <td><span class="estado <?= $c['estado'] === 'ACTIVO' ? 'estado-aprobada' : 'estado-cancelada' ?>"><?= ucfirst(strtolower($c['estado'])) ?></span></td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="accion" value="estado_cliente">
                                <input type="hidden" name="id_cliente" value="<?= (int)$c['id_cliente'] ?>">
                                <input type="hidden" name="volver" value="<?= e($volver) ?>">
                                <?php if ($c['estado'] === 'ACTIVO'): ?>
                                    <button class="btn btn-borde btn-sm" name="estado" value="INACTIVO" onclick="return confirm('El cliente no podrá iniciar sesión. ¿Continuar?')">Desactivar</button>
                                <?php else: ?>
                                    <button class="btn btn-sm" name="estado" value="ACTIVO">Activar</button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$lista): ?><tr><td colspan="5" class="vacio">Aún no hay clientes registrados.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>

<?php
/* =====================================================
   REPORTES
   ===================================================== */
elseif ($seccion === 'reportes'):
    $anioRep  = (int)date('Y');
    $porTipo  = $reservaM->contarPorTipo();
    $maxTipo  = max(array_column($porTipo, 'n') ?: [1]);
    $porMes   = $reservaM->aprobadasPorMes($anioRep);
    $maxMes   = max(max($porMes), 1);
?>
        <div class="cabecera"><h2><?= icono('grafica') ?> Reportes</h2></div>
        <div class="tarjetas cuatro">
            <div class="tarjeta"><?= icono('usuarios') ?><span class="nombre">Clientes</span><b><?= $clienteM->contar() ?></b></div>
            <div class="tarjeta"><?= icono('reloj') ?><span class="nombre">Reservas pendientes</span><b><?= $porEstado['Pendiente'] ?></b></div>
            <div class="tarjeta"><?= icono('check') ?><span class="nombre">Reservas aprobadas</span><b><?= $porEstado['Aprobada'] ?></b></div>
            <div class="tarjeta"><?= icono('documento') ?><span class="nombre">Cotizaciones por responder</span><b><?= $cotizacionesPend ?></b></div>
        </div>
        <div class="reportes-grid">
            <div class="caja">
                <h3>Reservas por tipo de evento</h3>
                <div class="barras">
                    <?php foreach ($porTipo as $t): ?>
                        <div class="barra"><span><?= e($t['tipo_evento']) ?></span>
                            <div class="pista"><div class="relleno" style="width:<?= round($t['n'] / $maxTipo * 100) ?>%"></div></div>
                            <b><?= (int)$t['n'] ?></b></div>
                    <?php endforeach; ?>
                    <?php if (!$porTipo): ?><p class="vacio">Sin datos todavía.</p><?php endif; ?>
                </div>
            </div>
            <div class="caja">
                <h3>Estado de las solicitudes</h3>
                <div class="barras">
                    <?php $maxEst = max(max($porEstado), 1);
                    foreach ($porEstado as $est => $n): ?>
                        <div class="barra"><span><?= $est ?></span>
                            <div class="pista"><div class="relleno" style="width:<?= round($n / $maxEst * 100) ?>%"></div></div>
                            <b><?= $n ?></b></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="caja">
            <h3>Eventos aprobados por mes (<?= $anioRep ?>)</h3>
            <div class="meses">
                <?php foreach ($porMes as $m => $n): ?>
                    <div title="<?= MESES[$m - 1] ?>: <?= $n ?>">
                        <b><?= $n ?: '' ?></b>
                        <span class="col" style="height:<?= round($n / $maxMes * 100) ?>%"></span>
                        <?= substr(MESES[$m - 1], 0, 3) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

<?php
/* =====================================================
   CONFIGURACIÓN
   ===================================================== */
else:
    $bloqueos = $bloqueoM->futuras();
?>
        <div class="cabecera"><h2><?= icono('ajustes') ?> Configuración</h2></div>
        <div class="perfil-grid">
            <div class="caja">
                <h3>Días bloqueados (mantenimiento)</h3>
                <form method="POST" class="form-linea" style="margin-bottom:16px">
                    <input type="hidden" name="accion" value="bloquear">
                    <input type="hidden" name="volver" value="<?= e($volver) ?>">
                    <input type="date" name="fecha" min="<?= $hoy ?>" required aria-label="Fecha a bloquear">
                    <input name="motivo" placeholder="Motivo" maxlength="120" aria-label="Motivo">
                    <button class="btn btn-sm"><?= icono('mas') ?> Bloquear</button>
                </form>
                <?php foreach ($bloqueos as $b): ?>
                    <form method="POST" class="form-linea" style="justify-content:space-between;border-top:1px solid var(--crema);padding:8px 0">
                        <span><b><?= fechaCorta($b['fecha']) ?></b> · <?= e($b['motivo']) ?></span>
                        <input type="hidden" name="accion" value="desbloquear">
                        <input type="hidden" name="fecha" value="<?= e($b['fecha']) ?>">
                        <input type="hidden" name="volver" value="<?= e($volver) ?>">
                        <button class="btn btn-borde btn-sm">Quitar</button>
                    </form>
                <?php endforeach; ?>
                <?php if (!$bloqueos): ?><p class="sub-dato">No hay días bloqueados.</p><?php endif; ?>
            </div>

            <form method="POST" class="caja" oninput="a2.setCustomValidity(a2.value && a1.value !== a2.value ? 'No coinciden' : '')">
                <h3>Cambiar mi contraseña</h3>
                <input type="hidden" name="accion" value="password">
                <input type="hidden" name="volver" value="<?= e($volver) ?>">
                <label class="etiqueta" for="a0">Contraseña actual</label>
                <div class="campo"><?= icono('candado') ?><input id="a0" type="password" name="actual" required autocomplete="current-password"></div>
                <label class="etiqueta" for="a1">Nueva contraseña</label>
                <div class="campo"><?= icono('candado') ?><input id="a1" type="password" name="nueva" minlength="8" required autocomplete="new-password"></div>
                <label class="etiqueta" for="a2">Confirmar</label>
                <div class="campo"><?= icono('candado') ?><input id="a2" type="password" name="confirmar" required autocomplete="new-password"></div>
                <button class="btn"><?= icono('check') ?> Guardar</button>
            </form>
        </div>
<?php endif; ?>
    </main>
</div>

<!-- Modal "Ver detalles" de una reserva -->
<dialog id="modal-detalle" class="modal">
    <div class="modal-cabeza"><h3>Detalle de la solicitud</h3>
        <button type="button" onclick="this.closest('dialog').close()" aria-label="Cerrar"><?= icono('x') ?></button></div>
    <div class="modal-cuerpo"><dl id="detalle"></dl></div>
    <form method="POST" class="modal-pie" id="detalle-acciones">
        <input type="hidden" name="id_reserva" id="detalle-id">
        <input type="hidden" name="volver" value="<?= e($volver) ?>">
        <button class="btn btn-borde" name="accion" value="rechazar"><?= icono('x') ?> Rechazar</button>
        <button class="btn" name="accion" value="aprobar"><?= icono('check') ?> Aprobar</button>
    </form>
</dialog>

<script>
function verDetalles(boton) {
    const r = JSON.parse(boton.dataset.reserva);
    const dl = document.getElementById('detalle');
    dl.innerHTML = '';
    for (const [k, v] of Object.entries(r.filas)) {
        const dt = document.createElement('dt'); dt.textContent = k;
        const dd = document.createElement('dd'); dd.textContent = v;
        dl.append(dt, dd);
    }
    const estado = document.createElement('dd');
    estado.innerHTML = '<span class="estado estado-' + r.estado.toLowerCase() + '"></span>';
    estado.firstChild.textContent = r.estado;
    const dt = document.createElement('dt'); dt.textContent = 'Estado';
    dl.append(dt, estado);

    document.getElementById('detalle-id').value = r.id;
    document.getElementById('detalle-acciones').hidden = r.estado !== 'Pendiente';
    document.getElementById('modal-detalle').showModal();
}
</script>

<?php require __DIR__ . "/partials/pie.php"; ?>
