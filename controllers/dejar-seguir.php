
<?php
session_start();
// Verificar sesión y redirigir si no hay usuario logueado
require_once(__DIR__ . '/../includes/funciones.php');
verificarLogin();
// Incluir archivos necesarios
require_once(__DIR__ . '/../includes/conexion.php');


// Obtener ID del usuario a dejar de seguir
$usuario_id = isset($_POST['usuario_id']) ? intval($_POST['usuario_id']) : 0;
// Obtener ID del usuario actual
$usuario_actual_id = $_SESSION['usuario_id'];

// Eliminar seguimiento
$sql_dejar_seguir = "DELETE FROM seguidores WHERE usuario_id = ? AND seguido_id = ?";
$stmt_dejar_seguir = $conexion->prepare($sql_dejar_seguir);
$stmt_dejar_seguir->bind_param("ii", $usuario_actual_id, $usuario_id);

if ($stmt_dejar_seguir->execute()) {
    $_SESSION['exito'] = "Has dejado de seguir a este usuario.";
} else {
    $_SESSION['error'] = "Error al dejar de seguir al usuario. Por favor, inténtalo de nuevo.";
}

// Redireccionar de vuelta al perfil
header("Location: " . BASE_URL . "/public/perfil.php?id=" . $usuario_id);
exit();