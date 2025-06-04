<?php
require_once('../includes/config.php');
require_once('../includes/conexion.php');
require_once('../includes/funciones.php');
session_start();

if(!isset($_SESSION['usuario_id'])){
    header("Location: ".BASE_URL."/public/login.php");
    exit;
}
if (!isset($_POST['csrf_token']) || !verificarTokenCSRF($_POST['csrf_token'])) {
    header("Location: ../comunidad/nuevo_tema.php?msg=csrf");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$categoria_id = intval($_POST['categoria']);
$titulo = limpiarDatos($_POST['titulo']);
$contenido = limpiarDatos($_POST['mensaje']);

if (!$categoria_id || empty($titulo) || empty($contenido)) {
    header("Location: ../comunidad/nuevo_tema.php?msg=datos");
    exit;
}

$stmt = $conexion->prepare("INSERT INTO temas_foro (categoria_id, usuario_id, titulo, contenido, fecha, ultima_actividad) VALUES (?, ?, ?, ?, NOW(), NOW())");
$stmt->bind_param("iiss", $categoria_id, $usuario_id, $titulo, $contenido);
if ($stmt->execute()) {
    $tema_id = $stmt->insert_id;
    header("Location: ../comunidad/tema.php?id=$tema_id&msg=ok");
    exit;
} else {
    header("Location: ../comunidad/nuevo_tema.php?msg=error");
    exit;
}
?>