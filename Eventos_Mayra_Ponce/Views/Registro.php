<?php
require_once dirname(__DIR__) . "/Config/helpers.php";
require_once dirname(__DIR__) . "/controllers/ClienteController.php";

if (esCliente()) redirigir('mi-panel');

$errores = [];
if (isset($_POST['btnRegistrar'])) {
    $controlador = new ClienteController();
    $errores = $controlador->registrarCliente();   // si todo sale bien, redirige al panel
}

$titulo = 'Crear cuenta';
require __DIR__ . "/partials/head.php";
require __DIR__ . "/partials/nav.php";
?>
<main class="escena">

    <div class="foto foto-registro"><span class="cita">Tu evento comienza aquí</span></div>

    <form method="POST" class="tarjeta-auth" data-un-envio
          oninput="document.getElementById('clave2').setCustomValidity(clave2.value && clave1.value !== clave2.value ? 'Las contraseñas no coinciden' : '')">
        <h1>Crear cuenta</h1>
        <p class="sub">Regístrate para gestionar tus solicitudes</p>
        <span class="divisor"></span>

        <?php if ($errores): ?>
            <div class="alerta alerta-error" role="alert">
                <?php if (count($errores) === 1): ?><?= e($errores[0]) ?>
                <?php else: ?><ul><?php foreach ($errores as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul><?php endif; ?>
            </div>
        <?php endif; ?>

        <label class="campo"><?= icono('usuario') ?><span class="oculto-visual">Nombre completo</span>
            <input type="text" name="nombre" placeholder="Nombre completo" value="<?= e(valorPost('nombre')) ?>" autocomplete="name" required minlength="3"></label>

        <label class="campo"><?= icono('correo') ?><span class="oculto-visual">Correo electrónico</span>
            <input type="email" name="correo" placeholder="Correo electrónico" value="<?= e(valorPost('correo')) ?>" autocomplete="email" required></label>

        <label class="campo"><?= icono('telefono') ?><span class="oculto-visual">Teléfono</span>
            <input type="tel" name="telefono" placeholder="Teléfono (opcional)" value="<?= e(valorPost('telefono')) ?>" autocomplete="tel"></label>

        <div class="campo"><?= icono('candado') ?>
            <label for="clave1" class="oculto-visual">Contraseña</label>
            <input type="password" name="password" id="clave1" placeholder="Contraseña (mínimo 6 caracteres)" autocomplete="new-password" required minlength="6">
            <button type="button" class="campo-boton" onclick="verClave('clave1', this)" aria-label="Mostrar contraseña"><?= icono('ojo') ?></button></div>

        <div class="campo"><?= icono('candado') ?>
            <label for="clave2" class="oculto-visual">Confirmar contraseña</label>
            <input type="password" name="confirmar" id="clave2" placeholder="Confirmar contraseña" autocomplete="new-password" required></div>

        <label class="casilla" style="margin:4px 0 18px">
            <input type="checkbox" name="terminos" required <?= isset($_POST['terminos']) ? 'checked' : '' ?>>
            <span>Acepto los <a href="#" onclick="abrirModal('terminos');return false">términos y condiciones</a></span>
        </label>

        <button type="submit" name="btnRegistrar" class="btn btn-grande">Crear cuenta</button>

        <p class="pie-tarjeta">¿Ya tienes cuenta? <a href="<?= url('login') ?>">Iniciar sesión</a></p>
    </form>

</main>

<dialog id="terminos" class="modal">
    <div class="modal-cabeza"><h3>Términos y condiciones</h3>
        <button type="button" onclick="this.closest('dialog').close()" aria-label="Cerrar"><?= icono('x') ?></button></div>
    <div class="modal-cuerpo">
        <p>Al crear tu cuenta aceptas que Eventos Mayra Ponce use tus datos (nombre, correo y teléfono) únicamente
           para gestionar tus reservas y cotizaciones y para contactarte sobre ellas.</p>
        <p>Una reserva solo queda confirmada cuando el salón la aprueba. Las solicitudes pendientes pueden cancelarse
           desde tu panel en cualquier momento.</p>
        <p>Puedes pedir la eliminación de tus datos escribiéndonos por WhatsApp al <?= e(NEGOCIO_TELEFONO) ?>.</p>
    </div>
    <div class="modal-pie"><button class="btn" type="button" onclick="this.closest('dialog').close()">Entendido</button></div>
</dialog>

<?php require __DIR__ . "/partials/pie.php"; ?>
