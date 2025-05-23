
<?php
/**
 * Conexión a la base de datos
 * Este archivo establece la conexión con la base de datos MySQL
 */

// Determinar la ruta base del proyecto
$document_root = $_SERVER['DOCUMENT_ROOT'];
$current_path = dirname(__FILE__);
$project_path = str_replace('\\', '/', substr($current_path, 0, strrpos($current_path, '/includes')));
$relative_path = str_replace(str_replace('\\', '/', $document_root), '', $project_path);

// Definir constantes si no están definidas
if (!defined('BASE_URL')) {
    define('BASE_URL', $relative_path);
}

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'proyecto_db');

// Conexión a la base de datos
try {
    $conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // Comprobar si hay error en la conexión
    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }

    // Establecer charset a utf8
    $conexion->set_charset("utf8");
} catch (Exception $e) {
    die("Error al conectar con la base de datos: " . $e->getMessage());
}
?>

