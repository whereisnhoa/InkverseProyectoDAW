<?php
session_start();
include '../includes/conexion.php';
include_once('../includes/funciones.php');

if (!isset($_SESSION['usuario_id'])) {
    redirigirConMensaje('../public/login.php', 'Debes iniciar sesión para comentar.', 'error');
    exit();
}
if (!isset($_POST['csrf_token']) || !verificarTokenCSRF($_POST['csrf_token'])) {
    redirigirConMensaje('../comunidad/arte.php', 'Error de validación. Por favor, intenta nuevamente.', 'error');
    exit();
}
$comentario = limpiarDatos($_POST['comentario']);
$arte_id = intval($_POST['arte_id']);
$usuario_id = $_SESSION['usuario_id'];

if (empty($comentario) || empty($arte_id)) {
    redirigirConMensaje('../comunidad/ver_arte.php?id=' . $arte_id, 'El comentario no puede estar vacío.', 'error');
    exit();
}
$sql_check = "SELECT id FROM galeria_arte WHERE id = ?";
$stmt_check = $conexion->prepare($sql_check);
$stmt_check->bind_param("i", $arte_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
if ($result_check->num_rows == 0) {
    redirigirConMensaje('../comunidad/arte.php', 'La obra no existe.', 'error');
    exit();
}
$sql = "INSERT INTO comentarios_arte (arte_id, usuario_id, comentario) VALUES (?, ?, ?)";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("iis", $arte_id, $usuario_id, $comentario);
if ($stmt->execute()) {
    redirigirConMensaje('../comunidad/ver_arte.php?id=' . $arte_id, 'Tu comentario ha sido publicado correctamente.', 'exito');
} else {
    redirigirConMensaje('../comunidad/ver_arte.php?id=' . $arte_id, 'Error al publicar el comentario: ' . $stmt->error, 'error');
}
?>