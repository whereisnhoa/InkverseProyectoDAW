<?php
session_start();
require_once(__DIR__ . '/../includes/config.php');
require_once(__DIR__ . '/../includes/conexion.php');
require_once(__DIR__ . '/../includes/funciones.php');
verificarLogin();

$usuario_id = $_SESSION['usuario_id'];

// Recibe todos los campos del formulario
$usuario = trim($_POST['usuario'] ?? '');
$email = trim($_POST['email'] ?? '');
$nombre = trim($_POST['nombre'] ?? '');
$fecha_nacimiento = trim($_POST['fecha_nacimiento'] ?? '');
$biografia = trim($_POST['biografia'] ?? '');
$sitio_web = trim($_POST['sitio_web'] ?? '');
$ubicacion = trim($_POST['ubicacion'] ?? '');
$privacidad = in_array(($_POST['privacidad'] ?? 'publico'), ['publico','privado']) ? $_POST['privacidad'] : 'publico';
$X = trim($_POST['X'] ?? '');
$instagram = trim($_POST['instagram'] ?? '');
$facebook = trim($_POST['facebook'] ?? '');
$banner_color = trim($_POST['banner_color'] ?? '#4361ee');
$eliminar_banner_img = isset($_POST['eliminar_banner_img']) && $_POST['eliminar_banner_img'] === "1";

// Obtén avatar y banner actuales
$sql = "SELECT avatar, banner_img FROM usuarios WHERE id=?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($avatar_actual, $banner_actual);
$stmt->fetch();
$stmt->close();

// --- Manejo de subida de avatar ---
$avatar_path = $avatar_actual;
if (!empty($_FILES['avatar']['name'])) {
    $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg','jpeg','png','gif']) && $_FILES['avatar']['size'] <= 2*1024*1024) {
        $avatar_name = uniqid('avatar_') . '.' . $ext;
        $ruta_destino = __DIR__ . '/../uploads/avatars/' . $avatar_name;
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $ruta_destino)) {
            // Elimina avatar anterior si no es el default y existe
            if (!empty($avatar_actual) && file_exists(__DIR__ . '/../uploads/avatars/' . $avatar_actual)) {
                @unlink(__DIR__ . '/../uploads/avatars/' . $avatar_actual);
            }
            $avatar_path = $avatar_name;
        }
    }
}

// --- Manejo de subida/eliminación de banner ---
$banner_path = $banner_actual;
// Eliminar banner si el usuario lo pidió
if ($eliminar_banner_img && !empty($banner_actual) && file_exists(__DIR__ . '/../uploads/banners/' . $banner_actual)) {
    @unlink(__DIR__ . '/../uploads/banners/' . $banner_actual);
    $banner_path = null;
}
if (!empty($_FILES['banner_img']['name'])) {
    $ext = strtolower(pathinfo($_FILES['banner_img']['name'], PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg','jpeg','png','gif']) && $_FILES['banner_img']['size'] <= 2*1024*1024) {
        $banner_name = uniqid('banner_') . '.' . $ext;
        $ruta_destino = __DIR__ . '/../uploads/banners/' . $banner_name;
        if (move_uploaded_file($_FILES['banner_img']['tmp_name'], $ruta_destino)) {
            // Elimina banner anterior si existe
            if (!empty($banner_actual) && file_exists(__DIR__ . '/../uploads/banners/' . $banner_actual)) {
                @unlink(__DIR__ . '/../uploads/banners/' . $banner_actual);
            }
            $banner_path = $banner_name;
        }
    }
}

// Sanear color hex
if (!preg_match('/^#[a-f0-9]{6}$/i', $banner_color)) {
    $banner_color = '#4361ee';
}

// Actualiza usuario
$sql = "UPDATE usuarios SET
    usuario=?, email=?, nombre=?, fecha_nacimiento=?, biografia=?, sitio_web=?, ubicacion=?, privacidad=?, avatar=?, banner_img=?, banner_color=?, X=?, instagram=?, facebook=?
    WHERE id=?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("ssssssssssssssi",
    $usuario, $email, $nombre, $fecha_nacimiento, $biografia, $sitio_web, $ubicacion, $privacidad,
    $avatar_path, $banner_path, $banner_color, $X, $instagram, $facebook, $usuario_id
);

if ($stmt->execute()) {
    $_SESSION['mensaje'] = "Perfil actualizado correctamente.";
    $_SESSION['tipo_mensaje'] = "exito";
} else {
    $_SESSION['mensaje'] = "Error al actualizar el perfil: " . $stmt->error;
    $_SESSION['tipo_mensaje'] = "error";
}
header("Location: " . BASE_URL . "/usuario/perfil.php");
exit();