
<?php

session_start();
include '/includes/conexion.php';

if (!isset($_SESSION['usuario_id']) || empty($_POST['contenido'])) 
{
    header("Location: /public/login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$obra_id = $_POST['obra_id'];
$contenido = trim($_POST['contenido']);

$sql = "INSERT INTO comentarios (usuario_id, obra_id, contenido) VALUES (?, ?, ?)";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("iis", $usuario_id, $obra_id, $contenido);
$stmt->execute();

header("Location: /public/obra.php?id=" . $obra_id);
exit();

?>