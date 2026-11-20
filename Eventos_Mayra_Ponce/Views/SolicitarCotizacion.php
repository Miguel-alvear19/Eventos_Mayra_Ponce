<?php
require_once dirname(__DIR__) . "/Config/helpers.php";
require_once dirname(__DIR__) . "/controllers/CotizacionController.php";

$errores = [];
$enviada = false;
if (isset($_POST['btnCotizar'])) {
    $errores = (new CotizacionController())->crear();
    $enviada = !$errores;
}

// Valores del formulario (se conservan si hubo errores; "tipo" puede venir del inicio)
$tipoElegido = valorPost('tipo', $_GET['tipo'] ?? '');
$serviciosElegidos = $_POST['servicios'] ?? [];

// Con sesión: mostramos los datos de la cuenta y, si viene de una reserva confirmada, sus datos
$cliente = esCliente() ? (new Cliente())->obtenerPorId($_SESSION['id_cliente']) : null;
$reserva = null;
if ($cliente && isset($_REQUEST['reserva']) || isset($_POST['id_reserva'])) {
    $idR = (int)($_POST['id_reserva'] ?? $_GET['reserva'] ?? 0);
    $r   = $cliente ? (new Reserva())->obtenerDeCliente($idR, $_SESSION['id_cliente']) : null;
    if ($r && $r['estado'] === 'Aprobada') {
        $reserva = $r;
        if (!isset($_POST['btnCotizar'])) {
            $serviciosElegidos = array_filter(array_map('trim', explode(',', (string)$r['servicios'])));
        }
    }
}
$invitadosValor = valorPost('invitados', $reserva ? (string)max(30, (int)$reserva['invitados']) : '');

