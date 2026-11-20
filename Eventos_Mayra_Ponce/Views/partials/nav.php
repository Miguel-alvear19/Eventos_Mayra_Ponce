<?php
/*
 * Barra de navegación.
 * Variables: $activo ('inicio','servicios','galeria','salon','contacto')
 *            $oscura (true = versión café de los paneles)
 */
$activo = $activo ?? '';
$oscura = $oscura ?? false;
$links  = [
    'salon'     => ['El salón',  'inicio#salon'],
    'servicios' => ['Servicios', 'inicio#servicios'],
    'videos'    => ['Videos',    'inicio#videos'],
    'menu'      => ['Menú',      'inicio#menu'],
    'galeria'   => ['Galería',   'inicio#galeria'],
    'preguntas' => ['Preguntas', 'inicio#preguntas'],
    'contacto'  => ['Contacto',  'inicio#contacto'],
];
if (esAdmin()) {
    $cuenta = ['admin', 'Panel administrativo'];
} elseif (esCliente()) {
    $cuenta = ['mi-panel', 'Mi panel'];
} else {
    $cuenta = ['login', 'Iniciar sesión'];
}
?>
<header class="<?= $oscura ? 'topbar' : 'nav' ?>">
    <div class="<?= $oscura ? 'interior' : 'nav-interior' ?>" style="position:relative">
        <a class="logo" href="<?= url('inicio') ?>" aria-label="Eventos Mayra Ponce, ir al inicio">
            <img class="logo-img" src="<?= recurso($oscura ? 'Imagenes/logo-claro.png' : 'Imagenes/logo.png') ?>"
                 alt="Eventos Mayra Ponce" width="200" height="71">
        </a>

        <button class="nav-toggle" type="button" aria-label="Abrir menú" aria-expanded="false"
                onclick="const l=this.nextElementSibling;l.classList.toggle('abierto');this.setAttribute('aria-expanded',l.classList.contains('abierto'))">
            <?= icono('menu') ?>
        </button>
        <ul class="nav-links">
            <?php foreach ($links as $clave => [$texto, $url]): ?>
                <li><a href="<?= url($url) ?>" class="<?= $activo === $clave ? 'activo' : '' ?>"><?= $texto ?></a></li>
            <?php endforeach; ?>
        </ul>

        <div class="nav-acciones">
            <?php if (!esAdmin()): ?>
                <a class="btn <?= $oscura ? '' : 'btn-cuadrado' ?>" href="<?= url('reservar') ?>"><?= icono('calendario') ?> Reservar fecha</a>
            <?php endif; ?>
            <a class="nav-usuario" href="<?= url($cuenta[0]) ?>" title="<?= $cuenta[1] ?>" aria-label="<?= $cuenta[1] ?>"><?= icono('usuario') ?></a>
        </div>
    </div>
</header>
