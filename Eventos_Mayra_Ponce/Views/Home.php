<?php
require_once dirname(__DIR__) . "/Config/helpers.php";

$titulo    = 'Inicio';
$activo    = 'inicio';
$estilos   = ['home.css'];
$claseBody = 'home';

$whatsapp       = 'https://wa.me/' . NEGOCIO_WHATSAPP . '?text=' . rawurlencode('Hola, quiero información sobre el salón de eventos.');
$whatsappVisita = 'https://wa.me/' . NEGOCIO_WHATSAPP . '?text=' . rawurlencode('Hola, quisiera agendar una visita al salón.');

// Capítulos del video: [título, descripción, segundo en que empieza]
$capitulos = [
    ['Quinceañeras',  'Mesa de postres y candelabros',  0],
    ['Cumpleaños',    'Montaje con flores y frutas',    6.4],
    ['El salón',      'Montaje completo e iluminación', 11.3],
];

// Galería. Las primeras 12 se ven de entrada; el resto aparece al pulsar "Ver más".
$galeria = [
    ['evento_boda_altar.jpg',          'Mesa de novios con arco de flores blancas',            'Bodas'],
    ['salon_centro_floral.jpg',        'Centro de mesa alto con rosas sobre el salón',         'Centros de mesa'],
    ['fondo_mediterraneo.jpg',         'Fondo de azulejos azules y amarillos con flores',      'Fondos temáticos'],
    ['evento_globos_sol.jpg',          'Pared de globos dorados con un sol iluminado',         'Decoración temática'],
    ['mesa_mariposas.jpg',             'Mesa de postres con mariposas iluminadas',             'Mesas de postres'],
    ['letras_neon.jpg',                'Letras iluminadas y flores en el salón',               'Letras y luces'],
    ['arco_globos.jpg',                'Arco de globos y candelabro sobre mesa de postres',    'Arcos de globos'],
    ['mesa_postres_dorada.jpg',        'Mesa de postres con bandejas doradas',                 'Postres'],
    ['rosas_amarillas.jpg',            'Arreglo de rosas amarillas en jarrón blanco',          'Arreglos florales'],
    ['mesa_postres_azul.jpg',          'Mesa de postres sobre mantel azul con velas',          'Cumpleaños'],
    ['fondo_castillo.jpg',             'Fondo de castillo con candelabro y letra iluminada',   'Quinceañeras'],
    ['evento_florero_dorado.jpg',      'Florero dorado y copas sobre una mesa con flores',     'Detalles dorados'],
    ['mesa_vinotinto.jpg',             'Puesto de mesa en vinotinto con plato dorado',         'Mantelería y vajilla'],
    ['evento_mesa_platos.jpg',         'Platos y cubiertos dorados sobre mesa de cristal',     'Montaje de mesa'],
    ['mesa_postres_candelabros.jpg',   'Mesa de postres entre candelabros encendidos',         'Candelabros'],
    ['torta_naranjas.jpg',             'Torta de varios pisos decorada con naranjas',          'Tortas'],
    ['evento_entrada_morada.jpg',      'Entrada decorada con flores y luces moradas',          'Entradas'],
    ['pista_luces.jpg',                'Pista de baile iluminada bajo luces colgantes',        'Pista iluminada'],
    ['evento_flores_camino.jpg',       'Camino de hortensias y rosas blancas',                 'Caminos de flores'],
    ['globos_rojo_dorado.jpg',         'Pared de globos rojos y dorados',                      'Fondos de globos'],
];
$visibles = 12;

