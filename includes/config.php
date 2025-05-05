
<?php
// Configuración general del proyecto

// Rutas
define('BASE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/proyectosPhp/proyecto');
define('BASE_URL', '/proyectosPhp/proyecto');

// Base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); 
define('DB_NAME', 'proyecto_db');

// Zona horaria y errores
date_default_timezone_set('Europe/Madrid');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
