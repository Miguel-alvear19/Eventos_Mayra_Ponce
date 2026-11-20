/* =====================================================
   JavaScript común a todas las páginas
   ===================================================== */

const menosMovimiento = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

// Mostrar / ocultar contraseña. Uso: <button onclick="verClave('id-del-input', this)">
function verClave(id, boton) {
    const campo = document.getElementById(id);
    const oculto = campo.type === 'password';
    campo.type = oculto ? 'text' : 'password';
    if (boton) boton.setAttribute('aria-label', oculto ? 'Ocultar contraseña' : 'Mostrar contraseña');
}

// Abrir un <dialog> por id
function abrirModal(id) {
    const m = document.getElementById(id);
    if (m && m.showModal) m.showModal();
}

// Cerrar el modal al hacer clic fuera de él
document.querySelectorAll('dialog').forEach(d =>
    d.addEventListener('click', e => { if (e.target === d) d.close(); }));

// Animación suave de aparición al hacer scroll
const aparecer = document.querySelectorAll('.aparecer, .revela');
if ('IntersectionObserver' in window && aparecer.length) {
    const obs = new IntersectionObserver(entradas => entradas.forEach(en => {
        if (en.isIntersecting) { en.target.classList.add('visible'); obs.unobserve(en.target); }
    }), { threshold: 0.15 });
    aparecer.forEach(el => obs.observe(el));
} else {
    aparecer.forEach(el => el.classList.add('visible'));
}

// Evita enviar dos veces el mismo formulario
document.querySelectorAll('form[data-un-envio]').forEach(f => f.addEventListener('submit', () => {
    const b = f.querySelector('[type=submit]');
    if (b) { setTimeout(() => b.disabled = true, 0); b.style.opacity = .7; }
}));

/* =====================================================
   GALERÍA DEL INICIO
   ===================================================== */
