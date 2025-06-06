
<?php
session_start();
include_once('../includes/conexion.php');
include_once('../includes/funciones.php');

if (!isset($_SESSION['usuario_id'])) {
    redirigirConMensaje('../comunidad/arte.php', 'Debes iniciar sesión.','error');
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$arte_id = isset($_POST['arte_id']) ? intval($_POST['arte_id']) : 0;

// Verifica CSRF si usas tokens
if (isset($_POST['csrf_token']) && !verificarTokenCSRF($_POST['csrf_token'])) {
    redirigirConMensaje('../comunidad/arte.php', 'Token CSRF inválido.','error');
    exit();
}

// Verifica que la obra pertenezca al usuario
$sql = "SELECT imagen FROM galeria_arte WHERE id = ? AND usuario_id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("ii", $arte_id, $usuario_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($imagen);
    $stmt->fetch();

    // Elimina la imagen física si existe
    if ($imagen && file_exists('../' . $imagen)) {
        unlink('../' . $imagen);
    }

    // Elimina la obra de la base de datos
    $sql_del = "DELETE FROM galeria_arte WHERE id = ? AND usuario_id = ?";
    $stmt_del = $conexion->prepare($sql_del);
    $stmt_del->bind_param("ii", $arte_id, $usuario_id);
    $stmt_del->execute();

    redirigirConMensaje('../comunidad/arte.php', 'Obra eliminada correctamente.','success');
    exit();
} else {
    redirigirConMensaje('../comunidad/arte.php', 'No tienes permiso para eliminar esta obra.','error');
    exit();
}
?>