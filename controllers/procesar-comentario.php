
<?php
/**
 * Controlador unificado para procesar comentarios en obras (libros/cómics)
 * Este controlador maneja los comentarios en las obras, diferente de los comentarios en arte
 * y las respuestas en foros.
 */
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
    redirigirConMensaje(BASE_URL . '/public/index.php', 'Error de validación. Por favor, intenta nuevamente.', 'error');
    exit();
}

// Obtener datos del formulario
$contenido = limpiarDato($_POST['contenido']);
$contenido_id = intval($_POST['contenido_id']);
$tipo_contenido = $_POST['tipo_contenido']; // 'libro' o 'comic'
$usuario_id = $_SESSION['usuario_id'];

// Validar datos
if (empty($contenido) || empty($contenido_id) || !in_array($tipo_contenido, ['libro', 'comic'])) {
    redirigirConMensaje(BASE_URL . '/public/obra.php?id=' . $contenido_id . '&tipo=' . $tipo_contenido, 'Datos inválidos.', 'error');
    exit();
}

// Verificar que la obra exista
$tabla = ($tipo_contenido === 'libro') ? 'libros' : 'comics';
$sql_check = "SELECT id FROM $tabla WHERE id = ?";
$stmt_check = $conexion->prepare($sql_check);
$stmt_check->bind_param("i", $contenido_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows == 0) {
    redirigirConMensaje(BASE_URL . '/public/index.php', 'La obra no existe.', 'error');
    exit();
}

// Insertar comentario en la base de datos
$sql = "INSERT INTO comentarios (usuario_id, contenido, tipo_contenido, contenido_id) VALUES (?, ?, ?, ?)";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("issi", $usuario_id, $contenido, $tipo_contenido, $contenido_id);

if ($stmt->execute()) {
    redirigirConMensaje(BASE_URL . '/public/obra.php?id=' . $contenido_id . '&tipo=' . $tipo_contenido, 'Tu comentario ha sido publicado correctamente.', 'exito');
} else {
    redirigirConMensaje(BASE_URL . '/public/obra.php?id=' . $contenido_id . '&tipo=' . $tipo_contenido, 'Error al publicar el comentario: ' . $stmt->error, 'error');
}
?>
