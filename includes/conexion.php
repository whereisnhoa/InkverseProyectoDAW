
<?php
// Incluir configuración si no está incluida aún
if (!defined('DB_HOST')) {
    require_once(__DIR__ . '/config.php');
}

$conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Verificar errores de conexión
if ($conexion->connect_error) {
    die("Error al conectar con la base de datos: " . $conexion->connect_error);
}

// Charset
$conexion->set_charset("utf8");
?>
