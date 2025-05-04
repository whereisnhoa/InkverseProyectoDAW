
<?php

session_start();
include '/includes/conexion.php';

if (!isset($_SESSION['usuario_id']) || !isset($_POST['obra_id']) || !isset($_POST['accion'])) 
{
    header("Location: /public/login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$obra_id = $_POST['obra_id'];
$accion = $_POST['accion'];

if ($accion === "agregar") {
    $sql = "INSERT IGNORE INTO favoritos (usuario_id, obra_id) VALUES (?, ?)";
} elseif ($accion === "quitar") {
    $sql = "DELETE FROM favoritos WHERE usuario_id = ? AND obra_id = ?";
} else {
    exit("Acción no válida.");
}

$stmt = $conexion->prepare($sql);
$stmt->bind_param("ii", $usuario_id, $obra_id);
$stmt->execute();

// volver a la obra
header("Location: /public/obra.php?id=" . $obra_id);
exit();

?>