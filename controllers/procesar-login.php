
<?php

session_start();

include '../includes/conexion.php';

$usuario = $_POST['usuario'];
$password = $_POST['password'];

// buscar usuario por nombre de usuario o email
$sql = "SELECT * FROM usuarios WHERE usuario = ? OR email = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("ss", $usuario, $usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) 
{

    $fila = $result->fetch_assoc();

    // verificar la contraseña
    if (password_verify($password, $fila['password'])) 
    {
        // guardar información en sesión
        $_SESSION['usuario_id'] = $fila['id'];
        $_SESSION['usuario'] = $fila['usuario'];
        $_SESSION['edad'] = date_diff(date_create($fila['fecha_nacimiento']), date_create('today'))->y;

        // redirigir al perfil
        header("Location: ../usuarios/perfil.php");
        exit();

    }else
        {
            echo "Contraseña incorrecta.";
        }

}else 
    {
     echo "Usuario no encontrado.";
    }

?>
