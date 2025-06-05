<?php
session_start();
include '../includes/conexion.php';
include_once('../includes/funciones.php');

if (!isset($_SESSION['usuario_id'])) {
    redirigirConMensaje('../public/login.php', 'Debes iniciar sesión para subir arte.', 'error');
    exit();
}
if (!isset($_POST['csrf_token']) || !verificarTokenCSRF($_POST['csrf_token'])) {
    redirigirConMensaje('../comunidad/arte.php', 'Error de validación. Por favor, intenta nuevamente.', 'error');
    exit();
}
if (!isset($_POST['acepta_terminos'])) {
    redirigirConMensaje('../comunidad/subir_arte.php', 'Debes aceptar los términos y condiciones para subir arte.', 'error');
    exit();
}
$titulo = limpiarDatos($_POST['titulo']);
$descripcion = limpiarDatos($_POST['descripcion']);
$categoria_id = intval($_POST['categoria_id']);
$publico_adulto = isset($_POST['publico_adulto']) ? 1 : 0;
$usuario_id = $_SESSION['usuario_id'];

if (empty($titulo) || empty($categoria_id)) {
    redirigirConMensaje('../comunidad/subir_arte.php', 'Todos los campos obligatorios deben ser completados.', 'error');
    exit();
}

$sql_check = "SELECT id FROM categorias_arte WHERE id = ?";
$stmt_check = $conexion->prepare($sql_check);
$stmt_check->bind_param("i", $categoria_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
if ($result_check->num_rows == 0) {
    redirigirConMensaje('../comunidad/subir_arte.php', 'La categoría seleccionada no es válida.', 'error');
    exit();
}

if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] != UPLOAD_ERR_OK) {
    redirigirConMensaje('../comunidad/subir_arte.php', 'Error al subir la imagen. Por favor, intenta nuevamente.', 'error');
    exit();
}
$allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
$file_type = $_FILES['imagen']['type'];
if (!in_array($file_type, $allowed_types)) {
    redirigirConMensaje('../comunidad/subir_arte.php', 'Tipo de archivo no permitido. Solo se aceptan imágenes JPG, PNG o GIF.', 'error');
    exit();
}
$max_size = 5 * 1024 * 1024;
if ($_FILES['imagen']['size'] > $max_size) {
    redirigirConMensaje('../comunidad/subir_arte.php', 'La imagen es demasiado grande. El tamaño máximo permitido es 5MB.', 'error');
    exit();
}
$upload_dir = '../uploads/arte/';
$thumb_dir = '../uploads/arte/thumbnails/';
if (!file_exists($upload_dir)) mkdir($upload_dir, 0755, true);
if (!file_exists($thumb_dir)) mkdir($thumb_dir, 0755, true);
$file_extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
$file_name = uniqid('arte_') . '.' . $file_extension;
$file_path = $upload_dir . $file_name;
$thumb_path = $thumb_dir . $file_name;
if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $file_path)) {
    redirigirConMensaje('../comunidad/subir_arte.php', 'Error al guardar la imagen. Por favor, intenta nuevamente.', 'error');
    exit();
}
$thumb_created = false;
if (function_exists('imagecreatetruecolor')) {
    $max_width = 300; $max_height = 300;
    list($width, $height) = getimagesize($file_path);
    $new_width = $max_width; $new_height = intval($height * $max_width / $width);
    if ($width < $height) { $new_height = $max_height; $new_width = intval($width * $max_height / $height); }
    $thumb = imagecreatetruecolor($new_width, $new_height);
    switch ($file_type) {
        case 'image/jpeg': $source = imagecreatefromjpeg($file_path); break;
        case 'image/png':
            $source = imagecreatefrompng($file_path);
            imagealphablending($thumb, false); imagesavealpha($thumb, true); break;
        case 'image/gif': $source = imagecreatefromgif($file_path); break;
    }
    imagecopyresampled($thumb, $source, 0,0,0,0, $new_width,$new_height, $width,$height);
    switch ($file_type) {
        case 'image/jpeg': imagejpeg($thumb, $thumb_path, 80); break;
        case 'image/png': imagepng($thumb, $thumb_path, 8); break;
        case 'image/gif': imagegif($thumb, $thumb_path); break;
    }
    imagedestroy($source); imagedestroy($thumb);
    $thumb_created = true;
}
$imagen_db = str_replace('../', BASE_URL . '/', $file_path);
$thumb_db = $thumb_created ? str_replace('../', BASE_URL . '/', $thumb_path) : null;
$sql = "INSERT INTO galeria_arte (usuario_id, categoria_id, titulo, descripcion, imagen, thumbnail, publico_adulto) VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("iissssi", $usuario_id, $categoria_id, $titulo, $descripcion, $imagen_db, $thumb_db, $publico_adulto);
if ($stmt->execute()) {
    $arte_id = $stmt->insert_id;
    redirigirConMensaje('../comunidad/ver_arte.php?id=' . $arte_id, 'Tu obra ha sido subida correctamente.', 'exito');
} else {
    if (file_exists($file_path)) unlink($file_path);
    if (file_exists($thumb_path)) unlink($thumb_path);
    redirigirConMensaje('../comunidad/subir_arte.php', 'Error al guardar la obra: ' . $stmt->error, 'error');
}
?>