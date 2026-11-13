/* =====================================================
   JavaScript común a todas las páginas
   ===================================================== */

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
const aparecer = document.querySelectorAll('.aparecer');
if ('IntersectionObserver' in window && aparecer.length) {
    const obs = new IntersectionObserver(entradas => entradas.forEach(en => {
        if (en.isIntersecting) { en.target.classList.add('visible'); obs.unobserve(en.target); }
    }), { threshold: 0.15 });
    aparecer.forEach(el => obs.observe(el));
} else {
    aparecer.forEach(el => el.classList.add('visible'));
}

// Visor de la galería (en el inicio)
const visor = document.getElementById('visor');
if (visor) {
    document.querySelectorAll('.galeria figure').forEach(fig => fig.addEventListener('click', () => {
        const img = fig.querySelector('img');
        visor.querySelector('img').src = img.src;
        visor.querySelector('img').alt = img.alt;
        visor.showModal();
    }));
}

// Evita enviar dos veces el mismo formulario
document.querySelectorAll('form[data-un-envio]').forEach(f => f.addEventListener('submit', () => {
    const b = f.querySelector('[type=submit]');
    if (b) { setTimeout(() => b.disabled = true, 0); b.style.opacity = .7; }
}));