require "partials/head.php";
require "partials/nav.php";
?>
<main>
    <?= mostrarFlash() ?>

    <!-- ============ PORTADA ============ -->
    <section class="h-hero" id="inicio">
        <div class="h-wrap">
            <div>
                <p class="h-etiqueta">Salón de eventos · Buenaventura</p>
                <h1>Un salón para las fechas que <em>no se repiten.</em></h1>
                <p class="h-intro">Bodas, quinceañeras, grados, baby showers y eventos empresariales bajo techos de tela,
                   candelabros de cristal y una luz cálida que se adapta a cada celebración.</p>
                <div class="h-botones">
                    <a class="btn" href="SolicitarReserva.php"><?= icono('calendario') ?> Reservar mi fecha</a>
                    <a class="h-enlace" href="#salon">Conocer el salón <?= icono('flecha') ?></a>
                </div>
                <div class="h-nota">
                    <span><?= icono('calendario') ?> Abierto todos los días</span>
                    <span><?= icono('ubicacion') ?> <?= e(NEGOCIO_DIRECCION) ?></span>
                </div>
            </div>

            <div class="h-collage">
                <span class="marco"></span>
                <span class="sello">La vida son momentos</span>
                <div class="h-img f1 revela" data-parallax="-18">
                    <img src="../Imagenes/evento_salon_techo.jpg" alt="Salón con techo de telas, candelabros, flores blancas y mesas doradas">
                </div>
                <div class="h-img f2 revela" data-parallax="26">
                    <img src="../Imagenes/evento_sillas_doradas.jpg" alt="Sillas blancas con detalles dorados alrededor de una mesa de cristal">
                </div>
            </div>
        </div>
    </section>

    <!-- ============ TIPOS DE EVENTO (cinta) ============ -->
    <nav class="h-cinta" aria-label="Tipos de evento">
        <div class="h-cinta-pista">
            <?php for ($vuelta = 0; $vuelta < 2; $vuelta++): ?>
                <ul <?= $vuelta ? 'aria-hidden="true"' : '' ?>>
                    <?php foreach (TIPOS_EVENTO as $tipo): if ($tipo === 'Otro') continue; ?>
                        <li><a href="SolicitarReserva.php?tipo=<?= urlencode($tipo) ?>" <?= $vuelta ? 'tabindex="-1"' : '' ?>><?= e($tipo) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            <?php endfor; ?>
        </div>
    </nav>

    <!-- ============ EL SALÓN ============ -->
    <section class="h-sec h-salon" id="salon">
        <div class="h-wrap">
            <figure class="aparecer">
                <div class="h-img f-grande revela"><img src="../Imagenes/evento_mesa_flores.jpg" alt="Mesa larga con camino de flores blancas y sillas doradas" loading="lazy"></div>
                <div class="h-img f-pequena revela"><img src="../Imagenes/evento_flores_blancas.jpg" alt="Arreglo de rosas y hortensias blancas" loading="lazy"></div>
            </figure>
            <div class="aparecer">
                <p class="h-etiqueta">El salón</p>
                <h2 class="h-titulo">Un espacio pensado para que <em>la luz haga el resto.</em></h2>
                <p class="h-texto">Techos con telas, candelabros de cristal, mobiliario dorado y una iluminación cálida
                   que cambia con cada montaje. Es un lugar para una boda íntima, un grado en familia o un evento
                   empresarial de gran tamaño.</p>
                <dl class="h-ficha">
                    <dt>Atención</dt><dd>Todos los días, de lunes a domingo</dd>
                    <dt>Ambiente</dt><dd>Techos de tela, candelabros y luz cálida</dd>
                    <dt>Mobiliario</dt><dd>Sillas blancas y mesas doradas</dd>
                    <dt>Cobertura</dt><dd>También organizamos eventos en otros lugares</dd>
                </dl>
                <div class="h-botones">
                    <a class="btn btn-borde" target="_blank" rel="noopener" href="<?= $whatsappVisita ?>"><?= icono('ubicacion') ?> Agendar una visita</a>
                    <a class="h-enlace" href="#galeria">Ver la galería <?= icono('flecha') ?></a>
                </div>
            </div>
        </div>
    </section>

    <!-- ============ SERVICIOS ============ -->
    <section class="h-sec h-serv" id="servicios">
        <div class="h-wrap">
            <div class="aparecer">
                <p class="h-etiqueta">Lo que ofrecemos</p>
                <h2 class="h-titulo">Cada detalle, <em>a tu medida.</em></h2>
                <p class="h-texto">Puedes alquilar el salón y encargarte tú, o dejarnos coordinar lo que necesites.</p>
            </div>
            <ul class="h-lista">
                <?php
                $servicios = [
                    ['Decoración',           'Ambientación floral, centros de mesa, telas y luces a la medida de tu estilo.'],
                    ['Mobiliario y menaje',  'Sillas, mesas, mantelería y vajilla para cada invitado.'],
                    ['Catering',             'Menús personalizados, bebidas y estaciones de postres.'],
                    ['Sonido e iluminación', 'Equipos profesionales y luces para crear el ambiente que buscas.'],
                    ['Fotografía y video',   'Para guardar cada momento de tu celebración.'],
                    ['Logística completa',   'Coordinamos tu evento de principio a fin.'],
                ];
                foreach ($servicios as $i => [$nombre, $texto]): ?>
                    <li class="aparecer">
                        <span class="n"><?= sprintf('%02d', $i + 1) ?></span>
                        <h3><?= $nombre ?></h3>
                        <p><?= $texto ?></p>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>

    <!-- ============ VIDEO ============ -->
    <section class="h-sec h-reel" id="videos">
        <div class="h-wrap">
            <div class="cabeza aparecer">
                <div>
                    <p class="h-etiqueta">En movimiento</p>
                    <h2 class="h-titulo" style="margin-bottom:0">Míralo <em>como si ya estuvieras ahí.</em></h2>
                </div>
                <a class="h-enlace" target="_blank" rel="noopener" href="<?= e(REDES['tiktok']) ?>">Más videos en TikTok <?= icono('flecha') ?></a>
            </div>

            <div class="reel aparecer" id="reel" data-fin="19.3">
                <div class="reel-marco">
                    <video id="reel-video" autoplay muted loop playsinline preload="auto"
                           poster="<?= recurso('Videos/reel.jpg') ?>" aria-label="Montajes del salón en video">
                        <source src="<?= recurso('Videos/reel.mp4') ?>" type="video/mp4">
                        <a href="<?= e(REDES['tiktok']) ?>">Ver los videos en TikTok</a>
                    </video>
                    <button type="button" class="reel-play" aria-label="Pausar el video">
                        <span class="ico-pausa"></span>
                    </button>
                </div>

                <div class="reel-panel">
                    <p class="reel-ahora">
                        <span class="reel-num">01</span><span class="reel-de">/ <?= count($capitulos) ?></span>
                        <span class="reel-txt"><?= e($capitulos[0][0]) ?><em><?= e($capitulos[0][1]) ?></em></span>
                    </p>
                    <div class="reel-segs" role="tablist" aria-label="Partes del video">
                        <?php foreach ($capitulos as $i => [$nom, $desc, $ini]): ?>
                            <button type="button" role="tab" data-inicio="<?= $ini ?>" data-nombre="<?= e($nom) ?>"
                                    data-desc="<?= e($desc) ?>" aria-label="<?= e($nom) ?>"
                                    <?= $i === 0 ? 'aria-selected="true"' : '' ?>><i></i></button>
                        <?php endforeach; ?>
                    </div>
                    <div class="reel-flechas">
                        <button type="button" class="reel-ant" aria-label="Parte anterior"><?= icono('izquierda') ?></button>
                        <button type="button" class="reel-sig" aria-label="Parte siguiente"><?= icono('derecha') ?></button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============ MENÚ ============ -->
    <section class="h-sec h-menu" id="menu">
        <div class="h-wrap">
            <div class="aparecer">
                <p class="h-etiqueta">Menú del salón</p>
                <h2 class="h-titulo">Para cada mesa, <em>un buen plato.</em></h2>
                <p class="h-texto">Estas son las opciones que preparamos en el salón. Cuando tu evento esté confirmado, armamos contigo el menú.</p>
            </div>
            <div class="especial aparecer">
                <span>Especial de la casa</span>
                <strong>Chuleta a la calima</strong>
            </div>
            <div class="h-carta">
                <?php
                $carta = [
                    'Pollos'    => ['Pollo en champiñones', 'Pollo en salsa de tocineta', 'Pollo en salsa de camarón', 'Pollo en salsa de mostaza', 'Cordón blue'],
                    'Carnes'    => ['Lomo al vino', 'Lomo en salsa de maracuyá', 'Lomo en salsa de tocineta', 'Lomo en salsa de frutas', 'Lomo en salsa de borojó'],
                    'Arroces'   => ['Arroz verde', 'Arroz con ajonjolí', 'Arroz con pimentón', 'Arroz con maíz'],
                    'Ensaladas' => ['Ensalada con vinagreta', 'Ensalada dulce', 'Chop suey'],
                ];
                foreach ($carta as $grupo => $platos): ?>
                    <div class="grupo aparecer">
                        <h3><?= e($grupo) ?></h3>
                        <ul><?php foreach ($platos as $plato): ?><li><?= e($plato) ?></li><?php endforeach; ?></ul>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ============ GALERÍA ============ -->
    <section class="h-sec h-galeria" id="galeria">
        <div class="h-wrap">
            <div class="cabeza aparecer">
                <div>
                    <p class="h-etiqueta">Galería</p>
                    <h2 class="h-titulo" style="margin-bottom:0">Así se ve <em>una celebración aquí.</em></h2>
                </div>
                <p class="h-texto" style="margin:0">Toca una foto para verla en grande. <?= count($galeria) ?> montajes reales.</p>
            </div>

            <div class="galeria" id="galeria-grid">
                <?php foreach ($galeria as $i => [$archivo, $alt, $pie]): ?>
                    <figure class="<?= $i >= $visibles ? 'oculta' : '' ?>" data-i="<?= $i ?>">
                        <img src="../Imagenes/<?= e($archivo) ?>" alt="<?= e($alt) ?>" loading="lazy">
                        <figcaption><?= e($pie) ?></figcaption>
                    </figure>
                <?php endforeach; ?>
            </div>

            <?php if (count($galeria) > $visibles): ?>
                <div class="galeria-mas">
                    <button type="button" class="btn btn-borde" id="ver-mas"
                            data-mas="Ver las <?= count($galeria) - $visibles ?> fotos restantes"
                            data-menos="Ver menos fotos">
                        <?= icono('mas') ?> <span>Ver las <?= count($galeria) - $visibles ?> fotos restantes</span>
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- ============ CÓMO RESERVAR ============ -->
    <section class="h-sec h-como" id="como">
        <div class="h-wrap">
            <div class="aparecer">
                <p class="h-etiqueta">Cómo reservar</p>
                <h2 class="h-titulo">Cuatro pasos, <em>sin papeleo.</em></h2>
            </div>
            <ol class="h-pasos">
                <li class="h-paso aparecer"><span class="num">1</span><h3>Crea tu cuenta</h3>
                    <p>Una sola vez. Con ella guardamos tus datos para no pedírtelos de nuevo.</p></li>
                <li class="h-paso aparecer"><span class="num">2</span><h3>Elige tu fecha</h3>
                    <p>Envías tu solicitud con el tipo de evento, el número de invitados y la fecha que tienes en mente.</p></li>
                <li class="h-paso aparecer"><span class="num">3</span><h3>Confirmamos tu evento</h3>
                    <p>Revisamos tu solicitud y te avisamos si la fecha queda reservada para ti.</p></li>
                <li class="h-paso aparecer"><span class="num">4</span><h3>Cotizamos contigo</h3>
                    <p>Con la fecha confirmada preparamos la cotización con los servicios que necesites.</p></li>
            </ol>
        </div>
    </section>

    <!-- ============ PREGUNTAS FRECUENTES ============ -->
    <section class="h-sec h-faq" id="preguntas">
        <div class="h-wrap">
            <div class="lado aparecer">
                <p class="h-etiqueta">Preguntas</p>
                <h2 class="h-titulo">Lo que más <em>nos preguntan.</em></h2>
                <p class="h-texto">¿No ves tu duda? Escríbenos y te respondemos.</p>
                <a class="btn btn-borde" target="_blank" rel="noopener" href="<?= $whatsapp ?>"><?= icono('whatsapp') ?> Escribir por WhatsApp</a>
            </div>
            <div class="aparecer">
                <details>
                    <summary>¿Necesito una cuenta para reservar?</summary>
                    <div class="resp"><p>Puedes explorar el salón, la galería y los servicios sin registrarte. Para solicitar una fecha
                        creas tu cuenta: así quedas registrado como cliente y no tienes que repetir tus datos cada vez.</p></div>
                </details>
                <details>
                    <summary>¿Con cuánta anticipación debo reservar?</summary>
                    <div class="resp"><p>Si tenemos el espacio, podemos organizar un evento con una semana de anticipación. Los eventos más
                        complejos pueden requerir hasta 8 meses de planeación, así que mientras antes nos escribas, mejor.</p></div>
                </details>
                <details>
                    <summary>¿Qué tipos de evento se celebran aquí?</summary>
                    <div class="resp"><p><?= e(implode(', ', array_diff(TIPOS_EVENTO, ['Otro']))) ?>. ¿No ves el tuyo? Escríbenos y lo conversamos.</p></div>
                </details>
                <details>
                    <summary>¿Cómo se confirma mi fecha?</summary>
                    <div class="resp"><p>Cuando envías la solicitud queda en estado <b>Pendiente</b>. Nosotros la revisamos y la aprobamos
                        o la rechazamos, y puedes ver el estado en tu panel. La fecha queda para el primer cliente que hace el anticipo.</p></div>
                </details>
                <details>
                    <summary>¿Cómo se cotiza mi evento?</summary>
                    <div class="resp"><p>Cotizamos por paquete, con un mínimo de 30 personas. Para darte una propuesta personalizada necesitamos
                        el tipo de evento, la cantidad de personas, la temática que imaginas y la fecha estimada. Una vez tu evento está
                        confirmado, armamos la cotización contigo.</p></div>
                </details>
                <details>
                    <summary>¿Cómo se paga?</summary>
                    <div class="resp"><p>Se hace un anticipo del 50 % para asegurar tu fecha, y el evento debe quedar pagado por completo
                        mínimo 8 días antes. Entregamos factura electrónica.</p></div>
                </details>
                <details>
                    <summary>¿Puedo conocer el salón antes de reservar?</summary>
                    <div class="resp"><p>Claro, la mayoría de nuestros clientes nos visita antes. Escríbenos por WhatsApp y agendamos tu visita.</p></div>
                </details>
                <details>
                    <summary>¿Atienden varios eventos el mismo día?</summary>
                    <div class="resp"><p>Sí. Contamos con la logística para atender varios eventos el mismo día en diferentes lugares.</p></div>
                </details>
                <details>
                    <summary>¿Qué pasa si tengo que cancelar?</summary>
                    <div class="resp"><p>Las solicitudes pendientes puedes cancelarlas desde tu panel. Si ya pagaste, el dinero no se devuelve,
                        pero tampoco lo pierdes: puedes mover la fecha o cambiar el tipo de evento, siempre que demuestres que fue por
                        un caso fortuito o de fuerza mayor.</p></div>
                </details>
            </div>
        </div>
    </section>

    <!-- ============ CONTACTO ============ -->
    <section class="h-sec h-contacto" id="contacto">
        <div class="h-wrap">
            <div class="aparecer">
                <p class="h-etiqueta">Contacto</p>
                <h2 class="h-titulo">Visítanos o <em>escríbenos.</em></h2>
                <ul class="h-datos">
                    <li><?= icono('ubicacion') ?> <span><?= e(NEGOCIO_DIRECCION) ?></span></li>
                    <li><?= icono('telefono') ?> <a href="tel:<?= preg_replace('/\s+/', '', NEGOCIO_TELEFONO) ?>"><?= e(NEGOCIO_TELEFONO) ?></a></li>
                    <?php if (NEGOCIO_CORREO !== ''): ?><li><?= icono('correo') ?> <a href="mailto:<?= e(NEGOCIO_CORREO) ?>"><?= e(NEGOCIO_CORREO) ?></a></li><?php endif; ?>
                    <li><?= icono('instagram') ?> <a target="_blank" rel="noopener" href="<?= e(REDES['instagram']) ?>">@salondeeventosmayraponce</a></li>
                    <li><?= icono('reloj') ?> <span><?= e(NEGOCIO_HORARIO) ?></span></li>
                </ul>
                <a class="btn" target="_blank" rel="noopener" href="<?= $whatsapp ?>"><?= icono('whatsapp') ?> Escríbenos por WhatsApp</a>
            </div>
            <div class="mapa aparecer">
                <iframe title="Ubicación del salón" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                        src="https://www.google.com/maps?q=<?= urlencode(NEGOCIO_DIRECCION) ?>&output=embed"></iframe>
            </div>
        </div>
    </section>

    <!-- ============ LLAMADO FINAL ============ -->
    <section class="h-final">
        <div class="h-wrap aparecer">
            <p class="h-etiqueta">Tu fecha</p>
            <h2>¿Ya tienes <em>un día en mente?</em></h2>
            <a class="btn" style="padding:16px 34px" href="SolicitarReserva.php"><?= icono('calendario') ?> Reservar mi fecha</a>
        </div>
    </section>
</main>

<dialog id="visor" class="visor">
    <img src="" alt="">
    <p class="visor-pie"><span class="visor-txt"></span><span class="visor-num"></span></p>
    <button type="button" class="visor-ant" aria-label="Foto anterior"><?= icono('izquierda') ?></button>
    <button type="button" class="visor-sig" aria-label="Foto siguiente"><?= icono('derecha') ?></button>
    <button type="button" class="visor-x" onclick="this.closest('dialog').close()" aria-label="Cerrar"><?= icono('x') ?></button>
</dialog>

<?php $pie = 'completo'; require "partials/pie.php"; ?>