$titulo = 'Solicitar cotización';
require __DIR__ . "/partials/head.php";
require __DIR__ . "/partials/nav.php";
?>
<main>
    <section class="encabezado-pagina">
        <h1>Solicitar cotización</h1>
        <p>˙ Cuéntanos qué necesitas y te asesoramos ˙</p>
        <span class="divisor" style="margin-bottom:0"></span>
    </section>

    <div class="escena foto-angosta" style="padding-top:24px">

        <div class="foto foto-cotizacion"></div>

        <?php if ($enviada): ?>
            <div class="exito-envio" style="align-self:center">
                <div class="icono-ok"><?= icono('check') ?></div>
                <h2>¡Solicitud enviada!</h2>
                <p>Revisaremos tu solicitud y te enviaremos la cotización
                   <?= esCliente() ? 'a tu panel' : 'a tu correo' ?> muy pronto.</p>
                <div class="acciones">
                    <?php if (esCliente()): ?>
                        <a class="btn" href="<?= url('mi-panel') ?>?seccion=cotizaciones">Ver mis cotizaciones</a>
                    <?php else: ?>
                        <a class="btn" href="<?= url('registro') ?>">Crear cuenta para seguirla</a>
                    <?php endif; ?>
                    <a class="btn btn-borde" href="<?= url('inicio') ?>">Volver al inicio</a>
                </div>
            </div>
        <?php else: ?>

        <form method="POST" class="form-cotizacion" data-un-envio>

            <?php if ($errores): ?>
                <div class="alerta alerta-error" role="alert" style="margin:0 0 16px">
                    <ul><?php foreach ($errores as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
                </div>
            <?php endif; ?>

            <?php if ($cliente): ?>
                <div class="cuenta-ficha">
                    <span class="avatar-mini"><?= e(mb_strtoupper(mb_substr($cliente['nombres'], 0, 1))) ?></span>
                    <div class="datos">
                        <span class="rotulo">Cotizando como</span>
                        <strong><?= e(trim($cliente['nombres'] . ' ' . $cliente['apellidos'])) ?></strong>
                        <span><?= e($cliente['correo']) ?><?= $cliente['telefono'] ? ' · ' . e($cliente['telefono']) : '' ?></span>
                    </div>
                    <a class="cambiar" href="<?= url('mi-panel') ?>?seccion=perfil">Editar</a>
                </div>
            <?php endif; ?>

            <?php if ($reserva): ?>
                <input type="hidden" name="id_reserva" value="<?= (int)$reserva['id_reserva'] ?>">
                <div class="evento-ficha">
                    <span class="rotulo">Tu evento confirmado · <?= numeroReserva($reserva['id_reserva']) ?></span>
                    <strong><?= e($reserva['tipo_evento']) ?></strong>
                    <span><?= fechaCorta($reserva['fecha_evento']) ?>, <?= horaBonita($reserva['hora_evento']) ?></span>
                </div>
            <?php endif; ?>

            <?php if (!esCliente()): ?>
                <!-- Sin sesión pedimos los datos de contacto -->
                <p class="ayuda" style="margin:0 0 12px">¿Ya tienes cuenta? <a href="<?= url('login') ?>">Inicia sesión</a> y no tendrás que escribir tus datos.</p>
                <label class="etiqueta" for="nombre">Nombre <span class="req">*</span></label>
                <div class="campo"><?= icono('usuario') ?><input id="nombre" name="nombre" placeholder="Tu nombre" value="<?= e(valorPost('nombre')) ?>" required></div>
                <div class="rejilla-2">
                    <div>
                        <label class="etiqueta" for="correo">Correo <span class="req">*</span></label>
                        <div class="campo"><?= icono('correo') ?><input id="correo" type="email" name="correo" placeholder="tu@correo.com" value="<?= e(valorPost('correo')) ?>" required></div>
                    </div>
                    <div>
                        <label class="etiqueta" for="telefono">Teléfono</label>
                        <div class="campo"><?= icono('telefono') ?><input id="telefono" type="tel" name="telefono" placeholder="Opcional" value="<?= e(valorPost('telefono')) ?>"></div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!$reserva): ?>
            <label class="etiqueta" for="tipo">Tipo de evento <span class="req">*</span></label>
            <div class="campo"><select id="tipo" name="tipo" required>
                <option value="">Selecciona una opción</option>
                <?php foreach (TIPOS_EVENTO as $t): ?>
                    <option <?= $tipoElegido === $t ? 'selected' : '' ?>><?= $t ?></option>
                <?php endforeach; ?>
            </select></div>

            <div class="rejilla-2">
                <div>
                    <label class="etiqueta" for="fecha">Fecha tentativa</label>
                    <div class="campo"><?= icono('calendario') ?><input id="fecha" type="date" name="fecha" min="<?= date('Y-m-d') ?>" value="<?= e(valorPost('fecha')) ?>"></div>
                </div>
                <div>
            <?php endif; ?>
            <?php if ($reserva): ?><div class="rejilla-2"><div><?php endif; ?>
                    <label class="etiqueta" for="invitados">Número de invitados <span class="req">*</span></label>
                    <div class="campo"><?= icono('usuarios') ?><input id="invitados" type="number" name="invitados" min="30" max="500" placeholder="Desde 30" value="<?= e($invitadosValor) ?>" required></div>
                    <p class="ayuda">Cotizamos por paquete, desde 30 personas.</p>
                </div>
            </div>

            <span class="etiqueta">Servicios de interés</span>
            <div class="chips">
                <?php foreach (SERVICIOS as $s): ?>
                    <label class="chip"><input type="checkbox" name="servicios[]" value="<?= $s ?>" <?= in_array($s, $serviciosElegidos, true) ? 'checked' : '' ?>><span><?= $s ?></span></label>
                <?php endforeach; ?>
            </div>

            <label class="etiqueta" for="presupuesto">Presupuesto aproximado</label>
            <div class="campo"><?= icono('dinero') ?><select id="presupuesto" name="presupuesto">
                <option value="">Selecciona un rango</option>
                <?php foreach (PRESUPUESTOS as $p): ?>
                    <option <?= valorPost('presupuesto') === $p ? 'selected' : '' ?>><?= $p ?></option>
                <?php endforeach; ?>
            </select></div>

            <label class="etiqueta" for="mensaje">Cuéntanos qué tienes en mente</label>
            <textarea class="area" id="mensaje" name="mensaje" placeholder="Escribe aquí tus ideas..." maxlength="1000"><?= e(valorPost('mensaje')) ?></textarea>

            <button type="submit" name="btnCotizar" class="btn btn-grande">Enviar solicitud <?= icono('enviar') ?></button>
        </form>
        <?php endif; ?>

    </div>
</main>

<?php require __DIR__ . "/partials/pie.php"; ?>
