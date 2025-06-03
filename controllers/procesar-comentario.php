<?php
session_start();
require_once('../includes/conexion.php');
require_once('../includes/funciones.php');

// Solo JSON, nunca HTML:
header('Content-Type: application/json');

// Revisa login
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'mensaje' => 'Debes iniciar sesión para comentar.']);
    exit;
}
// Revisa CSRF
if (!isset($_POST['csrf_token']) || !verificarTokenCSRF($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'mensaje' => 'Error de validación CSRF.']);
    exit;
}

$contenido = limpiarDato($_POST['contenido']);
$contenido_id = intval($_POST['contenido_id']);
$tipo_contenido = $_POST['tipo_contenido'];
$usuario_id = $_SESSION['usuario_id'];
if (empty($contenido) || empty($contenido_id) || !in_array($tipo_contenido, ['libro', 'comic'])) {
    echo json_encode(['success' => false, 'mensaje' => 'Datos inválidos.']);
    exit;
}

$sql = "INSERT INTO comentarios (usuario_id, contenido, tipo_contenido, contenido_id) VALUES (?, ?, ?, ?)";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("issi", $usuario_id, $contenido, $tipo_contenido, $contenido_id);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'mensaje' => 'Error al publicar el comentario.']);
}
?>