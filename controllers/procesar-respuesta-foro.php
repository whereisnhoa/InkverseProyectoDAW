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
    header("Location: foros.php?msg=csrf");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$tema_id = intval($_POST['tema_id']);
$contenido = limpiarDato($_POST['contenido']);

if (!$tema_id || empty($contenido)) {
    header("Location: tema.php?id=$tema_id&msg=datos");
    exit;
}

// Verifica que el tema exista y no esté cerrado
$tema = $conexion->query("SELECT estado FROM temas_foro WHERE id=$tema_id")->fetch_assoc();
if(!$tema || $tema['estado'] == 'cerrado'){
    header("Location: tema.php?id=$tema_id&msg=cerrado");
    exit;
}

// Insertar respuesta
$stmt = $conexion->prepare("INSERT INTO respuestas_foro (tema_id, usuario_id, contenido, fecha) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("iis", $tema_id, $usuario_id, $contenido);
if ($stmt->execute()) {
    // Actualiza última actividad del tema
    $conexion->query("UPDATE temas_foro SET ultima_actividad = NOW() WHERE id = $tema_id");
    header("Location: tema.php?id=$tema_id&msg=ok#final");
    exit;
} else {
    header("Location: tema.php?id=$tema_id&msg=error");
    exit;
}