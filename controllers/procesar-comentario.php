
<?php
session_start();
include '../includes/conexion.php';

if (!isset($_SESSION['usuario_id']) || empty($_POST['contenido']) || !isset($_POST['contenido_id']) || !isset($_POST['tipo_contenido'])) {
    header("Location: ../public/login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$contenido_id = $_POST['contenido_id'];
$tipo_contenido = $_POST['tipo_contenido']; // 'libro' o 'comic'
$contenido = trim($_POST['contenido']);

$sql = "INSERT INTO comentarios (usuario_id, contenido, tipo_contenido, contenido_id) VALUES (?, ?, ?, ?)";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("issi", $usuario_id, $contenido, $tipo_contenido, $contenido_id);
$stmt->execute();

header("Location: ../public/obra.php?id=" . $contenido_id . "&tipo=" . $tipo_contenido);
exit();
?>