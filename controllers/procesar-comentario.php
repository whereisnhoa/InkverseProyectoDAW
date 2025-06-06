<?php
error_reporting(0);
ini_set('display_errors', 0);
session_start();
require_once(__DIR__ . '/../includes/conexion.php');
header('Content-Type: application/json');

$usuario_id = $_SESSION['usuario_id'] ?? null;
if (!$usuario_id) {
    echo json_encode(['success' => false, 'mensaje' => 'Debes iniciar sesión']);
    exit;
}

$contenido_id = isset($_POST['contenido_id']) ? intval($_POST['contenido_id']) : 0;
$tipo_contenido = $_POST['tipo_contenido'] ?? '';
if ($tipo_contenido === 'libros') $tipo_contenido = 'libro';
if ($tipo_contenido === 'comics') $tipo_contenido = 'comic';
$comentario = trim($_POST['contenido'] ?? ''); // En formulario es "contenido", en BD es "comentario"

if (!$contenido_id || ($tipo_contenido !== 'libro' && $tipo_contenido !== 'comic') || $comentario === '') {
    echo json_encode(['success' => false, 'mensaje' => 'Datos incompletos']);
    exit;
}

$stmt = $conexion->prepare("INSERT INTO comentarios (usuario_id, contenido_id, tipo_contenido, comentario, fecha) VALUES (?, ?, ?, ?, NOW())");
$stmt->bind_param("iiss", $usuario_id, $contenido_id, $tipo_contenido, $comentario);
$ok = $stmt->execute();
$stmt->close();

if ($ok) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'mensaje' => 'Error en la base de datos']);
}