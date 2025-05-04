
<?php

// iniciar sesión si aún no está iniciada
if (session_status() === PHP_SESSION_NONE) 
{
    session_start();
}

// mostrar mensajes almacenados en sesión (éxito o error)
function mostrarMensaje() 
{
    if (isset($_SESSION['mensaje'])) {
        echo "<div class='mensaje'>" . $_SESSION['mensaje'] . "</div>";
        unset($_SESSION['mensaje']);
    }
}

// redirigir con mensaje
function redirigirConMensaje($url, $mensaje) 
{
    $_SESSION['mensaje'] = $mensaje;
    header("Location: $url");
    exit;
}

// validar edad mínima
function esMayorEdad($fechaNacimiento) 
{
    $hoy = new DateTime();
    $fecha = new DateTime($fechaNacimiento);
    $edad = $hoy->diff($fecha)->y;
    return $edad >= 18;
}

// comprobar si el usuario está logueado
function estaLogueado() 
{
    return isset($_SESSION['usuario']);
}

// proteger páginas privadas
function requerirLogin() 
{
    if (!estaLogueado()) 
    {
        redirigirConMensaje('login.php', 'Debes iniciar sesión para acceder.');
    }
}
