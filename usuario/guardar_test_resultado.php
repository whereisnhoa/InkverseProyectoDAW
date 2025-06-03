
<?php
session_start();
include '../includes/conexion.php';
if (!isset($_SESSION['usuario_id']) || empty($_POST['genero'])) exit;
$usuario_id = $_SESSION['usuario_id'];
$genero = $_POST['genero'];
$stmt = $conexion->prepare("UPDATE usuarios SET genero_favorito = ? WHERE id = ?");
$stmt->bind_param("si", $genero, $usuario_id);
$stmt->execute();
$stmt->close();
?>