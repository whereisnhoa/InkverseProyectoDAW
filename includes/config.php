
<?php

/**
 * Configuración global del proyecto
 * Este archivo define constantes y configuraciones utilizadas en todo el proyecto
 */

// Detectar automáticamente la ruta base del proyecto
$document_root = $_SERVER['DOCUMENT_ROOT'];
$project_folder = '/proyecto';

// Ruta base del proyecto
define('BASE_PATH', $document_root . $project_folder);

// URL base del proyecto (para enlaces)
define('BASE_URL', $project_folder);

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'proyecto_db');

// Configuración de sesión
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); 

// Zona horaria
date_default_timezone_set('Europe/Madrid');

// Configuración de errores 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

?>