const grid = document.getElementById('galeria-grid');
if (grid) {
    const fotos = [...grid.querySelectorAll('figure')];

    // Aparición escalonada de cada foto
    const mostrar = fig => fig.classList.add('visible');
    if ('IntersectionObserver' in window && !menosMovimiento) {
        const ojo = new IntersectionObserver((entradas, obs) => {
            entradas.filter(en => en.isIntersecting).forEach((en, i) => {
                setTimeout(() => mostrar(en.target), i * 80);
                obs.unobserve(en.target);
            });
        }, { threshold: 0.1 });
        fotos.forEach(f => ojo.observe(f));
    } else {
        fotos.forEach(mostrar);
    }

    // Botón "Ver más fotos"
    const btnMas = document.getElementById('ver-mas');
    if (btnMas) {
        const etiqueta = btnMas.querySelector('span');
        btnMas.addEventListener('click', () => {
            const abriendo = !btnMas.classList.contains('abierto');
            btnMas.classList.toggle('abierto', abriendo);
            etiqueta.textContent = btnMas.dataset[abriendo ? 'menos' : 'mas'];
            fotos.forEach((f, i) => {
                if (i < 12) return;
                f.classList.toggle('oculta', !abriendo);
                if (abriendo) setTimeout(() => mostrar(f), (i - 12) * 60);
            });
            if (!abriendo) fotos[11].scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
    }

    // Visor a pantalla completa, con flechas y teclado
    const visor = document.getElementById('visor');
    if (visor) {
        const imgVisor = visor.querySelector('img');
        const txt = visor.querySelector('.visor-txt');
        const num = visor.querySelector('.visor-num');
        let actual = 0;

        const abrir = i => {
            const visibles = fotos.filter(f => !f.classList.contains('oculta'));
            if (!visibles.length) return;
            actual = (i + visibles.length) % visibles.length;
            const fig = visibles[actual];
            const img = fig.querySelector('img');
            imgVisor.src = img.src;
            imgVisor.alt = img.alt;
            txt.textContent = fig.querySelector('figcaption').textContent;
            num.textContent = (actual + 1) + ' / ' + visibles.length;
            if (!visor.open) visor.showModal();
        };

        fotos.forEach(fig => fig.addEventListener('click', () => {
            abrir(fotos.filter(f => !f.classList.contains('oculta')).indexOf(fig));
        }));
        visor.querySelector('.visor-ant').addEventListener('click', e => { e.stopPropagation(); abrir(actual - 1); });
        visor.querySelector('.visor-sig').addEventListener('click', e => { e.stopPropagation(); abrir(actual + 1); });
        visor.addEventListener('keydown', e => {
            if (e.key === 'ArrowLeft')  { e.preventDefault(); abrir(actual - 1); }
            if (e.key === 'ArrowRight') { e.preventDefault(); abrir(actual + 1); }
        });
    }
}

/* =====================================================
   VIDEO DEL INICIO (un solo video con tres partes)
   ===================================================== */
const reel = document.getElementById('reel');
if (reel) {
    const video  = document.getElementById('reel-video');
    const segs   = [...reel.querySelectorAll('.reel-segs button')];
    const elNum  = reel.querySelector('.reel-num');
    const elTxt  = reel.querySelector('.reel-txt');
    const fin    = parseFloat(reel.dataset.fin) || 0;
    const inicios = segs.map(b => parseFloat(b.dataset.inicio));
    let parte = -1;

    const finDe = i => (i + 1 < inicios.length ? inicios[i + 1] : fin);

    const pintar = () => {
        const t = video.currentTime;
        let activa = 0;
        inicios.forEach((ini, i) => { if (t >= ini - 0.05) activa = i; });

        segs.forEach((b, i) => {
            const ini = inicios[i], largo = finDe(i) - ini;
            const avance = i < activa ? 1 : i > activa ? 0 : Math.min(1, Math.max(0, (t - ini) / largo));
            b.querySelector('i').style.setProperty('--avance', avance.toFixed(3));
            b.setAttribute('aria-selected', i === activa);
        });

        if (activa !== parte) {
            parte = activa;
            elNum.textContent = String(activa + 1).padStart(2, '0');
            elTxt.innerHTML = '';
            elTxt.append(segs[activa].dataset.nombre);
            const em = document.createElement('em');
            em.textContent = segs[activa].dataset.desc;
            elTxt.append(em);
        }
    };

    const irA = i => {
        const n = (i + inicios.length) % inicios.length;
        video.currentTime = inicios[n] + 0.05;
        pintar();
        video.play().catch(() => {});
    };

    video.addEventListener('timeupdate', pintar);
    video.addEventListener('loadedmetadata', pintar);
    segs.forEach((b, i) => b.addEventListener('click', () => irA(i)));
    reel.querySelector('.reel-ant').addEventListener('click', () => irA(parte - 1));
    reel.querySelector('.reel-sig').addEventListener('click', () => irA(parte + 1));

    // Reproducción automática: el usuario no puede pausarlo.
    // Solo se detiene solo cuando sale de la pantalla (ahorra datos) y vuelve a correr al verse.
    video.controls = false;
    const reproducir = () => video.play().catch(() => {});
    if ('IntersectionObserver' in window) {
        new IntersectionObserver(entradas => entradas.forEach(en => {
            en.isIntersecting ? reproducir() : video.pause();
        }), { threshold: 0.25 }).observe(reel);
    } else {
        reproducir();
    }
    // Si el navegador lo pausa por su cuenta (p. ej. al volver a la pestaña), lo reanudamos
    document.addEventListener('visibilitychange', () => { if (!document.hidden) reproducir(); });

    // Si el servidor no permite saltar dentro del video, escondemos flechas y partes
    const revisarSaltos = () => {
        const puede = video.seekable.length > 0 && video.seekable.end(0) > 1;
        reel.classList.toggle('sin-salto', !puede);
    };
    video.addEventListener('canplay', revisarSaltos);
    video.addEventListener('progress', revisarSaltos);
}

/* =====================================================
   INICIO: menú activo y movimiento suave de las fotos
   ===================================================== */
const enlacesMenu = [...document.querySelectorAll('.nav-links a[href*="#"]')];
if ('IntersectionObserver' in window && enlacesMenu.length && document.body.classList.contains('home')) {
    const porId = new Map(enlacesMenu.map(a => [a.hash.slice(1), a]));
    const espia = new IntersectionObserver(entradas => entradas.forEach(en => {
        if (!en.isIntersecting) return;
        enlacesMenu.forEach(a => a.classList.remove('activo'));
        const enlace = porId.get(en.target.id);
        if (enlace) enlace.classList.add('activo');
    }), { rootMargin: '-45% 0px -50% 0px' });
    porId.forEach((_, id) => { const seccion = document.getElementById(id); if (seccion) espia.observe(seccion); });
}

// Las fotos de la portada se mueven un poco más lento que el resto
const conProfundidad = [...document.querySelectorAll('[data-parallax]')];
if (conProfundidad.length && !menosMovimiento) {
    let pendiente = false;
    const mover = () => {
        pendiente = false;
        const y = window.scrollY;
        if (y > 900) return;
        conProfundidad.forEach(el => {
            const fuerza = parseFloat(el.dataset.parallax) / 100;
            el.style.transform = 'translate3d(0,' + (y * fuerza).toFixed(1) + 'px,0)';
        });
    };
    window.addEventListener('scroll', () => {
        if (!pendiente) { pendiente = true; requestAnimationFrame(mover); }
    }, { passive: true });
    mover();
}
