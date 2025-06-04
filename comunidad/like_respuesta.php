<?php
require_once('../includes/config.php');
require_once('../includes/conexion.php');
require_once('../includes/funciones.php');
session_start();

header('Content-Type: application/json');


if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$respuesta_id = isset($_POST['respuesta_id']) ? intval($_POST['respuesta_id']) : 0;

if ($respuesta_id < 1) {
    echo json_encode(['success' => false, 'error' => 'ID inválido']);
    exit;
}


$sql_check = "SELECT id FROM respuestas_likes WHERE respuesta_id = ? AND usuario_id = ?";
$stmt = $conexion->prepare($sql_check);
$stmt->bind_param("ii", $respuesta_id, $usuario_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // Si ya le dio like, eliminar el like
    $stmt->close();
    $sql_del = "DELETE FROM respuestas_likes WHERE respuesta_id = ? AND usuario_id = ?";
    $stmt = $conexion->prepare($sql_del);
    $stmt->bind_param("ii", $respuesta_id, $usuario_id);
    $stmt->execute();
    // Contar likes actualizados
    $sql_count = "SELECT COUNT(*) FROM respuestas_likes WHERE respuesta_id = ?";
    $stmt2 = $conexion->prepare($sql_count);
    $stmt2->bind_param("i", $respuesta_id);
    $stmt2->execute();
    $stmt2->bind_result($likes);
    $stmt2->fetch();
    $stmt2->close();
    echo json_encode(['success' => true, 'liked' => false, 'likes' => $likes]);
} else {
    // Si no le ha dado like, agregar like
    $stmt->close();
    $sql_ins = "INSERT INTO respuestas_likes (respuesta_id, usuario_id, fecha) VALUES (?, ?, NOW())";
    $stmt = $conexion->prepare($sql_ins);
    $stmt->bind_param("ii", $respuesta_id, $usuario_id);
    $stmt->execute();

    // Notificar al dueño de la respuesta (solo si no es el mismo usuario)
    $sql_owner = "SELECT usuario_id FROM respuestas_foro WHERE id = ?";
    $stmt_owner = $conexion->prepare($sql_owner);
    $stmt_owner->bind_param("i", $respuesta_id);
    $stmt_owner->execute();
    $stmt_owner->bind_result($dueño_id);
    $stmt_owner->fetch();
    $stmt_owner->close();

    if ($dueño_id != $usuario_id) {
        // Usa tu función crearNotificacion
        crearNotificacion($dueño_id, 'like', '¡Tu respuesta recibió un "Me gusta"!', null);
    }

    // Contar likes actualizados
    $sql_count = "SELECT COUNT(*) FROM respuestas_likes WHERE respuesta_id = ?";
    $stmt2 = $conexion->prepare($sql_count);
    $stmt2->bind_param("i", $respuesta_id);
    $stmt2->execute();
    $stmt2->bind_result($likes);
    $stmt2->fetch();
    $stmt2->close();
    echo json_encode(['success' => true, 'liked' => true, 'likes' => $likes]);
}

$stmt->close();
$conexion->close();
?>