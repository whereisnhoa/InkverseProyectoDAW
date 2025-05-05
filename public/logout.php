
<?php

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) 
{
    session_start();
}

// Limpiar todas las variables de sesión
$_SESSION = array();

// Si se está usando una cookie de sesión, eliminarla
if (ini_get("session.use_cookies")) 
{
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir la sesión
session_destroy();

// Redirigir al inicio con mensaje de éxito
session_start();
$_SESSION['mensaje'] = "Has cerrado sesión correctamente.";
$_SESSION['tipo_mensaje'] = "exito";

header("Location: /public/index.php");
exit();

?>