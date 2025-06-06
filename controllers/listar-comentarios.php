<?php
error_reporting(0);
ini_set('display_errors', 0);
require_once(__DIR__ . '/../includes/conexion.php');
require_once(__DIR__ . '/../includes/config.php');
header('Content-Type: application/json');

$contenido_id = isset($_GET['contenido_id']) ? intval($_GET['contenido_id']) : 0;
$tipo_contenido = $_GET['tipo_contenido'] ?? '';
if ($tipo_contenido === 'libros') $tipo_contenido = 'libro';
if ($tipo_contenido === 'comics') $tipo_contenido = 'comic';

if (!$contenido_id || ($tipo_contenido !== 'libro' && $tipo_contenido !== 'comic')) {
    echo json_encode([]);
    exit;
}

$stmt = $conexion->prepare("SELECT c.comentario, c.fecha, u.usuario, u.avatar FROM comentarios c JOIN usuarios u ON c.usuario_id = u.id WHERE c.contenido_id = ? AND c.tipo_contenido = ? ORDER BY c.fecha DESC");
$stmt->bind_param("is", $contenido_id, $tipo_contenido);
$stmt->execute();
$res = $stmt->get_result();
$out = [];
while($row = $res->fetch_assoc()) {
    $avatarUrl = !empty($row['avatar'])
        ? BASE_URL . '/uploads/avatars/' . $row['avatar']
        : BASE_URL . '/img/avatar_default.png';
    $out[] = [
        'usuario' => htmlspecialchars($row['usuario']),
        'avatar' => $avatarUrl,
        'fecha' => date('d/m/Y H:i', strtotime($row['fecha'])),
        'contenido' => nl2br(htmlspecialchars($row['comentario']))
    ];
}
echo json_encode($out);