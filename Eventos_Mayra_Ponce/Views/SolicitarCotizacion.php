<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Solicitar cotización | Eventos Mayra Ponce</title>
    <link rel="stylesheet" href="../Styles/auth.css">
</head>
<body>

<header class="nav">
    <div class="marca">Eventos<br>Mayra Ponce</div>
    <ul>
        <li><a href="PanelCliente.php">Mi panel</a></li>
        <li><a href="#">Servicios</a></li>
        <li><a href="#">Galería</a></li>
        <li><a href="#">El salón</a></li>
        <li><a href="#">Contacto</a></li>
    </ul>
</header>

<section class="encabezado-pagina">
    <h1>Solicitar cotización</h1>
    <p>Cuéntanos qué necesitas y te asesoramos</p>
</section>

<main class="escena cot">

    <div class="foto" style="border-radius:16px"></div>

    <div>
        <form id="formulario" class="form-simple">

            <label>Tipo de evento</label>
            <div class="campo"><select required>
                <option value="">Selecciona una opción</option>
                <option>Boda</option><option>Cumpleaños</option>
                <option>Evento social</option><option>Evento empresarial</option>
            </select></div>

            <label>Fecha tentativa</label>
            <div class="campo"><span>📅</span><input type="date" required></div>

            <label>Número de invitados</label>
            <div class="campo"><span>👥</span><input type="number" min="1" placeholder="Ej. 50" required></div>

            <label>Servicios de interés</label>
            <div class="campo"><select>
                <option value="">Selecciona servicios</option>
                <option>Decoración</option><option>Mobiliario</option>
                <option>Catering</option><option>Logística completa</option>
            </select></div>

            <label>Presupuesto aproximado</label>
            <div class="campo"><select>
                <option value="">Selecciona un rango</option>
                <option>Hasta $2.000.000</option><option>$2.000.000 - $5.000.000</option>
                <option>Más de $5.000.000</option>
            </select></div>

            <label>Cuéntanos qué tienes en mente</label>
            <textarea placeholder="Escribe aquí tus ideas..."></textarea>

            <button type="submit" class="btn-principal">Enviar solicitud ➤</button>
        </form>

        <!-- DEMO: se muestra al enviar; mañana se guardará en la base de datos -->
        <div id="ok" class="mensaje-ok" style="display:none">
            <h2>¡Solicitud enviada!</h2>
            <p>Revisaremos tu solicitud y te contactaremos pronto con tu cotización.</p>
            <a class="btn-principal" style="display:inline-block;width:auto;padding:12px 28px;text-decoration:none" href="PanelCliente.php">Volver a mi panel</a>
        </div>
    </div>

</main>

<footer class="pie">Eventos inolvidables, hechos a tu medida</footer>

<script>
document.getElementById('formulario').addEventListener('submit', function (e) {
    e.preventDefault();
    this.style.display = 'none';
    document.getElementById('ok').style.display = 'block';
});
</script>
</body>
</html>