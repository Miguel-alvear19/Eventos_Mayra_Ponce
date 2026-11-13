<?php
require_once dirname(__DIR__) . "/Config/helpers.php";

$titulo = 'Inicio';
$activo = 'inicio';
require "partials/head.php";
require "partials/nav.php";
?>
<main>
    <?= mostrarFlash() ?>

    <!-- ============ HERO ============ -->
    <section class="hero-home">
        <div class="interior">
            <div class="hero-texto">
                <?= icono('flor', 'hero-flor') ?>
                <h1>Creamos momentos <span class="script">para recordar</span></h1>
                <p>Un espacio especial para bodas, cumpleaños y eventos sociales y empresariales.</p>
                <div class="hero-botones">
                    <a class="btn btn-grande" style="width:auto" href="#salon">Conocer el salón <?= icono('derecha') ?></a>
                    <a class="btn btn-borde btn-grande" style="width:auto" href="SolicitarCotizacion.php">Solicitar cotización <?= icono('derecha') ?></a>
                </div>
            </div>
            <div class="hero-foto" role="img" aria-label="Salón decorado con flores blancas y candelabros"></div>
        </div>
    </section>

    <!-- ============ TIPOS DE EVENTO ============ -->
    <section class="seccion-home" style="padding-top:0">
        <div class="categorias">
            <?php
            $categorias = [
                ['Bodas',                 'bodas.jpg',       'Boda'],
                ['Cumpleaños',            'cumpleanos.jpg',  'Cumpleaños'],
                ['Eventos sociales',      'social.jpg',      'Evento social'],
                ['Eventos empresariales', 'empresarial.jpg', 'Evento empresarial'],
            ];
            foreach ($categorias as [$nombre, $foto, $tipo]): ?>
                <a class="categoria aparecer" href="SolicitarCotizacion.php?tipo=<?= urlencode($tipo) ?>">
                    <img src="../Imagenes/<?= $foto ?>" alt="" loading="lazy">
                    <h3><?= $nombre ?></h3>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ============ GALERÍA ============ -->
    <section class="seccion-home" id="galeria" style="padding-top:10px">
        <div class="titulo-seccion aparecer">
            <span class="script">Nuestra</span>
            <h2>Galería</h2>
        </div>
        <div class="galeria">
            <figure class="aparecer"><img src="../Imagenes/galeria1.jpg" alt="Montaje de boda con centros de mesa florales" loading="lazy"><figcaption>Bodas de ensueño</figcaption></figure>
            <figure class="aparecer"><img src="../Imagenes/galeria2.jpg" alt="Mesa principal con arco de flores blancas" loading="lazy"><figcaption>Mesa principal</figcaption></figure>
            <figure class="aparecer"><img src="../Imagenes/galeria3.jpg" alt="Salón iluminado con luces cálidas" loading="lazy"><figcaption>Ambientes cálidos</figcaption></figure>
        </div>
    </section>

    <!-- ============ SERVICIOS ============ -->
    <section class="seccion-home" id="servicios">
        <div class="titulo-seccion aparecer">
            <span class="script">Todo lo que necesitas</span>
            <h2>Nuestros servicios</h2>
            <p>Nos encargamos de cada detalle para que tú solo te preocupes por disfrutar.</p>
        </div>
        <div class="servicios-home">
            <?php
            $servicios = [
                ['flor',      'Decoración',            'Ambientación floral, centros de mesa, telas y luces a la medida de tu estilo.'],
                ['silla',     'Mobiliario y menaje',   'Sillas, mesas, mantelería y vajilla elegantes para cada invitado.'],
                ['cubiertos', 'Catering',              'Menús personalizados, bebidas y estaciones de postres.'],
                ['musica',    'Sonido e iluminación',  'Equipos profesionales, DJ y luces para crear el ambiente perfecto.'],
                ['camara',    'Fotografía y video',    'Guarda cada momento con nuestro equipo de fotografía y video.'],
                ['brillo',    'Logística completa',    'Coordinamos tu evento de principio a fin, sin preocupaciones.'],
            ];
            foreach ($servicios as [$ico, $nombre, $texto]): ?>
                <article class="servicio aparecer">
                    <div class="circulo"><?= icono($ico) ?></div>
                    <h3><?= $nombre ?></h3>
                    <p><?= $texto ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ============ EL SALÓN ============ -->
    <div class="banda-salon" id="salon">
        <section class="seccion-home salon-home">
            <img class="aparecer" src="../Imagenes/salon.jpg" alt="Salón principal con mesas doradas y sillas blancas" loading="lazy">
            <div class="aparecer">
                <span class="script" style="font-size:32px">El salón</span>
                <h2>Un espacio elegante y versátil</h2>
                <p>Techos con telas y candelabros de cristal, mobiliario dorado y una iluminación cálida que
                   se adapta a cualquier celebración: desde una boda íntima hasta un gran evento empresarial.</p>
                <div class="datos-salon">
                    <div class="dato"><?= icono('usuarios') ?><div><b><?= NEGOCIO_CAPACIDAD ?></b><small>invitados máximo</small></div></div>
                    <div class="dato"><?= icono('estrella') ?><div><b>+500</b><small>eventos realizados</small></div></div>
                    <div class="dato"><?= icono('brillo') ?><div><b>4</b><small>tipos de evento</small></div></div>
                    <div class="dato"><?= icono('corazon') ?><div><b>100%</b><small>a tu medida</small></div></div>
                </div>
                <a class="btn" href="<?= esCliente() ? 'SolicitarReserva.php' : 'Login.php' ?>"><?= icono('calendario') ?> Reservar una fecha</a>
            </div>
        </section>
    </div>

    <!-- ============ TESTIMONIOS ============ -->
    <section class="seccion-home">
        <div class="titulo-seccion aparecer">
            <span class="script">Lo que dicen</span>
            <h2>Nuestros clientes</h2>
        </div>
        <div class="testimonios">
            <?php
            $testimonios = [
                ['Todo quedó exactamente como lo soñamos. La decoración y la atención fueron impecables.', 'Laura y Andrés', 'Boda'],
                ['Los quince de mi hija fueron mágicos. Gracias por estar pendientes de cada detalle.', 'Carolina G.', 'Cumpleaños'],
                ['Organizamos nuestro evento de fin de año y todo fue puntual y muy profesional.', 'Empresa local', 'Evento empresarial'],
            ];
            foreach ($testimonios as [$texto, $autor, $tipo]): ?>
                <blockquote class="testimonio aparecer" style="margin:0">
                    <div class="estrellas" aria-label="5 estrellas"><?= str_repeat(icono('estrella'), 5) ?></div>
                    <p><?= $texto ?></p>
                    <div class="autor"><?= $autor ?><small><?= $tipo ?></small></div>
                </blockquote>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ============ CONTACTO ============ -->
    <section class="seccion-home" id="contacto" style="padding-top:0">
        <div class="titulo-seccion aparecer">
            <span class="script">Hablemos</span>
            <h2>Contacto</h2>
        </div>
        <div class="contacto-home">
            <div class="contacto-datos aparecer">
                <h3>Visítanos o escríbenos</h3>
                <p><?= icono('ubicacion') ?> <?= e(NEGOCIO_DIRECCION) ?></p>
                <p><?= icono('telefono') ?> <a href="tel:<?= preg_replace('/\s+/', '', NEGOCIO_TELEFONO) ?>"><?= e(NEGOCIO_TELEFONO) ?></a></p>
                <p><?= icono('correo') ?> <a href="mailto:<?= e(NEGOCIO_CORREO) ?>"><?= e(NEGOCIO_CORREO) ?></a></p>
                <p><?= icono('reloj') ?> <?= e(NEGOCIO_HORARIO) ?></p>
                <a class="btn" target="_blank" rel="noopener"
                   href="https://wa.me/<?= NEGOCIO_WHATSAPP ?>?text=<?= rawurlencode('Hola, quiero información sobre el salón de eventos.') ?>">
                    <?= icono('whatsapp') ?> Escríbenos por WhatsApp</a>
            </div>
            <div class="mapa aparecer">
                <iframe title="Ubicación del salón" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                        src="https://www.google.com/maps?q=<?= urlencode(NEGOCIO_DIRECCION) ?>&output=embed"></iframe>
            </div>
        </div>
    </section>

    <!-- ============ LLAMADO FINAL ============ -->
    <section class="cta-final">
        <h2>¿Listo para celebrar?</h2>
        <p>Cuéntanos tu idea y te enviamos una cotización sin compromiso.</p>
        <a class="btn btn-grande" style="width:auto" href="SolicitarCotizacion.php"><?= icono('enviar') ?> Solicitar cotización</a>
    </section>
</main>

<dialog id="visor" class="visor">
    <img src="" alt="">
    <button type="button" onclick="this.closest('dialog').close()" aria-label="Cerrar"><?= icono('x') ?></button>
</dialog>

<?php $pie = 'completo'; require "partials/pie.php"; ?>
