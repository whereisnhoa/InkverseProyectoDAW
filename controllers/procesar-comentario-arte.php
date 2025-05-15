
<?php
session_start();
include '../includes/conexion.php';
include_once('../includes/funciones.php');

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    redirigirConMensaje(BASE_URL . '/public/login.php', 'Debes iniciar sesión para comentar.', 'error');
    exit();
}

// Verificar token CSRF
if (!isset($_POST['csrf_token']) || !verificarTokenCSRF($_POST['csrf_token'])) {
    redirigirConMensaje(BASE_URL . '/comunidad/arte.php', 'Error de validación. Por favor, intenta nuevamente.', 'error');
    exit();
}

// Obtener datos del formulario
$comentario = limpiarDato($_POST['comentario']);
$arte_id = intval($_POST['arte_id']);
$usuario_id = $_SESSION['usuario_id'];

// Validar datos
if (empty($comentario) || empty($arte_id)) {
    redirigirConMensaje(BASE_URL . '/comunidad/ver_arte.php?id=' . $arte_id, 'El comentario no puede estar vacío.', 'error');
    exit();
}

// Verificar que la obra exista
$sql_check = "SELECT id FROM galeria_arte WHERE id = ?";
$stmt_check = $conexion->prepare($sql_check);
$stmt_check->bind_param("i", $arte_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows == 0) {
    redirigirConMensaje(BASE_URL . '/comunidad/arte.php', 'La obra no existe.', 'error');
    exit();
}

// Insertar comentario en la base de datos
$sql = "INSERT INTO comentarios_arte (arte_id, usuario_id, comentario) VALUES (?, ?, ?)";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("iis", $arte_id, $usuario_id, $comentario);

if ($stmt->execute()) {
    redirigirConMensaje(BASE_URL . '/comunidad/ver_arte.php?id=' . $arte_id, 'Tu comentario ha sido publicado correctamente.', 'exito');
} else {
    redirigirConMensaje(BASE_URL . '/comunidad/ver_arte.php?id=' . $arte_id, 'Error al publicar el comentario: ' . $stmt->error, 'error');
}
?>
