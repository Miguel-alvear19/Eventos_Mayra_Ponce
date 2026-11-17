<?php
/*
 * Barra de navegación.
 * Variables: $activo ('inicio','servicios','galeria','salon','contacto')
 *            $oscura (true = versión café de los paneles)
 */
$activo = $activo ?? '';
$oscura = $oscura ?? false;
$links  = [
    'salon'     => ['El salón',  'Home.php#salon'],
    'servicios' => ['Servicios', 'Home.php#servicios'],
    'videos'    => ['Videos',    'Home.php#videos'],
    'menu'      => ['Menú',      'Home.php#menu'],
    'galeria'   => ['Galería',   'Home.php#galeria'],
    'preguntas' => ['Preguntas', 'Home.php#preguntas'],
    'contacto'  => ['Contacto',  'Home.php#contacto'],
];
if (esAdmin()) {
    $cuenta = ['PanelAdmin.php', 'Panel administrativo'];
} elseif (esCliente()) {
    $cuenta = ['PanelCliente.php', 'Mi panel'];
} else {
    $cuenta = ['Login.php', 'Iniciar sesión'];
}
?>
<header class="<?= $oscura ? 'topbar' : 'nav' ?>">
    <div class="<?= $oscura ? 'interior' : 'nav-interior' ?>" style="position:relative">
        <a class="logo" href="Home.php" aria-label="Eventos Mayra Ponce, ir al inicio">
            <img class="logo-img" src="<?= recurso($oscura ? 'Imagenes/logo-claro.png' : 'Imagenes/logo.png') ?>"
                 alt="Eventos Mayra Ponce" width="200" height="71">
        </a>

        <button class="nav-toggle" type="button" aria-label="Abrir menú" aria-expanded="false"
                onclick="const l=this.nextElementSibling;l.classList.toggle('abierto');this.setAttribute('aria-expanded',l.classList.contains('abierto'))">
            <?= icono('menu') ?>
        </button>
        <ul class="nav-links">
            <?php foreach ($links as $clave => [$texto, $url]): ?>
                <li><a href="<?= $url ?>" class="<?= $activo === $clave ? 'activo' : '' ?>"><?= $texto ?></a></li>
            <?php endforeach; ?>
        </ul>

        <div class="nav-acciones">
            <?php if (!esAdmin()): ?>
                <a class="btn <?= $oscura ? '' : 'btn-cuadrado' ?>" href="SolicitarReserva.php"><?= icono('calendario') ?> Reservar fecha</a>
            <?php endif; ?>
            <a class="nav-usuario" href="<?= $cuenta[0] ?>" title="<?= $cuenta[1] ?>" aria-label="<?= $cuenta[1] ?>"><?= icono('usuario') ?></a>
        </div>
    </div>
</header>
