<?php
/**
 * Funciones útiles para el proyecto
 * Este archivo contiene funciones auxiliares utilizadas en todo el sitio
 */

// Iniciar sesión si aún no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Mostrar mensajes almacenados en sesión (éxito o error)
function mostrarMensaje() {
    if (isset($_SESSION['mensaje'])) {
        $tipo = isset($_SESSION['tipo_mensaje']) ? $_SESSION['tipo_mensaje'] : 'info';
        echo "<div class='alerta alerta-{$tipo}'>" . $_SESSION['mensaje'] . "</div>";
        unset($_SESSION['mensaje']);
        unset($_SESSION['tipo_mensaje']);
    }
}

// Redirigir con mensaje
function redirigirConMensaje($url, $mensaje, $tipo = 'info') {
    $_SESSION['mensaje'] = $mensaje;
    $_SESSION['tipo_mensaje'] = $tipo;
    header("Location: $url");
    exit;
}

// Validar edad mínima
function esMayorEdad($fechaNacimiento) {
    $hoy = new DateTime();
    $fecha = new DateTime($fechaNacimiento);
    $edad = $hoy->diff($fecha)->y;
    return $edad >= 18;
}

// Comprobar si el usuario está logueado
function estaLogueado() {
    return isset($_SESSION['usuario_id']);
}

// Proteger páginas privadas
function requerirLogin() {
    if (!estaLogueado()) {
        redirigirConMensaje(BASE_URL . '/public/login.php', 'Debes iniciar sesión para acceder.', 'error');
    }
}

// Limpiar datos de entrada
function limpiarDato($dato) {
    $dato = trim($dato);
    $dato = stripslashes($dato);
    $dato = htmlspecialchars($dato);
    return $dato;
}

// Generar token CSRF
function generarTokenCSRF() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verificar token CSRF
function verificarTokenCSRF($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        redirigirConMensaje(BASE_URL . '/public/index.php', 'Error de validación. Por favor, intenta nuevamente.', 'error');
    }
}
?>