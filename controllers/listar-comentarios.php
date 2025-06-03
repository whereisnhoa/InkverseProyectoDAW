<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Cambia la ruta según tu estructura real
require_once(__DIR__ . '/../includes/conexion.php');
header('Content-Type: application/json');

$contenido_id = isset($_GET['contenido_id']) ? intval($_GET['contenido_id']) : 0;
$tipo_contenido = isset($_GET['tipo_contenido']) ? $_GET['tipo_contenido'] : '';

if (!$contenido_id || !in_array($tipo_contenido, array('libro','comic'))) {
    echo json_encode([]);
    exit;
}

$sql = "SELECT c.contenido, c.fecha, u.usuario FROM comentarios c
        JOIN usuarios u ON c.usuario_id = u.id
        WHERE c.contenido_id = ? AND c.tipo_contenido = ?
        ORDER BY c.fecha ASC";
$stmt = $conexion->prepare($sql);
if (!$stmt) {
    echo json_encode(['error' => $conexion->error]);
    exit;
}
$stmt->bind_param("is", $contenido_id, $tipo_contenido);
$stmt->execute();
$res = $stmt->get_result();
$comentarios = array();
while($row = $res->fetch_assoc()) {
    $comentarios[] = array(
        'usuario' => htmlspecialchars($row['usuario']),
        'fecha' => date('d/m/Y H:i', strtotime($row['fecha'])),
        'contenido' => nl2br(htmlspecialchars($row['contenido']))
    );
}
echo json_encode($comentarios);
exit;