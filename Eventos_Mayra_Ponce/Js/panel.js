// DEMO: datos de ejemplo en memoria (se pierden al recargar la página)
const reservas = [
    { fecha: '25 Abr 2026', evento: 'Boda',       estado: 'Pendiente'  },
    { fecha: '30 May 2026', evento: 'Cumpleaños', estado: 'Confirmada' }
];
const cotizaciones = [
    { fecha: '25 Abr 2026', evento: 'Boda', estado: 'Pendiente' }
];

// Reservas creadas desde SolicitarReserva.php (guardadas mientras dure la sesión del navegador)
try { (JSON.parse(sessionStorage.getItem('reservasNuevas')) || []).forEach(r => reservas.push(r)); } catch (e) {}

// Muestra una sección y oculta las demás
function mostrar(nombre) {
    document.querySelectorAll('.seccion').forEach(s =>
        s.classList.toggle('visible', s.id === nombre));
    document.querySelectorAll('.menu a[data-ir]').forEach(a =>
        a.classList.toggle('activo', a.dataset.ir === nombre));
}

function estado(texto) {
    return '<span class="estado ' + texto + '">' + texto + '</span>';
}

// Dibuja tablas y contadores a partir de los arreglos
function pintar() {
    const filaProxima = (r, i) =>
        '<tr><td>' + r.fecha + '</td><td>' + r.evento + '</td><td>' + estado(r.estado) +
        '</td><td><button class="btn btn-claro" onclick="verReserva(' + i + ')">Ver</button></td></tr>';

    const filaReserva = (r, i) =>
        '<tr><td>' + r.fecha + '</td><td>' + r.evento + '</td><td>' + estado(r.estado) + '</td><td>' +
        '<button class="btn btn-claro" onclick="verReserva(' + i + ')">Ver</button> ' +
        (r.estado === 'Cancelada' ? '' :
        '<button class="btn" onclick="cancelar(' + i + ')">Cancelar</button>') + '</td></tr>';

    document.getElementById('tabla-proximas').innerHTML = reservas.map(filaProxima).join('');
    document.getElementById('tabla-reservas').innerHTML = reservas.map(filaReserva).join('');
    document.getElementById('tabla-cotizaciones').innerHTML = cotizaciones.map(c =>
        '<tr><td>' + c.fecha + '</td><td>' + c.evento + '</td><td>' + estado(c.estado) + '</td></tr>').join('');

    const activas = reservas.filter(r => r.estado !== 'Cancelada').length;
    document.getElementById('n-reservas').textContent = activas + ' activas';
    document.getElementById('n-cotizaciones').textContent = cotizaciones.length + ' pendiente';
}

function verReserva(i) {
    const r = reservas[i];
    document.getElementById('detalle').innerHTML =
        '<h3>' + r.evento + '</h3><p>Fecha: ' + r.fecha + '</p><p>Estado: ' + estado(r.estado) + '</p>';
    document.getElementById('modal').showModal();
}

function cancelar(i) {
    if (confirm('¿Quieres cancelar esta reserva?')) {
        reservas[i].estado = 'Cancelada';
        pintar();
    }
}

pintar();
const inicial = location.hash.slice(1);
mostrar(document.getElementById(inicial) ? inicial : 'inicio');