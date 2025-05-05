
<?php
/**
 * Conexión a la base de datos
 * Este archivo establece la conexión con la base de datos MySQL
 */

// Incluir archivo de configuración si no está incluido
if (!defined('DB_HOST')) 
{
    require_once(__DIR__ . '/config.php');
}

// Conexión a la base de datos
$conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Comprobar si hay error en la conexión
if ($conexion->connect_error) 
{
    die("Error de conexión: " . $conexion->connect_error);
}

// Establecer charset a utf8
$conexion->set_charset("utf8");

?>