
<?php
/**
 * Configuración global del proyecto
 */

// Detectar automáticamente la ruta base del proyecto
$document_root = $_SERVER['DOCUMENT_ROOT'];
$project_folder = '/cozitas/proyecto';

// Ruta base del proyecto (ruta absoluta en el sistema de archivos)
define('BASE_PATH', $document_root . $project_folder);

// URL base del proyecto (para enlaces)
define('BASE_URL', $project_folder);

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'proyecto_db');


?>
