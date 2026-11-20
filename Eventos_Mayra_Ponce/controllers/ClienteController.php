<?php

require_once dirname(__DIR__) . "/Config/helpers.php";
require_once dirname(__DIR__) . "/Models/Cliente.php";
require_once dirname(__DIR__) . "/Models/Administrador.php";

class ClienteController {

    /*
    ==========================
    REGISTRAR
    Devuelve una lista de errores; si todo sale bien inicia sesión y va al panel.
    ==========================
    */
    public function registrarCliente() {

        $nombreCompleto = valorPost('nombre');
        $correo    = strtolower(valorPost('correo'));
        $telefono  = valorPost('telefono');
        $password  = $_POST['password'] ?? '';
        $confirmar = $_POST['confirmar'] ?? '';

        $errores = [];
        if (mb_strlen($nombreCompleto) < 3)                 $errores[] = 'Escribe tu nombre completo.';
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL))    $errores[] = 'El correo no es válido.';
        if ($telefono !== '' && !preg_match('/^[0-9 +()-]{7,20}$/', $telefono)) $errores[] = 'El teléfono no es válido.';
        if (strlen($password) < 6)                          $errores[] = 'La contraseña debe tener al menos 6 caracteres.';
        if ($password !== $confirmar)                       $errores[] = 'Las contraseñas no coinciden.';
        if (!isset($_POST['terminos']))                     $errores[] = 'Debes aceptar los términos y condiciones.';

        $cliente = new Cliente();

        if (!$errores && $cliente->existeCorreo($correo)) {
            $errores[] = 'Ya existe una cuenta con ese correo. ¿Quieres iniciar sesión?';
        }
        if ($errores) return $errores;

        // El formulario pide "Nombre completo": lo partimos en nombres y apellidos
        $partes    = preg_split('/\s+/', $nombreCompleto, 2);
        $nombres   = $partes[0];
        $apellidos = $partes[1] ?? '';

        if (!$cliente->registrar($nombres, $apellidos, $correo, $telefono, $password)) {
            return ['No se pudo registrar el cliente. Intenta de nuevo.'];
        }

        $this->iniciarSesionCliente($cliente->buscarPorCorreo($correo));
        flash('exito', '¡Tu cuenta fue creada! Bienvenida(o) a Eventos Mayra Ponce.');
        // Si venía de "Reservar mi fecha", sigue allí sin volver a escribir nada
        $destino = $_SESSION['volver_a'] ?? 'mi-panel';
        unset($_SESSION['volver_a']);
        redirigir($destino);
    }

    /*
    ==========================
    LOGIN (clientes y administradores usan el mismo formulario)
    Devuelve un mensaje de error; si todo sale bien redirige.
    ==========================
    */
    public function loginCliente() {

        $correo   = strtolower(valorPost('correo'));
        $password = $_POST['password'] ?? '';

        if ($correo === '' || $password === '') {
            return 'Escribe tu correo y tu contraseña.';
        }

        // 1. ¿Es administrador?
        $admin = (new Administrador())->buscarPorCorreo($correo);
        if ($admin && password_verify($password, $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['id_admin']     = $admin['id_admin'];
            $_SESSION['nombre_admin'] = $admin['nombre'];
            $this->recordar();
            redirigir('admin');
        }

        // 2. ¿Es cliente?
        $datos = (new Cliente())->buscarPorCorreo($correo);
        if ($datos && password_verify($password, $datos['password'])) {
            $this->iniciarSesionCliente($datos);
            $this->recordar();
            // Si venía de una página que pedía sesión (p. ej. Solicitar reserva), vuelve allá
            $destino = $_SESSION['volver_a'] ?? 'mi-panel';
            unset($_SESSION['volver_a']);
            redirigir($destino);
        }

        // Mismo mensaje en ambos casos para no revelar qué correos existen
        return 'Correo o contraseña incorrectos.';
    }

    private function iniciarSesionCliente($datos) {
        session_regenerate_id(true);
        $_SESSION['id_cliente'] = $datos['id_cliente'];
        $_SESSION['nombre']     = $datos['nombres'];
        $_SESSION['correo']     = $datos['correo'];
    }

    // "Recordarme": la cookie de sesión dura 30 días en vez de cerrarse con el navegador
    private function recordar() {
        if (isset($_POST['recordar'])) {
            $p = session_get_cookie_params();
            setcookie(session_name(), session_id(), time() + 60 * 60 * 24 * 30, $p['path'], $p['domain'], $p['secure'], true);
        }
    }

    /*
    ==========================
    PERFIL
    ==========================
    */
    public function actualizarPerfil() {
        $nombres   = valorPost('nombres');
        $apellidos = valorPost('apellidos');
        $telefono  = valorPost('telefono');

        if (mb_strlen($nombres) < 2) {
            flash('error', 'El nombre no puede quedar vacío.');
        } else {
            (new Cliente())->actualizarPerfil($_SESSION['id_cliente'], $nombres, $apellidos, $telefono);
            $_SESSION['nombre'] = $nombres;
            flash('exito', 'Tus datos se actualizaron.');
        }
        redirigir('mi-panel?seccion=perfil');
    }

    public function cambiarPassword() {
        $cliente = new Cliente();
        $datos   = $cliente->obtenerPorId($_SESSION['id_cliente']);
        $actual  = $_POST['actual'] ?? '';
        $nueva   = $_POST['nueva'] ?? '';

        if (!password_verify($actual, $datos['password'])) {
            flash('error', 'La contraseña actual no es correcta.');
        } elseif (strlen($nueva) < 6 || $nueva !== ($_POST['confirmar'] ?? '')) {
            flash('error', 'La nueva contraseña debe tener 6 caracteres o más y coincidir con la confirmación.');
        } else {
            $cliente->cambiarPassword($_SESSION['id_cliente'], $nueva);
            flash('exito', 'Tu contraseña se cambió correctamente.');
        }
        redirigir('mi-panel?seccion=perfil');
    }
}
