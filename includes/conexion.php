
<?php

// conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "miproyecto");

// comprobar si hay error en la conexión
if ($conexion->connect_error) 
{
    die("Error de conexión: " . $conexion->connect_error);
}

?>
