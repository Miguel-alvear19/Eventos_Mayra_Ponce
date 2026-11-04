<?php

require_once dirname(__DIR__) . "/Models/Cliente.php";

class ClienteController {

    /*
    ==========================
    REGISTRAR
    ==========================
    */
    public function registrarCliente() {

    $nombres = $_POST['nombres'];
    $apellidos = $_POST['apellidos'];
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'];
    $password = $_POST['password'];

    $cliente = new Cliente();

    $resultado = $cliente->registrar(
        $nombres,
        $apellidos,
        $correo,
        $telefono,
        $password
    );

    if($resultado){
        echo "Cliente registrado correctamente";
    }else{
        echo "Error al registrar cliente";
    }
}

    /*
    ==========================
    LOGIN
    ==========================
    */
    public function loginCliente() {

    $correo = $_POST['correo'];
    $password = $_POST['password'];

    $cliente = new Cliente();

    $resultado = $cliente->buscarPorCorreo($correo);

    if($resultado->num_rows > 0){

        $datos = $resultado->fetch_assoc();

        if(password_verify($password, $datos['password'])){

        session_start();

        $_SESSION['id_cliente'] = $datos['id_cliente'];
        $_SESSION['nombre'] = $datos['nombres'];
        $_SESSION['correo'] = $datos['correo'];

        header("Location: ../Views/Home.php");
        exit();

        }else{

            echo "Contraseña incorrecta";

        }

    }else{

        echo "El usuario no existe";

    }
}
}