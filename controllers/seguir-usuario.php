<?php
// Verificar sesión y redirigir si no hay usuario logueado
require_once(__DIR__ . '/../includes/funciones.php');
verificarLogin();

// Incluir archivos necesarios
require_once(__DIR__ . '/../includes/conexion.php');

// Obtener ID del usuario a seguir
$usuario_id = isset($_POST['usuario_id']) ? intval($_POST['usuario_id']) : 0;

// Obtener ID del usuario actual
$seguidor_id = $_SESSION['usuario_id'];

// Validar que no sea el mismo usuario
if ($usuario_id == $seguidor_id) {
    $_SESSION['error'] = "No puedes seguirte a ti mismo.";
    header("Location: " . BASE_URL . "/public/perfil.php?id=" . $usuario_id);
    exit();
}

// Verificar si ya sigue al usuario
$sql_verificar = "SELECT id FROM seguidores WHERE seguidor_id = ? AND seguido_id = ?";
$stmt_verificar = $conexion->prepare($sql_verificar);
$stmt_verificar->bind_param("ii", $seguidor_id, $usuario_id);
$stmt_verificar->execute();
$resultado = $stmt_verificar->get_result();

if ($resultado->num_rows > 0) {
    $_SESSION['info'] = "Ya sigues a este usuario.";
    header("Location: " . BASE_URL . "/public/perfil.php?id=" . $usuario_id);
    exit();
}

// Insertar nuevo seguimiento
$sql_seguir = "INSERT INTO seguidores (seguidor_id, seguido_id) VALUES (?, ?)";
$stmt_seguir = $conexion->prepare($sql_seguir);
$stmt_seguir->bind_param("ii", $seguidor_id, $usuario_id);

if ($stmt_seguir->execute()) {
    // Crear notificación para el usuario seguido
    $sql_notificacion = "INSERT INTO notificaciones (usuario_id, tipo, titulo, contenido, enlace) 
                         VALUES (?, 'seguidor', 'Nuevo seguidor', ?, ?)";
    
    // Obtener nombre de usuario del seguidor
    $sql_usuario = "SELECT usuario FROM usuarios WHERE id = ?";
    $stmt_usuario = $conexion->prepare($sql_usuario);
    $stmt_usuario->bind_param("i", $seguidor_id);
    $stmt_usuario->execute();
    $nombre_seguidor = $stmt_usuario->get_result()->fetch_assoc()['usuario'];
    
    $contenido = $nombre_seguidor . " ha comenzado a seguirte.";
    $enlace = "/public/perfil.php?id=" . $seguidor_id;
    
    $stmt_notificacion = $conexion->prepare($sql_notificacion);
    $stmt_notificacion->bind_param("iss", $usuario_id, $contenido, $enlace);
    $stmt_notificacion->execute();
    
    $_SESSION['exito'] = "Ahora sigues a este usuario.";
} else {
    $_SESSION['error'] = "Error al seguir al usuario. Por favor, inténtalo de nuevo.";
}

// Redireccionar de vuelta al perfil
header("Location: " . BASE_URL . "/public/perfil.php?id=" . $usuario_id);
exit();
