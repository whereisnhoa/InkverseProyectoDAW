
<?php
/**
 * Configuración global del proyecto
 */

// Ruta base del proyecto
$document_root = $_SERVER['DOCUMENT_ROOT'];
$project_folder = '/cozitas/proyecto';

define('BASE_PATH', $document_root . $project_folder); // Ruta física
define('BASE_URL', $project_folder);                   // URL relativa

// Configuración de base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'proyecto_db');

// Idioma
$idioma = $_GET['lang'] ?? 'es'; // Predeterminado: español

// Cargar archivo de idioma
$ruta_idioma = __DIR__ . "/languages/$idioma.php";
if (file_exists($ruta_idioma)) {
    $TEXTOS = include $ruta_idioma;
    
} else {
    $TEXTOS = include __DIR__ . "/languages/es.php"; 
}

// Zona horaria
date_default_timezone_set('Europe/Madrid');

// Errores en desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

