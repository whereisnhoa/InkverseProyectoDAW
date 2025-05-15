
<?php
session_start();
include '../includes/conexion.php';
include_once('../includes/funciones.php');

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    redirigirConMensaje(BASE_URL . '/public/login.php', 'Debes iniciar sesión para responder.', 'error');
    exit();
}

// Verificar token CSRF
if (!isset($_POST['csrf_token']) || !verificarTokenCSRF($_POST['csrf_token'])) {
    redirigirConMensaje(BASE_URL . '/comunidad/foros.php', 'Error de validación. Por favor, intenta nuevamente.', 'error');
    exit();
}

// Obtener datos del formulario
$contenido = limpiarDato($_POST['contenido']);
$tema_id = intval($_POST['tema_id']);
$usuario_id = $_SESSION['usuario_id'];

// Validar datos
if (empty($contenido) || empty($tema_id)) {
    redirigirConMensaje(BASE_URL . '/comunidad/tema.php?id=' . $tema_id, 'El contenido de la respuesta no puede estar vacío.', 'error');
    exit();
}

// Verificar que el tema exista y no esté cerrado
$sql_check = "SELECT id, estado FROM temas_foro WHERE id = ?";
$stmt_check = $conexion->prepare($sql_check);
$stmt_check->bind_param("i", $tema_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
$tema = $result_check->fetch_assoc();

if (!$tema) {
    redirigirConMensaje(BASE_URL . '/comunidad/foros.php', 'El tema no existe.', 'error');
    exit();
}

if ($tema['estado'] == 'cerrado') {
    redirigirConMensaje(BASE_URL . '/comunidad/tema.php?id=' . $tema_id, 'No puedes responder a un tema cerrado.', 'error');
    exit();
}

// Insertar respuesta en la base de datos
$sql = "INSERT INTO respuestas_foro (tema_id, usuario_id, contenido) VALUES (?, ?, ?)";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("iis", $tema_id, $usuario_id, $contenido);

if ($stmt->execute()) {
    // Actualizar contador de respuestas y fecha de última actividad del tema
    $sql_update = "UPDATE temas_foro SET respuestas = respuestas + 1, ultima_actividad = NOW() WHERE id = ?";
    $stmt_update = $conexion->prepare($sql_update);
    $stmt_update->bind_param("i", $tema_id);
    $stmt_update->execute();
    
    redirigirConMensaje(BASE_URL . '/comunidad/tema.php?id=' . $tema_id, 'Tu respuesta ha sido publicada correctamente.', 'exito');
} else {
    redirigirConMensaje(BASE_URL . '/comunidad/tema.php?id=' . $tema_id, 'Error al publicar la respuesta: ' . $stmt->error, 'error');
}
?>
