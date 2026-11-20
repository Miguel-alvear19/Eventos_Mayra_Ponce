<?php
require_once dirname(__DIR__) . "/Config/helpers.php";
require_once dirname(__DIR__) . "/controllers/ClienteController.php";

// Si ya hay sesión, no tiene sentido mostrar el login
if (esAdmin())   redirigir('admin');
if (esCliente()) redirigir('mi-panel');

$error = '';
if (isset($_POST['btnLogin'])) {
    $controlador = new ClienteController();
    $error = $controlador->loginCliente();   // si es correcto, redirige y no vuelve aquí
}

$titulo = 'Iniciar sesión';
require __DIR__ . "/partials/head.php";
require __DIR__ . "/partials/nav.php";
?>
<main class="escena foto-angosta">

    <div class="foto foto-login"><span class="cita">Bienvenido</span></div>

    <form method="POST" class="tarjeta-auth" data-un-envio>
        <h1>Iniciar sesión</h1>
        <p class="sub">Bienvenido de nuevo</p>
        <span class="divisor"></span>

        <?= mostrarFlash() ?>
        <?php if ($error): ?><div class="alerta alerta-error" role="alert"><?= e($error) ?></div><?php endif; ?>

        <label class="campo">
            <?= icono('correo') ?>
            <span class="oculto-visual">Correo electrónico</span>
            <input type="email" name="correo" placeholder="Correo electrónico" value="<?= e(valorPost('correo')) ?>" autocomplete="email" required autofocus>
        </label>

        <div class="campo">
            <?= icono('candado') ?>
            <label for="clave" class="oculto-visual">Contraseña</label>
            <input type="password" name="password" id="clave" placeholder="Contraseña" autocomplete="current-password" required>
            <button type="button" class="campo-boton" onclick="verClave('clave', this)" aria-label="Mostrar contraseña"><?= icono('ojo') ?></button>
        </div>

        <div class="fila">
            <label class="casilla"><input type="checkbox" name="recordar"> Recordarme</label>
            <a href="#" onclick="abrirModal('olvido');return false">¿Olvidaste tu contraseña?</a>
        </div>

        <button type="submit" name="btnLogin" class="btn btn-grande">Ingresar</button>

        <p class="pie-tarjeta">¿No tienes cuenta? <a href="<?= url('registro') ?>">Crear cuenta</a></p>
    </form>

</main>

<dialog id="olvido" class="modal">
    <div class="modal-cabeza"><h3>¿Olvidaste tu contraseña?</h3>
        <button type="button" onclick="this.closest('dialog').close()" aria-label="Cerrar"><?= icono('x') ?></button></div>
    <div class="modal-cuerpo">
        <p>Escríbenos y te ayudamos a recuperar el acceso a tu cuenta:</p>
        <?php if (NEGOCIO_CORREO !== ''): ?><p><?= icono('correo') ?> <a href="mailto:<?= e(NEGOCIO_CORREO) ?>?subject=Recuperar%20contrase%C3%B1a"><?= e(NEGOCIO_CORREO) ?></a></p><?php endif; ?>
        <p><?= icono('whatsapp') ?> <a target="_blank" rel="noopener" href="https://wa.me/<?= NEGOCIO_WHATSAPP ?>">WhatsApp <?= e(NEGOCIO_TELEFONO) ?></a></p>
    </div>
</dialog>

<?php require __DIR__ . "/partials/pie.php"; ?>
