
<?php
/**
 * Este archivo contiene funciones utilizadas en todo el proyecto
 */

// iniciar sesión si aún no está iniciada
if (session_status() === PHP_SESSION_NONE) 
{
    session_start();
}

// mostrar mensajes almacenados en sesión (éxito o error)
function mostrarMensaje() 
{
    if (isset($_SESSION['mensaje'])) {
        $tipo = isset($_SESSION['tipo_mensaje']) ? $_SESSION['tipo_mensaje'] : 'info';
        echo "<div class='alerta alerta-{$tipo}'>" . $_SESSION['mensaje'] . "</div>";
        unset($_SESSION['mensaje']);
        unset($_SESSION['tipo_mensaje']);
    }
}

// redirigir con mensaje
function redirigirConMensaje($url, $mensaje, $tipo = 'info') 
{
    $_SESSION['mensaje'] = $mensaje;
    $_SESSION['tipo_mensaje'] = $tipo;
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
    return isset($_SESSION['usuario_id']);
}

// proteger páginas privadas
function requerirLogin() 
{
    if (!estaLogueado()) 
    {
        redirigirConMensaje(BASE_URL . '/public/login.php', 'Debes iniciar sesión para acceder.', 'error');
    }
}

// limpiar datos de entrada
function limpiarDato($dato) 
{
    $dato = trim($dato);
    $dato = stripslashes($dato);
    $dato = htmlspecialchars($dato);
    return $dato;
}

// generar token CSRF
function generarTokenCSRF() 
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// verificar token CSRF
function verificarTokenCSRF($token) 
{
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        redirigirConMensaje(BASE_URL . '/public/index.php', 'Error de validación. Por favor, intenta nuevamente.', 'error');
    }
}

?>
