
<?php

// conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "proyecto_db");

// comprobar si hay error en la conexión
if ($conexion->connect_error) 
{
    die("Error de conexión: " . $conexion->connect_error);
}

?>
