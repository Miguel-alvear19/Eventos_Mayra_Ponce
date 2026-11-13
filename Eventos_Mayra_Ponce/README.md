# Eventos Mayra Ponce

Sitio web del salón de eventos: página pública, registro e inicio de sesión, panel del cliente
(reservas y cotizaciones) y panel administrativo (calendario de disponibilidad, aprobar/rechazar, reportes).

Hecho en PHP + MySQL (mysqli), sin frameworks, con estructura MVC sencilla.

## Cómo ponerlo a funcionar (XAMPP)

1. Enciende **Apache** y **MySQL** en el panel de XAMPP.
2. Abre phpMyAdmin → pestaña **SQL** → pega y ejecuta `Database/eventos_mayra_ponce.sql`.
   Crea la base y las tablas que falten; si ya tenías `clientes`, no la toca.
3. Si tu MySQL tiene contraseña, cámbiala en `Config/Database.php`.
4. Entra a `http://localhost/Eventos_Mayra_Ponce-1/Eventos_Mayra_Ponce/`

**Administrador por defecto:** `admin@eventosmayraponce.com` / `Admin2026*`
(entra por el mismo login; cambia la contraseña en *Configuración*).

## Estructura

| Carpeta | Qué hay |
|---|---|
| `Config/` | Conexión (`Database.php`), funciones comunes (`helpers.php`), íconos SVG, **datos del negocio** (teléfono, correo, dirección, redes: al inicio de `helpers.php`) |
| `Models/` | Acceso a la base: `Cliente`, `Reserva`, `Cotizacion`, `Administrador`, `FechaBloqueada` (todos heredan de `Modelo`) |
| `controllers/` | Validan los formularios y llaman a los modelos |
| `Views/` | Páginas. `partials/` tiene el encabezado, el menú y el pie que comparten |
| `Styles/` | `main.css` (general y páginas públicas) y `panel.css` (paneles y reserva) |
| `Js/app.js` | Detalles de interfaz: ver contraseña, modales, galería, animaciones |
| `Imagenes/` | Fotos del salón |
| `Database/` | Script SQL |

## Páginas

- `Home.php` – inicio: tipos de evento, galería, servicios, el salón, testimonios, contacto y mapa.
- `Login.php` / `Registro.php` – un solo login para clientes y administradores.
- `SolicitarCotizacion.php` – funciona con o sin cuenta.
- `SolicitarReserva.php` – en 3 pasos; avisa si el día ya está reservado o en mantenimiento.
- `PanelCliente.php` – resumen, mis reservas (ver/cancelar), cotizaciones con la respuesta del salón, mi perfil.
- `PanelAdmin.php` – calendario, reservas, cotizaciones (responder con valor), clientes, reportes, configuración.

## Reglas del negocio

- El salón atiende **un evento aprobado por día**: no se puede aprobar ni solicitar una fecha ya aprobada.
- Los días marcados como mantenimiento no se pueden reservar.
- El cliente solo puede cancelar reservas futuras pendientes o confirmadas.
