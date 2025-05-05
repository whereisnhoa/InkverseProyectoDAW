<?php
/**
 * Archivo de redirección principal
 * Redirige a la carpeta public para mantener una estructura limpia
 */

// Incluir configuración si es necesario
if (file_exists(__DIR__ . '/includes/config.php')) {
    require_once(__DIR__ . '/includes/config.php');
    header("Location: " . BASE_URL . "/public/index.php");
} else {
    // Si no existe config.php, usar ruta relativa
    header("Location: public/index.php");
}
exit();
?>
