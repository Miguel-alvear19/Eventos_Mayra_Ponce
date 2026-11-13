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

$titulo = 'Solicitar cotización';
require "partials/head.php";
require "partials/nav.php";
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
                   <?= esCliente() ? 'a tu panel y a tu correo' : 'a tu correo' ?> muy pronto.</p>
                <div class="acciones">
                    <?php if (esCliente()): ?>
                        <a class="btn" href="PanelCliente.php?seccion=cotizaciones">Ver mis cotizaciones</a>
                    <?php else: ?>
                        <a class="btn" href="Registro.php">Crear cuenta para seguirla</a>
                    <?php endif; ?>
                    <a class="btn btn-borde" href="Home.php">Volver al inicio</a>
                </div>
            </div>
        <?php else: ?>

        <form method="POST" class="form-cotizacion" data-un-envio>

            <?php if ($errores): ?>
                <div class="alerta alerta-error" role="alert" style="margin:0 0 16px">
                    <ul><?php foreach ($errores as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
                </div>
            <?php endif; ?>

            <?php if (!esCliente()): ?>
                <!-- Sin sesión pedimos los datos de contacto -->
                <p class="ayuda" style="margin:0 0 12px">¿Ya tienes cuenta? <a href="Login.php">Inicia sesión</a> y no tendrás que escribir tus datos.</p>
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
                    <label class="etiqueta" for="invitados">Número de invitados <span class="req">*</span></label>
                    <div class="campo"><?= icono('usuarios') ?><input id="invitados" type="number" name="invitados" min="1" max="500" placeholder="Ej. 50" value="<?= e(valorPost('invitados')) ?>" required></div>
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

<?php require "partials/pie.php"; ?>
