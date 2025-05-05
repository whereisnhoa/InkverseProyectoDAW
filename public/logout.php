<?php
/**
 * Cierre de sesión
 */

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir configuración
require_once(__DIR__ . '/../includes/config.php');

// Limpiar todas las variables de sesión
$_SESSION = array();

// Si se está usando una cookie de sesión, eliminarla
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir la sesión
session_destroy();

// Iniciar una nueva sesión para el mensaje
session_start();
$_SESSION['mensaje'] = "Has cerrado sesión correctamente.";
$_SESSION['tipo_mensaje'] = "exito";

// Redirigir al inicio
header("Location: " . BASE_URL . "/public/index.php");
exit();
?>