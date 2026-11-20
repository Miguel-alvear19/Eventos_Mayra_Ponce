<?php
/*
 * Pie de página.
 * Variable: $pie = 'completo' (inicio, fondo café) o 'simple' (línea con el lema)
 */
$pie = $pie ?? 'simple';
?>
<?php if ($pie === 'completo'): ?>
<footer class="pie">
    <div class="pie-contacto">
        <a href="tel:<?= preg_replace('/\s+/', '', NEGOCIO_TELEFONO) ?>"><?= icono('telefono') ?> <?= e(NEGOCIO_TELEFONO) ?></a>
        <span class="sep"></span>
        <?php if (NEGOCIO_CORREO !== ''): ?>
        <a href="mailto:<?= e(NEGOCIO_CORREO) ?>"><?= icono('correo') ?> <?= e(NEGOCIO_CORREO) ?></a>
        <span class="sep"></span>
        <?php endif; ?>
        <a href="<?= url('inicio') ?>#contacto"><?= icono('ubicacion') ?> Ubicación</a>
        <div class="redes">
            <?php foreach (REDES as $red => $url): ?>
                <a href="<?= e($url) ?>" target="_blank" rel="noopener" aria-label="<?= ucfirst($red) ?>"><?= icono($red) ?></a>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="pie-marca">
        <img src="<?= recurso('Imagenes/logo-claro.png') ?>" alt="Eventos Mayra Ponce" width="230" height="81">
        <p>La vida son momentos. ¡Celébralos!</p>
    </div>
    <div class="pie-final">Eventos Mayra Ponce · © <?= date('Y') ?></div>
</footer>
<?php else: ?>
<footer class="pie-simple">
    <div class="interior">
        <span class="lema"><?= icono('calendario') ?> Eventos inolvidables, hechos a tu medida</span>
        <span class="marca">Eventos Mayra Ponce</span>
    </div>
</footer>
<?php endif; ?>
<script src="<?= recurso('Js/app.js') ?>"></script>
</body>
</html>
