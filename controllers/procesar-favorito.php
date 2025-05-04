<?php
session_start();
include '../includes/conexion.php';

if (!isset($_SESSION['usuario_id']) || !isset($_POST['contenido_id']) || !isset($_POST['tipo_contenido']) || !isset($_POST['accion'])) {
    header("Location: ../public/login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$contenido_id = $_POST['contenido_id'];
$tipo_contenido = $_POST['tipo_contenido']; // 'libro' o 'comic'
$accion = $_POST['accion'];

if ($accion === "agregar") {
    $sql = "INSERT IGNORE INTO favoritos (usuario_id, tipo_contenido, contenido_id) VALUES (?, ?, ?)";
} elseif ($accion === "quitar") {
    $sql = "DELETE FROM favoritos WHERE usuario_id = ? AND tipo_contenido = ? AND contenido_id = ?";
} else {
    exit("Acción no válida.");
}

$stmt = $conexion->prepare($sql);
$stmt->bind_param("isi", $usuario_id, $tipo_contenido, $contenido_id);
$stmt->execute();

// Volver a la obra
header("Location: ../public/obra.php?id=" . $contenido_id . "&tipo=" . $tipo_contenido);
exit();
?>