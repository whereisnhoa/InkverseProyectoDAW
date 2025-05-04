<?php

include '/includes/conexion.php';

// variables de datos
$nombre = $_POST['nombre'];
$usuario = $_POST['usuario'];
$email = $_POST['email'];
$fecha_nacimiento = $_POST['fecha_nacimiento'];
$password = $_POST['password'];

// para encriptar las contraseñas y que no se garde tal cual en la base de datos
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// comprobar si el usuario o email ya existen
$sql_check = "SELECT * FROM usuarios WHERE email = ? OR usuario = ?";
$stmt_check = $conexion->prepare($sql_check);
$stmt_check->bind_param("ss", $email, $usuario);
$stmt_check->execute();
$result = $stmt_check->get_result();

if ($result->num_rows > 0) 
{
    echo "El correo o el nombre de usuario ya están en uso.";

}else 
    {
        // Insertar nuevo usuario
        $sql = "INSERT INTO usuarios (nombre, usuario, email, fecha_nacimiento, password) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sssss", $nombre, $usuario, $email, $fecha_nacimiento, $passwordHash);

        if ($stmt->execute()) {
            header("Location: /public/login.php");
        }else 
            {
                echo "Error al registrar: " . $stmt->error;
            }
    }

?>
